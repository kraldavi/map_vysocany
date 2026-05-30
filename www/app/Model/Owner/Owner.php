<?php

declare(strict_types=1);

namespace App\Model\Owner;

use App\Model\House\House;
use Nextras\Orm\Entity\Entity;

/**
 * @property int $id {primary}
 * @property House $house {m:1 House::$owners}
 * @property string $name
 * @property string $share {default '1'}
 */
class Owner extends Entity
{
}
