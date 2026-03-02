<?php

namespace Spatie\SiteSearch\Drivers\Database;

use Illuminate\Database\Connection;

class PostgresGrammar extends Grammar
{
    protected const TEXT_SEARCH_CONFIG = 'site_search';

    public function ensureFtsSetup(Connection $connection): void
    {
        $this->ensureTextSearchConfig($connection);

        $hasColumn = $connection->select("
            SELECT column_name FROM information_schema.columns
            WHERE table_name = 'site_search_documents' AND column_name = 'search_vector'
        ");

        if (empty($hasColumn)) {
            $connection->statement('ALTER TABLE site_search_documents ADD COLUMN search_vector tsvector');

            $connection->statement('
                CREATE INDEX site_search_documents_search_idx ON site_search_documents USING gin(search_vector)
            ');

            $connection->statement("
                CREATE OR REPLACE FUNCTION site_search_documents_update_search_vector() RETURNS trigger AS \$\$
                BEGIN
                    NEW.search_vector :=
                        setweight(to_tsvector('site_search', COALESCE(NEW.entry, '')), 'A') ||
                        setweight(to_tsvector('site_search', COALESCE(NEW.description, '')), 'B') ||
                        setweight(to_tsvector('site_search', COALESCE(NEW.page_title, '')), 'C') ||
                        setweight(to_tsvector('site_search', COALESCE(NEW.h1, '')), 'C') ||
                        setweight(to_tsvector('site_search', COALESCE(NEW.url, '')), 'D');
                    RETURN NEW;
                END;
                \$\$ LANGUAGE plpgsql
            ");

            $connection->statement('
                CREATE TRIGGER site_search_documents_vector_update
                BEFORE INSERT OR UPDATE ON site_search_documents
                FOR EACH ROW EXECUTE FUNCTION site_search_documents_update_search_vector()
            ');

            $connection->statement("
                UPDATE site_search_documents SET search_vector =
                    setweight(to_tsvector('site_search', COALESCE(entry, '')), 'A') ||
                    setweight(to_tsvector('site_search', COALESCE(description, '')), 'B') ||
                    setweight(to_tsvector('site_search', COALESCE(page_title, '')), 'C') ||
                    setweight(to_tsvector('site_search', COALESCE(h1, '')), 'C') ||
                    setweight(to_tsvector('site_search', COALESCE(url, '')), 'D')
            ");
        }
    }

    public function search(Connection $connection, string $indexName, string $query, int $limit, int $offset): array
    {
        if (empty(trim($query))) {
            return $this->getAllDocuments($connection, $indexName, $limit, $offset);
        }

        $tsQuery = $this->prepareTsQuery($query);

        if (empty($tsQuery)) {
            return [];
        }

        return $connection->table('site_search_documents')
            ->select('*')
            ->selectRaw("ts_rank(search_vector, to_tsquery('site_search', ?)) as relevance", [$tsQuery])
            ->selectRaw("ts_headline('site_search', COALESCE(entry, ''), to_tsquery('site_search', ?), 'StartSel=<em>,StopSel=</em>,MaxFragments=1,MaxWords=50,MinWords=20') as entry_highlighted", [$tsQuery])
            ->selectRaw("ts_headline('site_search', COALESCE(description, ''), to_tsquery('site_search', ?), 'StartSel=<em>,StopSel=</em>,MaxFragments=1,MaxWords=50,MinWords=20') as description_highlighted", [$tsQuery])
            ->where('index_name', $indexName)
            ->whereRaw("search_vector @@ to_tsquery('site_search', ?)", [$tsQuery])
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

        $tsQuery = $this->prepareTsQuery($query);

        if (empty($tsQuery)) {
            return 0;
        }

        return $connection->table('site_search_documents')
            ->where('index_name', $indexName)
            ->whereRaw("search_vector @@ to_tsquery('site_search', ?)", [$tsQuery])
            ->count();
    }

    protected function ensureTextSearchConfig(Connection $connection): void
    {
        $exists = $connection->select("
            SELECT 1 FROM pg_ts_config WHERE cfgname = 'site_search'
        ");

        if (! empty($exists)) {
            return;
        }

        $connection->statement('
            CREATE TEXT SEARCH DICTIONARY site_search_stem (
                TEMPLATE = snowball,
                Language = english
            )
        ');

        $connection->statement('CREATE TEXT SEARCH CONFIGURATION site_search (COPY = simple)');

        $connection->statement('
            ALTER TEXT SEARCH CONFIGURATION site_search
            ALTER MAPPING FOR asciiword, asciihword, hword_asciipart, word, hword, hword_part
            WITH site_search_stem
        ');
    }

    protected function prepareTsQuery(string $query): string
    {
        $escaped = $this->escapeSearchTerm($query);

        $words = preg_split('/\s+/', $escaped, -1, PREG_SPLIT_NO_EMPTY);

        if (empty($words)) {
            return '';
        }

        $parts = array_map(
            fn (string $word) => preg_replace('/[^a-zA-Z0-9]/', '', $word).':*',
            $words
        );

        $parts = array_filter($parts, fn (string $part) => $part !== ':*');

        if (empty($parts)) {
            return '';
        }

        return implode(' & ', $parts);
    }
}
