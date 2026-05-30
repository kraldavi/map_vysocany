<?php

declare(strict_types=1);

namespace App;

use Nette;
use Nette\Bootstrap\Configurator;

class Bootstrap
{
	private readonly Configurator $configurator;
	private readonly string $rootDir;


	public function __construct()
	{
		$this->rootDir = dirname(__DIR__);
		$this->configurator = new Configurator();
		$this->configurator->setTempDirectory($this->rootDir . '/temp');
	}


	public function bootWebApplication(): Nette\DI\Container
	{
		$this->initializeEnvironment();
		$this->setupContainer(consoleMode: false);
		return $this->configurator->createContainer();
	}

	public function bootConsoleApplication(): Nette\DI\Container
	{
		$this->initializeEnvironment();
		$this->setupContainer(consoleMode: true);
		return $this->configurator->createContainer();
	}


	public function initializeEnvironment(): void
	{
		$this->configurator->setDebugMode(true); // enable for your remote IP
		$this->configurator->enableTracy($this->rootDir . '/log');

		$this->configurator->createRobotLoader()
			->addDirectory(__DIR__)
			->register();
	}


	private function setupContainer(bool $consoleMode): void
	{
		$configDir = $this->rootDir . '/config';

		$this->configurator->addStaticParameters([
			'consoleMode' => $consoleMode,
		]);

		$this->configurator->addDynamicParameters([
			'dbHost' => getenv('DB_HOST') ?: 'db',
			'dbName' => getenv('DB_DATABASE') ?: 'mapa_vysocany',
			'dbUser' => getenv('DB_USER') ?: 'user',
			'dbPassword' => getenv('DB_PASSWORD') ?: 'secret',
			'apiKey' => getenv('API_KEY') ?: '',
		]);

		$this->configurator->addConfig($configDir . '/common.neon');
		$this->configurator->addConfig($configDir . '/extensions.neon');

		// common.local.neon is for PHP on the host (dbHost 127.0.0.1); in Docker use DB_* from compose.
		$localConfig = $configDir . '/common.local.neon';
		if (is_file($localConfig) && !is_file('/.dockerenv')) {
			$this->configurator->addConfig($localConfig);
		}

		$this->configurator->addConfig($configDir . '/services.neon');
	}
}
