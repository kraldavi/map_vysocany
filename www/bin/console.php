<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$bootstrap = new App\Bootstrap();

$container = $bootstrap->bootConsoleApplication();

$application = $container->getByType(Contributte\Console\Application::class);

// Run application.

exit($application->run());
