<?php

declare(strict_types=1);

namespace App\Service\Owners;

use App\Model\Orm;

final class OwnersImportService
{
	public function __construct(
		private Orm $orm,
	) {
	}

	/**
	 * @param array<string, mixed> $post
	 *
	 * @return array{housenumber: string, place: string, saved: int}
	 */
	public function importFromPost(array $post): array
	{
		$houseNumber = $post['housenumber'] ?? null;
		$place = $post['place'] ?? null;
		$ownersJson = $post['owners'] ?? '[]';

		$owners = json_decode(is_string($ownersJson) ? $ownersJson : '', true);

		if (!is_string($houseNumber) || !is_string($place) || !is_array($owners)) {
			throw new InvalidOwnersPayloadException('Missing housenumber, place, or owners in JSON');
		}

		foreach ($owners as $row) {
			if (!is_array($row) || !isset($row['name'], $row['share'])) {
				throw new InvalidOwnersPayloadException('Each item must contain name and share');
			}
		}

		/** @var list<array{name: string, share: string}> $owners */
		$house = $this->orm->houses->getByAddress($place, $houseNumber);
		if ($house === null) {
			throw new HouseNotFoundException('House not found');
		}

		$this->orm->owners->replaceForHouse($house, $owners);

		return [
			'housenumber' => $houseNumber,
			'place' => $place,
			'saved' => count($owners),
		];
	}
}
