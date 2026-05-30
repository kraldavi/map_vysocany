<?php

declare(strict_types=1);

use App\Model\Orm;
use Nette\Application\IPresenterFactory;
use Nette\Application\Request;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use Nette\Http\IResponse;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../DatabaseTestCase.php';

$container = testContainer();

/**
 * @return array{code: int, payload: array<string, mixed>}
 */
function postOwners(Container $container, array $post, ?string $apiKey = 'test-secret'): array
{
	withHttpRequest(
		$container,
		'POST',
		'http://localhost/owners',
		$post,
		$apiKey !== null ? ['Authorization' => 'Bearer ' . $apiKey] : [],
	);

	$presenter = $container->getByType(IPresenterFactory::class)->createPresenter('Owners');
	Assert::type(Presenter::class, $presenter);

	$response = $presenter->run(new Request('Owners', 'POST', post: $post));
	Assert::type(JsonResponse::class, $response);

	return [
		'code' => $container->getByType(IResponse::class)->getCode(),
		'payload' => $response->getPayload(),
	];
}

$result = postOwners($container, [], apiKey: null);
Assert::same(IResponse::S401_Unauthorized, $result['code']);
Assert::same('error', $result['payload']['status']);

$result = postOwners($container, [], apiKey: 'wrong-key');
Assert::same(IResponse::S401_Unauthorized, $result['code']);

$result = postOwners($container, []);
Assert::same(IResponse::S400_BadRequest, $result['code']);
Assert::same('error', $result['payload']['status']);

$result = postOwners($container, [
	'place' => 'Molenburk',
	'housenumber' => '97',
	'owners' => json_encode([['name' => 'John Doe']], JSON_THROW_ON_ERROR),
]);
Assert::same(IResponse::S400_BadRequest, $result['code']);

resetDatabase($container);
$result = postOwners($container, [
	'place' => 'Nonexistent',
	'housenumber' => '1',
	'owners' => json_encode([['name' => 'John Doe', 'share' => '1']], JSON_THROW_ON_ERROR),
]);
Assert::same(IResponse::S404_NotFound, $result['code']);

$orm = freshOrm($container);
seedHouse($orm);

$result = postOwners($container, [
	'place' => 'Molenburk',
	'housenumber' => '97',
	'owners' => json_encode([
		['name' => 'John Doe', 'share' => '1/2'],
		['name' => 'Jane Doe', 'share' => '1/2'],
	], JSON_THROW_ON_ERROR),
]);
Assert::same(IResponse::S200_OK, $result['code']);
Assert::same('ok', $result['payload']['status']);
Assert::same(2, $result['payload']['saved']);
Assert::same(2, $container->getByType(Orm::class)->owners->findAll()->countStored());
