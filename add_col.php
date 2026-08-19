<?php
// add_name_column.php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$db = \Illuminate\Support\Facades\DB::connection()->getPdo();
$db->exec('ALTER TABLE users ADD name VARCHAR(255)');
echo "Added name column\n";