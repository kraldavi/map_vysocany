<?php

declare(strict_types=1);

use App\Security\ApiKeyAuthenticator;
use Nette\Http\Request;
use Nette\Http\UrlScript;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

function httpRequestWithHeaders(array $headers): Request
{
	return new Request(
		new UrlScript('http://localhost/owners'),
		headers: $headers,
		method: 'POST',
	);
}

$auth = new ApiKeyAuthenticator('');

Assert::true($auth->authenticate(httpRequestWithHeaders([])));
Assert::false($auth->isRequired());

$auth = new ApiKeyAuthenticator('secret-key');
Assert::true($auth->isRequired());

Assert::false($auth->authenticate(httpRequestWithHeaders([])));
Assert::false($auth->authenticate(httpRequestWithHeaders(['Authorization' => 'Bearer wrong'])));
Assert::false($auth->authenticate(httpRequestWithHeaders(['X-Api-Key' => 'wrong'])));

Assert::true($auth->authenticate(httpRequestWithHeaders(['Authorization' => 'Bearer secret-key'])));
Assert::true($auth->authenticate(httpRequestWithHeaders(['X-Api-Key' => 'secret-key'])));

Assert::true($auth->authenticate(httpRequestWithHeaders(['Authorization' => 'Bearer  secret-key  '])));
