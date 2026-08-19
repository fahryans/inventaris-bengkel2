<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
\Illuminate\Support\Facades\DB::connection()->getPdo()->query('SELECT * FROM users LIMIT 1');
echo "Query executed\n";