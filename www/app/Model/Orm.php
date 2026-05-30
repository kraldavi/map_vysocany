<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\House\HousesRepository;
use App\Model\Owner\OwnersRepository;
use Nextras\Orm\Model\Model;

/**
 * @property-read HousesRepository $houses
 * @property-read OwnersRepository $owners
 */
final class Orm extends Model
{
}
