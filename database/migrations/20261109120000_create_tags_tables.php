<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Multi-value tags (category, weather, style), user-editable style list.
     * Ported from Closet server/migrations/002_tags_layers.sql.
     */
    public function up(): void
    {
        Schema::create('item_tags', function (Blueprint $table) {
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->string('kind')->check("kind IN ('category', 'weather', 'style')");
            $table->string('value');
            $table->primary(['item_id', 'kind', 'value']);
            $table->index(['kind', 'value']);
        });

        // Carry forward existing tags from items.categories / weather / styles
        $rows = DB::table('items')->select(['id', 'categories', 'weather', 'styles'])->get();
        $inserts = [];
        foreach ($rows as $row) {
            foreach (json_decode($row->categories ?? '[]', true) as $v) {
                $inserts[] = ['item_id' => $row->id, 'kind' => 'category', 'value' => $v];
            }
            foreach (json_decode($row->weather ?? '[]', true) as $v) {
                $inserts[] = ['item_id' => $row->id, 'kind' => 'weather', 'value' => $v];
            }
            foreach (json_decode($row->styles ?? '[]', true) as $v) {
                $inserts[] = ['item_id' => $row->id, 'kind' => 'style', 'value' => $v];
            }
        }
        if ($inserts) DB::table('item_tags')->insert($inserts);

        Schema::create('styles', function (Blueprint $table) {
            $table->string('name')->primary();
            $table->unsignedInteger('position')->default(0);
        });
        DB::table('styles')->insert([
            ['name' => 'Casual', 'position' => 1],
            ['name' => 'Work', 'position' => 2],
            ['name' => 'Dressy', 'position' => 3],
            ['name' => 'Athletic', 'position' => 4],
            ['name' => 'Lounge', 'position' => 5],
            ['name' => 'Outdoor', 'position' => 6],
        ]);
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('items', function (Blueprint $table) {
            // Restore columns (no-op: they still exist, we keep them for back-compat)
        });
        Schema::disableForeignKeyConstraints();
        Schema::table('item_tags', function (Blueprint $table) {
            $table->drop();
        });
        Schema::table('styles', function (Blueprint $table) {
            $table->drop();
        });
        Schema::enableForeignKeyConstraints();
    }
};