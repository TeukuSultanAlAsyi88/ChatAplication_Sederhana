<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('owner_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('target_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('saved_name');
            $table->string('phone');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};