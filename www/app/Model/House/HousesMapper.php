<?php

declare(strict_types=1);

namespace App\Model\House;

use Nextras\Dbal\Platforms\Data\Fqn;
use Nextras\Orm\Mapper\Dbal\Conventions\IConventions;
use Nextras\Orm\Mapper\Dbal\DbalMapper;

/**
 * @extends DbalMapper<House>
 */
final class HousesMapper extends DbalMapper
{
	protected string|Fqn|null $tableName = 'house';

	protected function createConventions(): IConventions
	{
		$conventions = parent::createConventions();
		$conventions->setMapping('ruianRef', 'ruian_ref');
		$conventions->setMapping('sjtskX', 'sjtsk_x');
		$conventions->setMapping('sjtskY', 'sjtsk_y');
		return $conventions;
	}
}
