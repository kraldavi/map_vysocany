<?php

declare(strict_types=1);

namespace App\Console;

use App\Model\House\House;
use App\Model\Orm;
use proj4php\Point;
use proj4php\Proj;
use proj4php\Proj4php;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
	name: 'app:import-osm',
	description: 'Import address points from OSM JSON into the database',
)]
final class ImportOsmCommand extends Command
{
	public function __construct(
		private Orm $orm,
	) {
		parent::__construct();
	}

	protected function configure(): void
	{
		$this
			->addOption(
				'file',
				'f',
				InputOption::VALUE_REQUIRED,
				'Path to JSON file',
				dirname(__DIR__, 2) . '/www/data.json',
			)
			->addOption(
				'fresh',
				null,
				InputOption::VALUE_NONE,
				'Delete existing rows in the house table before import',
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$file = (string) $input->getOption('file');

		if (!is_file($file)) {
			$output->writeln("<error>File not found: $file</error>");
			return Command::FAILURE;
		}

		$json = json_decode((string) file_get_contents($file), true);
		if (!is_array($json)) {
			$output->writeln('<error>Invalid JSON</error>');
			return Command::FAILURE;
		}

		$elements = $json['elements'] ?? [];

		if ($input->getOption('fresh')) {
			foreach ($this->orm->houses->findAll() as $house) {
				$this->orm->remove($house);
			}
			$this->orm->flush();
			$output->writeln('<comment>house table cleared.</comment>');
		}

		$proj4 = new Proj4php();
		$wgs84 = new Proj('EPSG:4326', $proj4);
		$sjtsk = new Proj('EPSG:5514', $proj4);

		$count = 0;

		foreach ($elements as $el) {
			if (($el['type'] ?? null) !== 'node') {
				continue;
			}
			if (!isset($el['lat'], $el['lon'])) {
				continue;
			}

			$tags = $el['tags'] ?? [];
			$osmId = (int) $el['id'];

			$point = new Point($el['lon'], $el['lat'], $wgs84);
			$sjtskPoint = $proj4->transform($sjtsk, $point);

			$house = $this->orm->houses->getById($osmId) ?? new House();
			$house->id = $osmId;
			$house->lat = (float) $el['lat'];
			$house->lon = (float) $el['lon'];
			$house->sjtskX = (int) round($sjtskPoint->x);
			$house->sjtskY = (int) round($sjtskPoint->y);
			$house->place = $tags['addr:place'] ?? null;
			$house->housenumber = $tags['addr:housenumber'] ?? null;
			$house->postcode = $tags['addr:postcode'] ?? null;
			$house->country = $tags['addr:country'] ?? null;
			$house->conscriptionnumber = $tags['addr:conscriptionnumber'] ?? null;
			$house->ruianRef = $tags['ref:ruian:addr'] ?? null;

			$this->orm->persist($house);
			$count++;
		}

		$this->orm->flush();

		$output->writeln("<info>Imported/updated $count points.</info>");
		return Command::SUCCESS;
	}
}
