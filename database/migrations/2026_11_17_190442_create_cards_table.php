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
            $table->tinyUnsignedInteger('grid_x');
            $table->tinyUnsignedInteger('grid_y');
            $table->tinyUnsignedInteger('grid_w');
            $table->tinyUnsignedInteger('grid_h');
            $table->tinyUnsignedInteger('link_columns')->default(1);
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
