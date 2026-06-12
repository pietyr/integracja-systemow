<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('synced_periods', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedInteger('records_synced')->default(0);
            $table->timestamp('synced_at');
            $table->timestamps();

            $table->unique(['source', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('synced_periods');
    }
};
