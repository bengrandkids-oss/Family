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

            $table->ulid("primary");
            $table->foreign("primary")->references("member_id")->on("family_members");

            $table->ulid("secondary");
            $table->foreign("secondary")->references("member_id")->on("family_members");

            $table->enum('relationship_type',["father","mother","spouse"]);
            $table->timestamps();

            /*
             Examples:
             - father
             - mother
             - child
             - spouse
             - sibling
             */            

            // Prevent duplicate relationships
            $table->unique([
                'primary',
                "secondary",
                'relationship_type'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relations');
    }
};
