<?php

namespace Spatie\SiteSearch\Drivers\Database;

use Illuminate\Database\Connection;

class MySqlGrammar extends Grammar
{
    public function ensureFtsSetup(Connection $connection): void
    {
        $hasIndex = $connection->select("
            SHOW INDEX FROM site_search_documents WHERE Key_name = 'site_search_documents_fulltext'
        ");

        if (empty($hasIndex)) {
            $connection->statement('
                ALTER TABLE site_search_documents
                ADD FULLTEXT INDEX site_search_documents_fulltext (entry, page_title, h1, description, url)
            ');
        }
    }

    public function search(Connection $connection, string $indexName, string $query, int $limit, int $offset): array
    {
        if (empty(trim($query))) {
            return $this->getAllDocuments($connection, $indexName, $limit, $offset);
        }

        $searchTerms = $this->prepareBooleanQuery($query);

        $results = $connection->table('site_search_documents')
            ->select('*')
            ->selectRaw(
                'MATCH(entry, page_title, h1, description, url) AGAINST(? IN BOOLEAN MODE) as relevance',
                [$searchTerms]
            )
            ->where('index_name', $indexName)
            ->whereRaw('MATCH(entry, page_title, h1, description, url) AGAINST(? IN BOOLEAN MODE)', [$searchTerms])
            ->orderByDesc('relevance')
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();

        return array_map(fn (array $row) => $this->addHighlighting($row, $query), $results);
    }

    public function getTotalCount(Connection $connection, string $indexName, string $query): int
    {
        if (empty(trim($query))) {
            return $connection->table('site_search_documents')
                ->where('index_name', $indexName)
                ->count();
        }

        $searchTerms = $this->prepareBooleanQuery($query);

        return $connection->table('site_search_documents')
            ->where('index_name', $indexName)
            ->whereRaw('MATCH(entry, page_title, h1, description, url) AGAINST(? IN BOOLEAN MODE)', [$searchTerms])
            ->count();
    }

    protected function prepareBooleanQuery(string $query): string
    {
        $escaped = $this->escapeSearchTerm($query);

        $words = preg_split('/\s+/', $escaped, -1, PREG_SPLIT_NO_EMPTY);

        if (empty($words)) {
            return '';
        }

        return implode(' ', array_map(
            fn (string $word) => '+'.$word.'*',
            $words
        ));
    }

    protected function addHighlighting(array $row, string $query): array
    {
        $words = preg_split('/\s+/', $this->escapeSearchTerm($query), -1, PREG_SPLIT_NO_EMPTY);

        if (empty($words)) {
            return $row;
        }

        $pattern = '/\b('.implode('|', array_map('preg_quote', $words)).')\w*/i';

        if (! empty($row['entry'])) {
            $row['entry_highlighted'] = preg_replace($pattern, '<em>$0</em>', $row['entry']);
        }

        if (! empty($row['description'])) {
            $row['description_highlighted'] = preg_replace($pattern, '<em>$0</em>', $row['description']);
        }

        return $row;
    }
}
