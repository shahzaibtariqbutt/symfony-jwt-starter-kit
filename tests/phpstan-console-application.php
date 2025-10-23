<?php

// Helps PHPStan, see https://github.com/phpstan/phpstan-symfony#analysis-of-symfony-console-commands

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Dotenv\Dotenv;
use Webmozart\Assert\Assert;

require __DIR__.'/../vendor/autoload.php';

new Dotenv()->bootEnv(__DIR__.'/../.env');

Assert::string($_SERVER['APP_ENV']);
$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);

return new Application($kernel);
