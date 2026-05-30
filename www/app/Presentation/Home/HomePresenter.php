<?php

declare(strict_types=1);

namespace App\Presentation\Home;

use App\Model\Orm;
use Nette;

final class HomePresenter extends Nette\Application\UI\Presenter
{
	public function __construct(
		private Orm $orm,
	) {
		parent::__construct();
	}

	public function renderDefault(): void
	{
		$mapData = [];
		foreach ($this->orm->houses->findAll() as $house) {
			$ownersData = [];
			foreach ($house->owners as $owner) {
				$ownersData[] = [
					'name' => $owner->name,
					'share' => $owner->share,
				];
			}

			$mapData[] = [
				'id' => $house->id,
				'lat' => $house->lat,
				'lon' => $house->lon,
				'sjtsk_x' => $house->sjtskX,
				'sjtsk_y' => $house->sjtskY,
				'place' => $house->place,
				'housenumber' => $house->housenumber,
				'postcode' => $house->postcode,
				'country' => $house->country,
				'conscriptionnumber' => $house->conscriptionnumber,
				'ruian_ref' => $house->ruianRef,
				'ownersData' => $ownersData,
			];
		}

		$this->template->mapData = $mapData;
	}
}
