<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
           $table->ulid('id')->primary();
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();

            $table->string('profile_photo')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
