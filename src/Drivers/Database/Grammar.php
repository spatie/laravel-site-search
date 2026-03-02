<?php

namespace Spatie\SiteSearch\Drivers\Database;

use Illuminate\Database\Connection;

abstract class Grammar
{
    abstract public function ensureFtsSetup(Connection $connection): void;

    abstract public function search(Connection $connection, string $indexName, string $query, int $limit, int $offset): array;

    abstract public function getTotalCount(Connection $connection, string $indexName, string $query): int;

    public function escapeSearchTerm(string $query): string
    {
        $escaped = str_replace(
            ['"', '*', '(', ')', ':'],
            ['', '', '', '', ''],
            $query
        );

        $escaped = preg_replace('/\b(OR|AND|NOT)\b/i', '', $escaped);

        return trim($escaped);
    }

    public function getAllDocuments(Connection $connection, string $indexName, int $limit, int $offset): array
    {
        return $connection->table('site_search_documents')
            ->where('index_name', $indexName)
            ->orderByDesc('date_modified_timestamp')
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }
}
