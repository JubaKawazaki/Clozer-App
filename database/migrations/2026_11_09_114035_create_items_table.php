<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Les trois familles de tags de Closet (web/src/api.ts) : une pièce peut en
     * porter plusieurs de chaque type ; vide = indifférent (météo) / non classé.
     * [PLACEHOLDER:tags-relation] — phase sociale : tags partagés entre
     * utilisateurs (hashtags). Changer ces colonnes json en table pivot ne
     * touche que les modèles + filtres, pas les contrôleurs ni les écrans.
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('photo_path');              // photo d'origine, jamais réécrite
            $table->string('thumb_path')->nullable();  // version recadrée (rembg plus tard)
            $table->json('categories');                // ['top', 'outerwear', …]
            $table->json('weather');                   // ['hot','rainy', …] — vide = toute météo
            $table->json('styles');                    // ['casual', …]
            $table->boolean('dress')->default(false);  // se porte sans bas ni manteau
            $table->boolean('archived')->default(false);
            // Miroir du wear log (denormalisation, comme lastWorn côté Closet).
            $table->date('last_worn_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'archived']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
