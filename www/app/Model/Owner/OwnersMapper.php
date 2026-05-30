<?php

declare(strict_types=1);

namespace App\Model\Owner;

use Nextras\Dbal\Platforms\Data\Fqn;
use Nextras\Orm\Mapper\Dbal\DbalMapper;

/**
 * @extends DbalMapper<Owner>
 */
final class OwnersMapper extends DbalMapper
{
	protected string|Fqn|null $tableName = 'owners';
}
