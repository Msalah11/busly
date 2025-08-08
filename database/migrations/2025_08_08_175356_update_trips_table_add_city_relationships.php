<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table): void {
            $table->foreignId('origin_city_id')->after('bus_id')->constrained('cities');
            $table->foreignId('destination_city_id')->after('origin_city_id')->constrained('cities');
            
            $table->index(['origin_city_id', 'destination_city_id']);
            $table->index(['origin_city_id', 'departure_time']);
            $table->index(['destination_city_id', 'departure_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table): void {
            $table->dropForeign(['origin_city_id']);
            $table->dropForeign(['destination_city_id']);
            $table->dropColumn(['origin_city_id', 'destination_city_id']);
        });
    }
};