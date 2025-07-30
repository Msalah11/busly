<?php

declare(strict_types=1);

use App\Enums\BusType;
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
        Schema::create('buses', function (Blueprint $table): void {
            $table->id();
            $table->string('bus_code')->unique();
            $table->integer('capacity');
            $table->enum('type', array_column(BusType::cases(), 'value'))->default(BusType::STANDARD->value);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buses');
    }
};
