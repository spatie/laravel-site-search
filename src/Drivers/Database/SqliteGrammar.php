<?php

namespace Spatie\SiteSearch\Drivers\Database;

use Illuminate\Database\Connection;

class SqliteGrammar extends Grammar
{
    public function ensureFtsSetup(Connection $connection): void
    {
        $connection->statement("
            CREATE VIRTUAL TABLE IF NOT EXISTS site_search_documents_fts USING fts5(
                document_id UNINDEXED,
                index_name UNINDEXED,
                url,
                page_title,
                h1,
                entry,
                description,
                content='site_search_documents',
                content_rowid='id',
                tokenize='porter unicode61'
            )
        ");

        $connection->statement("
            CREATE TRIGGER IF NOT EXISTS site_search_documents_ai AFTER INSERT ON site_search_documents BEGIN
                INSERT INTO site_search_documents_fts(rowid, document_id, index_name, url, page_title, h1, entry, description)
                VALUES (NEW.id, NEW.document_id, NEW.index_name, NEW.url, NEW.page_title, NEW.h1, NEW.entry, NEW.description);
            END
        ");

        $connection->statement("
            CREATE TRIGGER IF NOT EXISTS site_search_documents_ad AFTER DELETE ON site_search_documents BEGIN
                INSERT INTO site_search_documents_fts(site_search_documents_fts, rowid, document_id, index_name, url, page_title, h1, entry, description)
                VALUES ('delete', OLD.id, OLD.document_id, OLD.index_name, OLD.url, OLD.page_title, OLD.h1, OLD.entry, OLD.description);
            END
        ");

        $connection->statement("
            CREATE TRIGGER IF NOT EXISTS site_search_documents_au AFTER UPDATE ON site_search_documents BEGIN
                INSERT INTO site_search_documents_fts(site_search_documents_fts, rowid, document_id, index_name, url, page_title, h1, entry, description)
                VALUES ('delete', OLD.id, OLD.document_id, OLD.index_name, OLD.url, OLD.page_title, OLD.h1, OLD.entry, OLD.description);
                INSERT INTO site_search_documents_fts(rowid, document_id, index_name, url, page_title, h1, entry, description)
                VALUES (NEW.id, NEW.document_id, NEW.index_name, NEW.url, NEW.page_title, NEW.h1, NEW.entry, NEW.description);
            END
        ");
    }

    public function search(Connection $connection, string $indexName, string $query, int $limit, int $offset): array
    {
        if (empty(trim($query))) {
            return $this->getAllDocuments($connection, $indexName, $limit, $offset);
        }

        $ftsQuery = $this->prepareFtsQuery($query);

        return $connection->table('site_search_documents_fts')
            ->select('d.*')
            ->selectRaw("highlight(site_search_documents_fts, 5, '<em>', '</em>') as entry_highlighted")
            ->selectRaw("highlight(site_search_documents_fts, 6, '<em>', '</em>') as description_highlighted")
            ->selectRaw('bm25(site_search_documents_fts, 0, 0, 1.0, 2.0, 1.0, 5.0, 3.0) as rank')
            ->join('site_search_documents as d', 'site_search_documents_fts.rowid', '=', 'd.id')
            ->whereRaw('site_search_documents_fts MATCH ?', [$ftsQuery])
            ->where('d.index_name', $indexName)
            ->orderBy('rank')
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    public function getTotalCount(Connection $connection, string $indexName, string $query): int
    {
        if (empty(trim($query))) {
            return $connection->table('site_search_documents')
                ->where('index_name', $indexName)
                ->count();
        }

        $ftsQuery = $this->prepareFtsQuery($query);

        return $connection->table('site_search_documents_fts')
            ->join('site_search_documents as d', 'site_search_documents_fts.rowid', '=', 'd.id')
            ->whereRaw('site_search_documents_fts MATCH ?', [$ftsQuery])
            ->where('d.index_name', $indexName)
            ->count();
    }

    protected function prepareFtsQuery(string $query): string
    {
        $escaped = $this->escapeSearchTerm($query);

        $words = preg_split('/\s+/', $escaped, -1, PREG_SPLIT_NO_EMPTY);

        if (empty($words)) {
            return '""';
        }

        return implode(' ', array_map(
            fn (string $word) => '"' . $word . '"*',
            $words
        ));
    }
}
