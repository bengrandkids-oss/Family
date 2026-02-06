<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_members', function (Blueprint $table) {
            $table->ulid('id')->primary();

            
            //$table->foreignId('user_id')
              //    ->nullable()
               //   ->constrained('users')
                //->nullOnDelete();

            
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('gender', 10)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->date('date_of_death')->nullable();
            $table->string('photo')->nullable();
            

            
            $table->boolean('is_root')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_members');
    }
};
