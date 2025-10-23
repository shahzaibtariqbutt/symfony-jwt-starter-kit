<?php

// Helps PHPStan, see https://github.com/phpstan/phpstan-doctrine#configuration

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

new Dotenv()->bootEnv(__DIR__.'/../.env');
$kernel = new Kernel('dev', true);
$kernel->boot();

return $kernel->getContainer()->get('doctrine')->getManager();
