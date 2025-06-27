<?php

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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., 'admin', 'editor', 'viewer'
            $table->string('roleable_type'); // e.g., 'App\Models\Workspace'
            $table->unsignedBigInteger('roleable_id')->nullable();
            $table->json('permissions')->nullable(); // Store permissions as JSON
            $table->timestamps();

            $table->index(['roleable_type', 'roleable_id']);
            $table->unique(['name', 'roleable_type', 'roleable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void 
    {
        Schema::dropIfExists('roles');
    }
};
