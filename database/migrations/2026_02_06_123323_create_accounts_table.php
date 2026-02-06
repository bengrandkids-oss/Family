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
  $table->string('phone')->nullable();
          /*  i feel like hizi fields zitakuwa implied  based on hio foreign key so kuziredeclare ni redundant
            $table->string('first_name');
            $table->string('last_name');
          
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable(); */

            $table->string('profile_photo')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
