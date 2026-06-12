<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indicators', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('category');
            $table->string('unit')->nullable();
            $table->string('source')->default('gus');
            $table->unsignedBigInteger('gus_variable_id')->nullable();
            $table->timestamps();
        });

        Schema::create('indicator_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indicator_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->decimal('value', 16, 4);
            $table->timestamps();

            $table->unique(['indicator_id', 'year']);
            $table->index('year');
        });

        Schema::create('news_articles', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique();
            $table->string('headline');
            $table->text('snippet')->nullable();
            $table->string('section')->nullable();
            $table->string('subsection')->nullable();
            $table->date('published_at')->nullable();
            $table->string('url')->nullable();
            $table->json('keywords')->nullable();
            $table->timestamps();

            $table->index('published_at');
            $table->index('section');
        });

        Schema::create('sync_runs', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->string('status');
            $table->unsignedInteger('records_synced')->default(0);
            $table->text('message')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['source', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_runs');
        Schema::dropIfExists('news_articles');
        Schema::dropIfExists('indicator_values');
        Schema::dropIfExists('indicators');
    }
};
