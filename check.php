<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$sql = 'SHOW TABLES';
$rows = \Illuminate\Support\Facades\DB::select($sql);
$tables = array_column($rows, 'In_Tables');
echo 'Tables: ' . implode(', ', $tables);