<?php

declare(strict_types=1);

namespace App\Presentation\Owners;

use App\Security\ApiKeyAuthenticator;
use App\Service\Owners\HouseNotFoundException;
use App\Service\Owners\InvalidOwnersPayloadException;
use App\Service\Owners\OwnersImportService;
use Nette;
use Nette\Http\IResponse;

final class OwnersPresenter extends Nette\Application\UI\Presenter
{
	public function __construct(
		private OwnersImportService $ownersImport,
		private ApiKeyAuthenticator $apiKeyAuth,
	) {
		parent::__construct();
	}

	protected function startup(): void
	{
		parent::startup();

		if (!$this->apiKeyAuth->authenticate($this->getHttpRequest())) {
			$this->sendJsonError('Unauthorized', IResponse::S401_Unauthorized);
		}
	}

	public function actionDefault(): void
	{
		try {
			$result = $this->ownersImport->importFromPost($this->getRequest()->getPost());
		} catch (InvalidOwnersPayloadException $e) {
			$this->sendJsonError($e->getMessage(), IResponse::S400_BadRequest);
		} catch (HouseNotFoundException $e) {
			$this->sendJsonError($e->getMessage(), IResponse::S404_NotFound);
		}

		$this->getHttpResponse()->setCode(IResponse::S200_OK);
		$this->sendJson([
			'status' => 'ok',
			...$result,
		]);
	}


	private function sendJsonError(string $message, int $code): never
	{
		$this->getHttpResponse()->setCode($code);
		$this->sendJson([
			'status' => 'error',
			'message' => $message,
		]);
	}
}
