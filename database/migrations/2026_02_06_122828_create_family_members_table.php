<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_members', function (Blueprint $table) {

            $table->ulid('member_id')->primary();            
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string("family_name")->nullable();
            $table->enum("gender",["male","female"]);
            $table->date('date_of_birth');
            $table->date('date_of_death')->nullable();
            $table->string('photo')->nullable();       

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_members');
    }
};
