<?php

namespace Spatie\SiteSearch\Drivers\Sqlite;

use PDO;

class SchemaManager
{
    public function createSchema(PDO $pdo): void
    {
        $this->createDocumentsTable($pdo);
        $this->createFtsTable($pdo);
        $this->createTriggers($pdo);
    }

    public function documentCount(PDO $pdo): int
    {
        $stmt = $pdo->query('SELECT COUNT(*) FROM documents');

        return (int) $stmt->fetchColumn();
    }

    protected function createDocumentsTable(PDO $pdo): void
    {
        $pdo->exec('
            CREATE TABLE IF NOT EXISTS documents (
                id TEXT PRIMARY KEY,
                url TEXT NOT NULL,
                page_title TEXT,
                h1 TEXT,
                entry TEXT,
                description TEXT,
                date_modified_timestamp INTEGER,
                extra TEXT,
                created_at INTEGER DEFAULT (strftime(\'%s\', \'now\'))
            )
        ');

        $pdo->exec('
            CREATE INDEX IF NOT EXISTS idx_documents_url
            ON documents(url)
        ');
    }

    protected function createFtsTable(PDO $pdo): void
    {
        $pdo->exec('
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

    protected function createTriggers(PDO $pdo): void
    {
        $pdo->exec('
            CREATE TRIGGER IF NOT EXISTS documents_ai AFTER INSERT ON documents BEGIN
                INSERT INTO documents_fts(rowid, id, url, page_title, h1, entry, description)
                VALUES (NEW.rowid, NEW.id, NEW.url, NEW.page_title, NEW.h1, NEW.entry, NEW.description);
            END
        ');

        $pdo->exec('
            CREATE TRIGGER IF NOT EXISTS documents_ad AFTER DELETE ON documents BEGIN
                INSERT INTO documents_fts(documents_fts, rowid, id, url, page_title, h1, entry, description)
                VALUES (\'delete\', OLD.rowid, OLD.id, OLD.url, OLD.page_title, OLD.h1, OLD.entry, OLD.description);
            END
        ');

        $pdo->exec('
            CREATE TRIGGER IF NOT EXISTS documents_au AFTER UPDATE ON documents BEGIN
                INSERT INTO documents_fts(documents_fts, rowid, id, url, page_title, h1, entry, description)
                VALUES (\'delete\', OLD.rowid, OLD.id, OLD.url, OLD.page_title, OLD.h1, OLD.entry, OLD.description);
                INSERT INTO documents_fts(rowid, id, url, page_title, h1, entry, description)
                VALUES (NEW.rowid, NEW.id, NEW.url, NEW.page_title, NEW.h1, NEW.entry, NEW.description);
            END
        ');
    }
}
