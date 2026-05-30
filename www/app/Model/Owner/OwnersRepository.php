<?php

declare(strict_types=1);

namespace App\Model\Owner;

use App\Model\House\House;
use Nextras\Orm\Repository\Repository;

/**
 * @extends Repository<Owner>
 */
final class OwnersRepository extends Repository
{
	public static function getEntityClassNames(): array
	{
		return [Owner::class];
	}

	/**
	 * @param list<array{name: string, share: string}> $rows
	 */
	public function replaceForHouse(House $house, array $rows): void
	{
		$model = $this->getModel();

		foreach ($this->findBy(['house->id' => $house->id]) as $owner) {
			$model->remove($owner);
		}

		foreach ($rows as $row) {
			$owner = new Owner();
			$owner->house = $house;
			$owner->name = $row['name'];
			$owner->share = $row['share'];
			$model->persist($owner);
		}

		$model->flush();
	}
}
