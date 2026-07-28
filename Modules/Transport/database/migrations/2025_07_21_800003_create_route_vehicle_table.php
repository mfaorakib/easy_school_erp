<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Which vehicles serve a route (a route can have many vehicles). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_vehicle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_route_id')->constrained('transport_routes')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['transport_route_id', 'vehicle_id'], 'route_vehicle_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_vehicle');
    }
};
