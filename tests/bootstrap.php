<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    if ('test' === $_SERVER['APP_ENV']) {
        (new Dotenv())->bootEnv(dirname(__DIR__).'/.env.test' );
    } else {
        (new Dotenv())->bootEnv(dirname(__DIR__).'/.env' );
    }
}
