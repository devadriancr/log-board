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
        Schema::create('link_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('link_id')->constrained('links');
            $table->string('value', 255);
            $table->string('label', 255);
            $table->smallInteger('sort_order')->default(0); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('link_options');
    }
};
