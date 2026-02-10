php<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
           $table->ulid('id')->primary();
           
            // $table->foreignId('user_id')
            //       ->constrained()
            //       ->cascadeOnDelete();

            // $table->foreignUlid('family_member_id')
            //       ->nullable()
            //       ->constrained('family_members')
            //       ->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->string('event_type'); 
            $table->date('event_date');
            $table->string('location')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
