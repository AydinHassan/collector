<?php

ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

use Collector\Collector;

$countries = function () {
    yield 'Austria';
    yield 'Kyrgyzstan';
    yield 'Burkina Faso';
};

$continents = function () {
    yield 'Europe';
    yield 'Asia';
    yield 'Africa';
};

$zipped = Collector::fromCallable($countries)
    ->zip(Collector::fromCallable($continents));

foreach ($zipped as $zipPart) {
    var_dump($zipPart->asArray());
}




