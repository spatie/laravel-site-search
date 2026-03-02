<?php

namespace Tests\TestSupport\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\SiteSearch\Models\SiteSearchConfig;

class SiteSearchConfigFactory extends Factory
{
    protected $model = SiteSearchConfig::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'crawl_url' => 'http://localhost:8181',
            'index_base_name' => $this->faker->word,
            'enabled' => true,
            'number_of_urls_indexed' => 0,
        ];
    }
}
