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
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->foreignId('id')
                ->unique()
                ->primary()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            // User roles is an internal feature, don't use as a policy
            $table->json('roles');
            $table->json('users')->nullable();
            $table->json('products')->nullable();
            $table->json('categories')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};
