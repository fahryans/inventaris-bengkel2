<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
// Check if name column exists
$pdo->exec('ALTER TABLE users ADD name VARCHAR(255) AFTER status');
echo "Added name column after status\n";