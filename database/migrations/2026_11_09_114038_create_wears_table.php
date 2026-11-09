<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Le wear log de Closet : ce qui a été porté, jour par jour. Une entrée
     * peut référencer une tenue (outfit_id) ou décrire un port libre via
     * wear_items. La tenue relogée garde un history même si ses items varient.
     */
    public function up(): void
    {
        Schema::create('wears', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('worn_on');
            $table->foreignId('outfit_id')->nullable()->constrained()->cascadeOnSetNull();
            $table->string('photo_path')->nullable();
            $table->timestamps();

            // Un seul port par jour et par tenue (le port libre est abordé
            // par le couple wear_items) ; deduplication du double-clic.
            $table->index(['user_id', 'worn_on']);
        });

        Schema::create('wear_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wear_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->string('slot')->nullable();
            $table->timestamps();

            $table->unique(['wear_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wear_items');
        Schema::dropIfExists('wears');
    }
};
