<?php

namespace Spatie\SiteSearch\Drivers\Sqlite;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;

class SchemaManager
{
    public function createSchema(Connection $connection): void
    {
        $this->createDocumentsTable($connection);
        $this->createFtsTable($connection);
        $this->createTriggers($connection);
    }

    public function documentCount(Connection $connection): int
    {
        return $connection->table('documents')->count();
    }

    protected function createDocumentsTable(Connection $connection): void
    {
        $schema = $connection->getSchemaBuilder();

        if ($schema->hasTable('documents')) {
            return;
        }

        $schema->create('documents', function (Blueprint $table) {
            $table->text('id')->primary();
            $table->text('url');
            $table->text('anchor')->nullable();
            $table->text('page_title')->nullable();
            $table->text('h1')->nullable();
            $table->text('entry')->nullable();
            $table->text('description')->nullable();
            $table->integer('date_modified_timestamp')->nullable();
            $table->text('extra')->nullable();
            $table->integer('created_at')->default(new Expression("(strftime('%s', 'now'))"));

            $table->index('url', 'idx_documents_url');
        });
    }

    protected function createFtsTable(Connection $connection): void
    {
        $connection->statement('
            CREATE VIRTUAL TABLE IF NOT EXISTS documents_fts USING fts5(
                id UNINDEXED,
                url,
                page_title,
                h1,
                entry,
                description,
                content=\'documents\',
                content_rowid=\'rowid\',
                tokenize=\'porter unicode61\'
            )
        ');
    }

    protected function createTriggers(Connection $connection): void
    {
        $connection->statement('
            CREATE TRIGGER IF NOT EXISTS documents_ai AFTER INSERT ON documents BEGIN
                INSERT INTO documents_fts(rowid, id, url, page_title, h1, entry, description)
                VALUES (NEW.rowid, NEW.id, NEW.url, NEW.page_title, NEW.h1, NEW.entry, NEW.description);
            END
        ');

        $connection->statement('
            CREATE TRIGGER IF NOT EXISTS documents_ad AFTER DELETE ON documents BEGIN
                INSERT INTO documents_fts(documents_fts, rowid, id, url, page_title, h1, entry, description)
                VALUES (\'delete\', OLD.rowid, OLD.id, OLD.url, OLD.page_title, OLD.h1, OLD.entry, OLD.description);
            END
        ');

        $connection->statement('
            CREATE TRIGGER IF NOT EXISTS documents_au AFTER UPDATE ON documents BEGIN
                INSERT INTO documents_fts(documents_fts, rowid, id, url, page_title, h1, entry, description)
                VALUES (\'delete\', OLD.rowid, OLD.id, OLD.url, OLD.page_title, OLD.h1, OLD.entry, OLD.description);
                INSERT INTO documents_fts(rowid, id, url, page_title, h1, entry, description)
                VALUES (NEW.rowid, NEW.id, NEW.url, NEW.page_title, NEW.h1, NEW.entry, NEW.description);
            END
        ');
    }
}
