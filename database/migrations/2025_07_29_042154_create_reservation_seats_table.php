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
        Schema::create('reservation_seats', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->onDelete('cascade');
            $table->integer('seat_number');
            $table->timestamps();

            $table->unique(['reservation_id', 'seat_number']);
            $table->index(['reservation_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_seats');
    }
};
