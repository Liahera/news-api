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
        Schema::create('news_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_id')
                ->constrained('news')
                ->onDelete('cascade');

            $table->enum('type', [
                'text',
                'image',
                'text_image_left',
                'text_image_right',
            ]);
            $table->text('text')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_blocks');
    }
};
