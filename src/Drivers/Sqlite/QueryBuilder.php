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

        $sql = '
            SELECT
                d.*,
                highlight(documents_fts, 4, \'<em>\', \'</em>\') as entry_highlighted,
                highlight(documents_fts, 5, \'<em>\', \'</em>\') as description_highlighted,
                bm25(documents_fts, 0, 1.0, 2.0, 5.0, 3.0, 1.0) as rank
            FROM documents_fts
            JOIN documents d ON documents_fts.id = d.id
            WHERE documents_fts MATCH ?
            ORDER BY rank
            LIMIT ? OFFSET ?
        ';

        $results = $connection->select($sql, [$ftsQuery, $limit, $offset]);

        return array_map(fn ($row) => (array) $row, $results);
    }

    public function getTotalCount(Connection $connection, string $query): int
    {
        if (empty(trim($query))) {
            $result = $connection->selectOne('SELECT COUNT(*) as count FROM documents');

            return (int) $result->count;
        }

        $ftsQuery = $this->prepareFtsQuery($query);

        $result = $connection->selectOne(
            'SELECT COUNT(*) as count FROM documents_fts WHERE documents_fts MATCH ?',
            [$ftsQuery]
        );

        return (int) $result->count;
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
        $results = $connection->select(
            'SELECT * FROM documents ORDER BY date_modified_timestamp DESC LIMIT ? OFFSET ?',
            [$limit, $offset]
        );

        return array_map(fn ($row) => (array) $row, $results);
    }
}
