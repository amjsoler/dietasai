<?php

//Script to autodeploy from github

// 1. Check the IP of the request ??

// 2. Check the authentication
$pass = (!empty($_GET['pass'])) ? $_GET['pass'] : '';

print_r($pass);
if($pass !== 'aj4haCmVUsqMj3q@DuYRNHZxbkFA') {
    die('No access!');
}

// 4. php artisan down
print_r("php artisan down... " . shell_exec("php ../../dietasai/artisan down" . "\n"));

// 4.1. php artisan optimize:clear
print_r("php artisan optimize:clear... " . shell_exec("php artisan optimize:clear" . "\n"));

// 5. Pull the changes from the repository
print_r("git pull" . shell_exec("git pull" . "\n"));

// 5.1. composer install
print_r("composer install... " . shell_exec("composer install" . "\n"));

// 5.2. composer dump-autoload
print_r("composer dump-autoload... " . shell_exec("composer dump-autoload" . "\n"));

// 5.3. composer update
print_r("composer update... " . shell_exec("composer update" . "\n"));

// 6. php artisan migrate
print_r("migrate... " . shell_exec("php artisan migrate --force". "\n"));

// 7. php artisan optimize
print_r(shell_exec("php artisan optimize" . "\n"));

// 11. php artisan up
print_r("php artisan up..." . shell_exec("php artisan up" . "\n"));
