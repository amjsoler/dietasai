<?php

//Script to autodeploy from github

// 1. Check the IP of the request ??

// 2. Check the authentication
$pass = (!empty($_GET['pass'])) ? $_GET['pass'] : '';

print_r($pass);
if($pass !== 'aj4haCmVUsqMj3q@DuYRNHZxbkFA') {
    die('No access!');
}
// 3. Navigate to the folder
print_r("cd .." . shell_exec("cd ../../dietasai" . "\n"));

// 4. php artisan down
print_r("php artisan down... " . shell_exec("php artisan down" . "\n"));

// 5. Pull the changes from the repository
print_r("git pull" . shell_exec("git pull" . "\n"));

// 6. php artisan migrate
print_r("migrate... " . shell_exec("php artisan migrate --force". "\n"));

// 7. php artisan optimize
print_r(shell_exec("php artisan optimize" . "\n"));

// 8. php artisan config:cache
print_r(shell_exec("php artisan config:cache" . "\n"));

// 9. php artisan route:cache
print_r(shell_exec("php artisan route:cache" . "\n"));

// 10. php artisan view:cache
print_r(shell_exec("php artisan view:cache" . "\n"));

// 11. php artisan up
print_r("php artisan up..." . shell_exec("php artisan up" . "\n"));
