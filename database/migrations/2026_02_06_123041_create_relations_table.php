<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('relations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('family_member_id')
                  ->constrained('family_members')
                  ->cascadeOnDelete();
            $table->foreignUlid('related_family_member_id')
                  ->constrained('family_members')
                  ->cascadeOnDelete();

            $table->string('relationship_type');
            /*
             Examples:
             - father
             - mother
             - child
             - spouse
             - sibling
             */

            $table->timestamps();

            // Prevent duplicate relationships
            $table->unique([
                'family_member_id',
                'related_family_member_id',
                'relationship_type'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relations');
    }
};
