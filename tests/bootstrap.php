<?php

/*
 * PHPUnit bootstrap.
 *
 * Sistem php.ini menggunakan variables_order tanpa 'E' sehingga $_ENV kosong
 * dan Dotenv (immutable) yang hanya membaca $_ENV mengira variabel dari
 * phpunit.xml belum ada, lalu menimpanya dengan nilai .env.
 * Salin $_SERVER (diset oleh <server>) ke $_ENV sebelum Laravel boot agar
 * variabel test (APP_ENV=testing, DB sqlite, SESSION array) terhormati.
 */

require __DIR__ . '/../vendor/autoload.php';

foreach ($_SERVER as $key => $value) {
    if (is_string($value) && !array_key_exists($key, $_ENV)) {
        $_ENV[$key] = $value;
    }
}