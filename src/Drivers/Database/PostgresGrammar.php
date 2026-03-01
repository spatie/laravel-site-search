<?php

namespace Spatie\SiteSearch\Drivers\Database;

use Illuminate\Database\Connection;

class PostgresGrammar extends Grammar
{
    public function ensureFtsSetup(Connection $connection): void
    {
        $hasColumn = $connection->select("
            SELECT column_name FROM information_schema.columns
            WHERE table_name = 'site_search_documents' AND column_name = 'search_vector'
        ");

        if (empty($hasColumn)) {
            $connection->statement('ALTER TABLE site_search_documents ADD COLUMN search_vector tsvector');

            $connection->statement("
                CREATE INDEX site_search_documents_search_idx ON site_search_documents USING gin(search_vector)
            ");

            $connection->statement("
                CREATE OR REPLACE FUNCTION site_search_documents_update_search_vector() RETURNS trigger AS $$
                BEGIN
                    NEW.search_vector :=
                        setweight(to_tsvector('english', COALESCE(NEW.entry, '')), 'A') ||
                        setweight(to_tsvector('english', COALESCE(NEW.description, '')), 'B') ||
                        setweight(to_tsvector('english', COALESCE(NEW.page_title, '')), 'C') ||
                        setweight(to_tsvector('english', COALESCE(NEW.h1, '')), 'C') ||
                        setweight(to_tsvector('english', COALESCE(NEW.url, '')), 'D');
                    RETURN NEW;
                END;
                $$ LANGUAGE plpgsql
            ");

            $connection->statement("
                CREATE TRIGGER site_search_documents_vector_update
                BEFORE INSERT OR UPDATE ON site_search_documents
                FOR EACH ROW EXECUTE FUNCTION site_search_documents_update_search_vector()
            ");

            $connection->statement("
                UPDATE site_search_documents SET search_vector =
                    setweight(to_tsvector('english', COALESCE(entry, '')), 'A') ||
                    setweight(to_tsvector('english', COALESCE(description, '')), 'B') ||
                    setweight(to_tsvector('english', COALESCE(page_title, '')), 'C') ||
                    setweight(to_tsvector('english', COALESCE(h1, '')), 'C') ||
                    setweight(to_tsvector('english', COALESCE(url, '')), 'D')
            ");
        }
    }

    public function search(Connection $connection, string $indexName, string $query, int $limit, int $offset): array
    {
        if (empty(trim($query))) {
            return $this->getAllDocuments($connection, $indexName, $limit, $offset);
        }

        return $connection->table('site_search_documents')
            ->select('*')
            ->selectRaw("ts_rank(search_vector, plainto_tsquery('english', ?)) as relevance", [$query])
            ->selectRaw("ts_headline('english', COALESCE(entry, ''), plainto_tsquery('english', ?), 'StartSel=<em>,StopSel=</em>,MaxFragments=1,MaxWords=50,MinWords=20') as entry_highlighted", [$query])
            ->selectRaw("ts_headline('english', COALESCE(description, ''), plainto_tsquery('english', ?), 'StartSel=<em>,StopSel=</em>,MaxFragments=1,MaxWords=50,MinWords=20') as description_highlighted", [$query])
            ->where('index_name', $indexName)
            ->whereRaw("search_vector @@ plainto_tsquery('english', ?)", [$query])
            ->orderByDesc('relevance')
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

        return $connection->table('site_search_documents')
            ->where('index_name', $indexName)
            ->whereRaw("search_vector @@ plainto_tsquery('english', ?)", [$query])
            ->count();
    }

}
