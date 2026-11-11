<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Item;

$u = User::firstOrCreate(['email' => 'test@clozer.app'], ['name' => 'Test', 'password' => bcrypt('password')]);
echo "User: {$u->id}\n";

$it = Item::updateOrCreate(
    ['user_id' => $u->id, 'photo_path' => 'items/originals/test.webp'],
    ['name' => 'T-shirt blanc', 'categories' => ['top'], 'weather' => [], 'styles' => [], 'dress' => false, 'archived' => false]
);
echo "Item: {$it->id}\n";

$token = $u->createToken('test')->plainTextToken;
echo "Token: {$token}\n";