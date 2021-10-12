<?php

namespace Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\SiteSearch\Models\SiteSearchIndex;

class SiteSearchIndexFactory extends Factory
{
    protected $model = SiteSearchIndex::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'crawl_url' => 'http://localhost:8181',
            'index_base_name' => $this->faker->word,
            'enabled' => true,
        ];
    }
}
