<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('families', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->string('family_name');  

            $table->text('description')->nullable();

            $table->ulid("father_id")->unique();
            $table->foreign("father_id")->references("member_id")->on("family_members")->cascadeOnDelete();

            // $table->foreignId('user_id')
            //       ->constrained("family_mambers")
            //       ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('families');
    }
};
