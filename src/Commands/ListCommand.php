<?php

namespace Spatie\SiteSearch\Commands;

use Illuminate\Console\Command;
use MeiliSearch\Exceptions\ApiException;
use Spatie\SiteSearch\Models\SiteSearchConfig;

class ListCommand extends Command
{
    protected $signature = 'site-search:list';

    public function handle()
    {
        [$headers, $rows] = $this->getHeadersAndRows();

        if (count($rows) === 0) {
            $this->warn("You need to run php artisan site-search:create first");
        }

        $this->info('Site search configs');
        $this->info('-------------------');

        $this->table($headers, $rows);
    }

    public function getStatus(SiteSearchConfig $searchConfig): string
    {
        if (! $searchConfig->index_name) {
            return "⚠️ Waiting on first crawl";
        }

        if ($searchConfig->pending_index_name) {
            return "🕷 Crawling...";
        }

        try {
            if ($searchConfig->getDriver()->isProcessing($searchConfig->index_name)) {
                return "⚠️ Processing...";
            }
        } catch (ApiException) {
            return "🚨 Did not find index";
        }


        return "✅ OK";
    }

    protected function getHeadersAndRows(): array
    {
        $headers = [
            'Name',
            'Crawl URL',
            'Real index name',
            'Status',
            '# Indexed URLs',
            '# Documents',
            'Lastest crawl ended at',
        ];

        $rows = SiteSearchConfig::all()->map(function (SiteSearchConfig $config) {
            return [
                $config->name,
                $config->crawl_url,
                $config->index_name,
                $this->getStatus($config),
                $config->number_of_urls_indexed,
                $config->index_name
                    ? $config->getDriver()->documentCount($config->index_name)
                    : '',
                $config->crawling_ended_at,
            ];
        });

        return [$headers, $rows];
    }
}
