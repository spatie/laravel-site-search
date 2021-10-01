<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    public function up()
    {
        Schema::create('site_search_indices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('crawl_url');
            $table->string('driver_class')->nullable();
            $table->string('profile_class')->nullable();
            $table->string('index_base_name');
            $table->string('index_name')->nullable();
            $table->boolean('enabled')->default(true);
            $table->json('extra')->nullable();
            $table->string('pending_index_name')->nullable();
            $table->dateTime('crawling_started_at')->nullable();
            $table->dateTime('crawling_ended_at')->nullable();
            $table->timestamps();
        });
    }
};
