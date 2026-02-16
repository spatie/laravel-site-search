<?php

namespace Spatie\SiteSearch\Drivers\Sqlite;

use PDO;

class QueryBuilder
{
    public function search(
        PDO $pdo,
        string $query,
        ?int $limit = null,
        int $offset = 0,
        array $searchParameters = []
    ): array {
        $limit = $limit ?? 20;

        if (empty(trim($query))) {
            return $this->getAllDocuments($pdo, $limit, $offset);
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
            WHERE documents_fts MATCH :query
            ORDER BY rank
            LIMIT :limit OFFSET :offset
        ';

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':query', $ftsQuery, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalCount(PDO $pdo, string $query): int
    {
        if (empty(trim($query))) {
            $stmt = $pdo->query('SELECT COUNT(*) FROM documents');

            return (int) $stmt->fetchColumn();
        }

        $ftsQuery = $this->prepareFtsQuery($query);

        $stmt = $pdo->prepare('
            SELECT COUNT(*) FROM documents_fts WHERE documents_fts MATCH :query
        ');
        $stmt->bindValue(':query', $ftsQuery, PDO::PARAM_STR);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
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

    protected function getAllDocuments(PDO $pdo, int $limit, int $offset): array
    {
        $stmt = $pdo->prepare('
            SELECT * FROM documents
            ORDER BY date_modified_timestamp DESC
            LIMIT :limit OFFSET :offset
        ');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
