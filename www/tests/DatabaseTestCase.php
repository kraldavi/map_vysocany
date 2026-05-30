<?php

declare(strict_types=1);

use App\Model\House\House;
use App\Model\Orm;
use Nette\DI\Container;
use Nette\Http\Request;
use Nette\Http\UrlScript;

function seedHouse(Orm $orm, int $id = 12_345, string $place = 'Molenburk', string $housenumber = '97'): House
{
	$house = new House();
	$house->id = $id;
	$house->lat = 49.43;
	$house->lon = 16.81;
	$house->sjtskX = -603_000;
	$house->sjtskY = -1_135_000;
	$house->place = $place;
	$house->housenumber = $housenumber;

	$orm->persist($house);
	$orm->flush();

	return $house;
}

function freshOrm(Container $container): Orm
{
	resetDatabase($container);

	return $container->getByType(Orm::class);
}

function withHttpRequest(Container $container, string $method, string $url, array $post = [], array $headers = []): void
{
	$container->removeService('http.request');
	$container->addService('http.request', new Request(
		new UrlScript($url),
		$post,
		headers: $headers,
		method: $method,
	));
}
