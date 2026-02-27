<?php

namespace Spatie\SiteSearch\Drivers\Sqlite;

use Illuminate\Database\Connection;

class QueryBuilder
{
    public function search(
        Connection $connection,
        string $query,
        ?int $limit = null,
        int $offset = 0,
        array $searchParameters = []
    ): array {
        $limit = $limit ?? 20;

        if (empty(trim($query))) {
            return $this->getAllDocuments($connection, $limit, $offset);
        }

        $ftsQuery = $this->prepareFtsQuery($query);

        $results = $connection->table('documents_fts')
            ->select('d.*')
            ->selectRaw("highlight(documents_fts, 4, '<em>', '</em>') as entry_highlighted")
            ->selectRaw("highlight(documents_fts, 5, '<em>', '</em>') as description_highlighted")
            ->selectRaw('bm25(documents_fts, 0, 1.0, 2.0, 5.0, 3.0, 1.0) as rank')
            ->join('documents as d', 'documents_fts.id', '=', 'd.id')
            ->whereRaw('documents_fts MATCH ?', [$ftsQuery])
            ->orderBy('rank')
            ->get();

        return $results->map(fn ($row) => (array) $row)->all();
    }

    public function getTotalCount(Connection $connection, string $query): int
    {
        if (empty(trim($query))) {
            return $connection->table('documents')->count();
        }

        $ftsQuery = $this->prepareFtsQuery($query);

        return $connection->table('documents_fts')
            ->whereRaw('documents_fts MATCH ?', [$ftsQuery])
            ->count();
    }

    protected function prepareFtsQuery(string $query): string
    {
        $escaped = str_replace(
            ['"', '*', '(', ')', ':'],
            ['', '', '', '', ''],
            $query
        );

        $escaped = preg_replace('/\b(OR|AND|NOT)\b/i', '', $escaped);

        $words = preg_split('/\s+/', trim($escaped), -1, PREG_SPLIT_NO_EMPTY);

        if (empty($words)) {
            return '""';
        }

        $terms = array_map(
            fn (string $word) => '"' . $word . '"*',
            $words
        );

        return implode(' ', $terms);
    }

    protected function getAllDocuments(Connection $connection, int $limit, int $offset): array
    {
        return $connection->table('documents')
            ->orderByDesc('date_modified_timestamp')
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }
}
