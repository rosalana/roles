<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Rosalana\Roles\Models\Role;

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

            $table->index(['roleable_type', 'roleable_id'], 'role_roleable_index');
            $table->unique(['name', 'roleable_type', 'roleable_id'], 'unique_role_per_roleable');
        });

        // Schema::create('assigned_roles', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
        //     $table->string('assignee_type'); // e.g., 'App\Models\User'
        //     $table->unsignedBigInteger('assignee_id');
        //     $table->timestamps();

        //     $table->index(['assignee_type', 'assignee_id'], 'role_assignee_index');
        //     $table->unique(['role_id', 'assignee_type', 'assignee_id'], 'unique_assigned_role');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void 
    {
        Schema::dropIfExists('roles');
        // Schema::dropIfExists('assigned_roles');
    }
};
