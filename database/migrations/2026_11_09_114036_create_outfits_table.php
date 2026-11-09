<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outfits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('photo_path')->nullable();
            // Photo de la tenue portée, la première fois qu'elle est loguée.
            $table->date('last_worn_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });

        // Couches : slot = la rangée Build de Closet ('headwear','outerwear',
        // 'top','bottom','shoes'), position = nombre de pièces dans le même
        // slot (0..2, max 3 couches de tops/outerwear).
        Schema::create('outfit_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outfit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->string('slot');
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['outfit_id', 'item_id']);
            $table->index(['outfit_id', 'slot', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outfit_items');
        Schema::dropIfExists('outfits');
    }
};
