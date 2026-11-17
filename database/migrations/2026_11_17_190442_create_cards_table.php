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
            $table->foreignId('board_id')->nullable()->constrained('boards');
            $table->string('title', 255);
            $table->unsignedTinyInteger('grid_x');
            $table->unsignedTinyInteger('grid_y');
            $table->unsignedTinyInteger('grid_w');
            $table->unsignedTinyInteger('grid_h');
            $table->unsignedTinyInteger('link_columns')->default(1);
            $table->boolean('is_active')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
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
