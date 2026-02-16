<?php

namespace Spatie\SiteSearch\Drivers\Sqlite;

use PDO;

class DatabaseManager
{
    /** @var array<string, PDO> */
    protected array $connections = [];

    public function __construct(
        protected string $storagePath
    ) {
    }

    public function getPath(string $indexName): string
    {
        $safeName = $this->sanitizeIndexName($indexName);

        return "{$this->storagePath}/{$safeName}.sqlite";
    }

    public function getTempPath(string $indexName): string
    {
        return $this->getPath($indexName) . '.tmp';
    }

    public function connect(string $indexName, bool $useTemp = false): PDO
    {
        if (! $useTemp) {
            $this->swapTempIfExists($indexName);
        }

        $path = $useTemp ? $this->getTempPath($indexName) : $this->getPath($indexName);
        $cacheKey = $path;

        if (! isset($this->connections[$cacheKey])) {
            $this->ensureDirectoryExists();

            $pdo = new PDO("sqlite:{$path}");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec('PRAGMA journal_mode=WAL');
            $pdo->exec('PRAGMA synchronous=NORMAL');

            $this->connections[$cacheKey] = $pdo;
        }

        return $this->connections[$cacheKey];
    }

    public function exists(string $indexName): bool
    {
        return file_exists($this->getPath($indexName));
    }

    public function tempExists(string $indexName): bool
    {
        return file_exists($this->getTempPath($indexName));
    }

    public function delete(string $indexName): void
    {
        $path = $this->getPath($indexName);

        $this->closeConnection($path);

        $this->deleteFileAndWal($path);
    }

    public function deleteTempIfExists(string $indexName): void
    {
        $tempPath = $this->getTempPath($indexName);

        if (file_exists($tempPath)) {
            $this->closeConnection($tempPath);
            $this->deleteFileAndWal($tempPath);
        }
    }

    public function atomicSwap(string $indexName): void
    {
        $tempPath = $this->getTempPath($indexName);
        $finalPath = $this->getPath($indexName);

        if (! file_exists($tempPath)) {
            return;
        }

        $this->closeConnection($tempPath);
        $this->closeConnection($finalPath);

        $this->checkpointWal($tempPath);
        $this->deleteFileAndWal($tempPath, walOnly: true);
        $this->deleteFileAndWal($finalPath);

        rename($tempPath, $finalPath);
    }

    public function allIndexNames(): array
    {
        $this->ensureDirectoryExists();

        $files = glob("{$this->storagePath}/*.sqlite") ?: [];

        return array_map(
            fn (string $file) => pathinfo($file, PATHINFO_FILENAME),
            $files
        );
    }

    protected function swapTempIfExists(string $indexName): void
    {
        if ($this->tempExists($indexName)) {
            $this->atomicSwap($indexName);
        }
    }

    protected function sanitizeIndexName(string $indexName): string
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '_', $indexName);
    }

    protected function closeConnection(string $path): void
    {
        if (isset($this->connections[$path])) {
            unset($this->connections[$path]);
        }
    }

    protected function checkpointWal(string $path): void
    {
        if (file_exists($path)) {
            $pdo = new PDO("sqlite:{$path}");
            $pdo->exec('PRAGMA wal_checkpoint(TRUNCATE)');
            $pdo = null;
        }
    }

    protected function deleteFileAndWal(string $path, bool $walOnly = false): void
    {
        $files = ["{$path}-wal", "{$path}-shm"];

        if (! $walOnly) {
            array_unshift($files, $path);
        }

        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    protected function ensureDirectoryExists(): void
    {
        if (! is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }
}
