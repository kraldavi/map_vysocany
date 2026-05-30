<?php

declare(strict_types=1);

namespace App\Model\House;

use App\Model\Owner\Owner;
use Nextras\Orm\Entity\Entity;
use Nextras\Orm\Relationships\OneHasMany;

/**
 * @property int $id {primary}
 * @property float $lat
 * @property float $lon
 * @property int $sjtskX
 * @property int $sjtskY
 * @property string|null $place
 * @property string|null $housenumber
 * @property string|null $postcode
 * @property string|null $country
 * @property string|null $conscriptionnumber
 * @property string|null $ruianRef
 * @property OneHasMany<Owner> $owners {1:m Owner::$house}
 */
class House extends Entity
{
}
