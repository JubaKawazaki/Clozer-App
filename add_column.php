<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::table('personal_access_tokens', function (Blueprint $table) {
    if (!Schema::hasColumn('personal_access_tokens', 'last_used_at')) {
        $table->timestamp('last_used_at')->nullable()->after('expires_at');
    }
});
echo "Column added/exists.\n";