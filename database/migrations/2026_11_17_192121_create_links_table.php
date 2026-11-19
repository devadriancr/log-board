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
        Schema::create('links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained('cards');
            $table->foreignId('application_id')->constrained('applications');
            $table->string('label', 255);
            $table->string('path', 255)->default('/');
            $table->text('description')->nullable();
            $table->boolean('show_description')->default(false);
            $table->boolean('open_in_new_tab')->default(true);
            $table->smallInteger('sort_order')->default(0);
            $table->unsignedTinyInteger('grid_x')->default(0);
            $table->unsignedSmallInteger('grid_y')->default(0);
            $table->unsignedTinyInteger('grid_w')->default(1);
            $table->boolean('is_active')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['card_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('links');
    }
};
