<?php

declare(strict_types=1);

namespace App\Model\House;

use Nextras\Orm\Repository\Repository;

/**
 * @extends Repository<House>
 */
final class HousesRepository extends Repository
{
	public static function getEntityClassNames(): array
	{
		return [House::class];
	}

	public function getByAddress(string $place, string $housenumber): ?House
	{
		return $this->getBy([
			'place' => $place,
			'housenumber' => $housenumber,
		]);
	}
}
