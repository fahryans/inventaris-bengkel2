<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
$pdo->exec('DESCRIBE users');
echo "Done\n";