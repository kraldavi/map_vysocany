<?php

declare(strict_types=1);

use App\Bootstrap;
use App\Model\Orm;
use Nette\DI\Container;
use Nextras\Dbal\Connection;
use Tester\Environment;

require __DIR__ . '/../vendor/autoload.php';

Environment::setup();

const TEST_DATABASE_DEFAULT = 'mapa_vysocany_test';

function testContainer(): Container
{
	static $container;

	if ($container === null) {
		putenv('DB_HOST=' . (getenv('TEST_DB_HOST') ?: getenv('DB_HOST') ?: '127.0.0.1'));
		putenv('DB_DATABASE=' . (getenv('TEST_DB_DATABASE') ?: TEST_DATABASE_DEFAULT));
		putenv('DB_USER=' . (getenv('TEST_DB_USER') ?: getenv('DB_USER') ?: 'user'));
		putenv('DB_PASSWORD=' . (getenv('TEST_DB_PASSWORD') ?: getenv('DB_PASSWORD') ?: 'secret'));
		putenv('API_KEY=' . (getenv('TEST_API_KEY') ?: 'test-secret'));

		$container = (new Bootstrap())->bootWebApplication();
	}

	return $container;
}

function assertTestDatabase(): void
{
	$dbName = getenv('DB_DATABASE') ?: '';
	if ($dbName !== TEST_DATABASE_DEFAULT && !str_ends_with($dbName, '_test')) {
		throw new RuntimeException(
			"Tests refuse to wipe database \"{$dbName}\". Use " . TEST_DATABASE_DEFAULT
			. ' or set TEST_DB_DATABASE ending with _test.',
		);
	}
}

function resetDatabase(Container $container): void
{
	assertTestDatabase();

	$connection = $container->getByType(Connection::class);

	$connection->query('SET FOREIGN_KEY_CHECKS = 0');
	$connection->query('TRUNCATE TABLE owners');
	$connection->query('TRUNCATE TABLE house');
	$connection->query('SET FOREIGN_KEY_CHECKS = 1');

	// After TRUNCATE clear identity map, otherwise ORM thinks entities still exist in DB
	$container->getByType(Orm::class)->clear();
}
