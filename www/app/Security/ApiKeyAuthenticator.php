<?php

declare(strict_types=1);

namespace App\Security;

use Nette\Http\IRequest;

final class ApiKeyAuthenticator
{
	public function __construct(
		private readonly string $apiKey,
	) {
	}

	public function isRequired(): bool
	{
		return $this->apiKey !== '';
	}

	public function authenticate(IRequest $request): bool
	{
		if (!$this->isRequired()) {
			return true;
		}

		$provided = $this->extractKey($request);

		return $provided !== null && hash_equals($this->apiKey, $provided);
	}

	private function extractKey(IRequest $request): ?string
	{
		$authorization = $request->getHeader('Authorization');
		if ($authorization !== null && str_starts_with($authorization, 'Bearer ')) {
			$key = trim(substr($authorization, 7));

			return $key !== '' ? $key : null;
		}

		$apiKey = $request->getHeader('X-Api-Key');

		return $apiKey !== null && $apiKey !== '' ? $apiKey : null;
	}
}
