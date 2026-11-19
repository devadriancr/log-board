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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained('boards');
            $table->string('title', 255);
            $table->string('icon', 50)->nullable();
            $table->string('type', 10)->default('links'); // links | text
            $table->longText('content')->nullable(); // sanitized HTML of the text cards
            $table->string('color', 7)->nullable(); // header colour, #rrggbb
            $table->unsignedTinyInteger('grid_x');
            $table->unsignedSmallInteger('grid_y');
            $table->unsignedTinyInteger('grid_w');
            $table->unsignedTinyInteger('grid_h');
            $table->boolean('is_active')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['board_id', 'grid_y', 'grid_x']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
