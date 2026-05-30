<?php

declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../DatabaseTestCase.php';

$orm = freshOrm(testContainer());
$house = seedHouse($orm);

$orm->owners->replaceForHouse($house, [
	['name' => 'John Doe', 'share' => '1/2'],
	['name' => 'Jane Doe', 'share' => '1/2'],
]);

Assert::same(2, $orm->owners->findBy(['house->id' => $house->id])->countStored());

$orm->owners->replaceForHouse($house, [
	['name' => 'Peter Smith', 'share' => '1'],
]);

$owners = $orm->owners->findBy(['house->id' => $house->id])->fetchAll();
Assert::same(1, count($owners));
Assert::same('Peter Smith', $owners[0]->name);
Assert::same('1', $owners[0]->share);

$orm->owners->replaceForHouse($house, []);

Assert::same(0, $orm->owners->findBy(['house->id' => $house->id])->countStored());
