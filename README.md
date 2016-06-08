# Collector

A collection library, inspired by Laravel, [Refactoring to Collections](http://adamwathan.me/refactoring-to-collections/),
this [tweet](https://twitter.com/fschmengler/status/738254612076101633) and my own need/desire for it.
Built using generators to preserve memory when dealing with large data sets.

This was put together in about 2 hours - unit tests ripped straight from Laravel - I'd like to implement as much of the
Laravel Collection class as possible in due time.

## Create a collection

Use the static helper method `collect` to create the collection, valid arguments are any objects implementing
`\Iterator` including generators, any callable which can resolve to a generator or an array.

```php

//iterator
$collection = Collector::collect(new ArrayIterator([1, 2, 3]);

//callable generator
$collection = Collector::collect(function () {
    yield 1;
    yield 2;
    yield 3;
});

//array
$collection = Collector::collect([1, 2, 3]);
```

You can also use the more explicit static constructors if you wish:

```php

//iterator
$collection = Collector::fromIterator(new ArrayIterator([1, 2, 3]);

//callable generator
$collection = Collector::fromCallable(function () {
    yield 1;
    yield 2;
    yield 3;
});

//array
$collection = Collector::fromArray([1, 2, 3]);
```

# Operations

## Map

```php

use Collector\Collector;

$source = function () {
    yield 1;
    yield 2;
    yield 3;
};

$collection = Collector::fromCallable($source)
    ->map(function ($item, $key) {
        return $item * 2;
    });

var_dump($collection->asArray());

array(3) {
  [0] =>
  int(2)
  [1] =>
  int(4)
  [2] =>
  int(6)
}
```

## Filter

```php

use Collector\Collector;

$source = function () {
    yield 1;
    yield 2;
    yield 3;
};

$collection = Collector::fromCallable($source)
    ->filter(function ($item, $key) {
        return $item >=2;
    });

var_dump($collection->asArray());

array(2) {
  [1] =>
  int(2)
  [2] =>
  int(3)
}
```

## Flat Map

```php

use Collector\Collector;

$source = function () {
    yield ['name' => 'Aloo Gobi', 'ingredients' => ['Cauliflower', 'Potato']];
    yield ['name' => 'Jelfrezi', 'ingredients' => ['Chicken', 'Tomatoes', 'Chili']];
};

$collection = Collector::fromCallable($source)
    ->flatMap(function ($item, $key) {
        return $item['ingredients'];
    });

var_dump($collection->asArray());

array(5) {
  [0] =>
  string(11) "Cauliflower"
  [1] =>
  string(6) "Potato"
  [2] =>
  string(7) "Chicken"
  [3] =>
  string(8) "Tomatoes"
  [4] =>
  string(5) "Chili"
}
```

## Each

```php

use Collector\Collector;

$source = function () {
    yield ['name' => 'Aloo Gobi', 'ingredients' => ['Cauliflower', 'Potato']];
    yield ['name' => 'Jelfrezi', 'ingredients' => ['Chicken', 'Tomatoes', 'Chili']];
};

$collection = Collector::fromCallable($source)
    ->each(function ($item, $key) {
        echo $item['name'] . "\n";
    });

//Outputs:
Aloo Gobi
Jelfrezi
```

## Collapse

```php

use Collector\Collector;

$source = function () {
    yield ['garlic', 'chili', 'ginger', 'coriander'];
    yield ['onions', 'tomatoes'];
};

$collection = Collector::fromCallable($source)
    ->collapse();

var_dump($collection->asArray());

array(6) {
  [0] =>
  string(6) "garlic"
  [1] =>
  string(5) "chili"
  [2] =>
  string(6) "ginger"
  [3] =>
  string(9) "coriander"
  [4] =>
  string(6) "onions"
  [5] =>
  string(8) "tomatoes"
}
```

## Flip

```php

use Collector\Collector;

$source = function () {
    yield 'Aydin';
    yield 'Caroline';
};

$collection = Collector::fromCallable($source)
    ->flip();

var_dump($collection->asArray());

array(2) {
  'Aydin' =>
  int(0)
  'Caroline' =>
  int(1)
}
```

## Key By

```php

use Collector\Collector;

$source = function () {
    yield ['name' => 'Vienna'];
    yield ['name' => 'Lower Austria'];
    yield ['name' => 'Styria'];
};

$collection = Collector::fromCallable($source)
    ->keyBy(function ($item, $key) {
        return $item['name'];
    });

var_dump($collection->asArray());

array(3) {
  'Vienna' =>
  array(1) {
    'name' =>
    string(6) "Vienna"
  }
  'Lower Austria' =>
  array(1) {
    'name' =>
    string(13) "Lower Austria"
  }
  'Styria' =>
  array(1) {
    'name' =>
    string(6) "Styria"
  }
}
```

## Values

```php

use Collector\Collector;

$source = function () {
    yield 5 => 'Vienna';
    yield 1 => 'Lower Austria';
    yield 9 => 'Styria';
};

$collection = Collector::fromCallable($source)
    ->values();

var_dump($collection->asArray());

array(3) {
  [0] =>
  string(6) "Vienna"
  [1] =>
  string(13) "Lower Austria"
  [2] =>
  string(6) "Styria"
}
```

## Keys

```php

use Collector\Collector;

$source = function () {
    yield 5 => 'Vienna';
    yield 1 => 'Lower Austria';
    yield 9 => 'Styria';
};

$collection = Collector::fromCallable($source)
    ->keys();

var_dump($collection->asArray());

array(3) {
  [0] => int(5)
  [1] => int(1)
  [2] => int(9)
}
```

## Zip

```php

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

array(2) {
  [0] =>
  string(7) "Austria"
  [1] =>
  string(6) "Europe"
}

array(2) {
  [0] =>
  string(10) "Kyrgyzstan"
  [1] =>
  string(4) "Asia"
}

array(2) {
  [0] =>
  string(12) "Burkina Faso"
  [1] =>
  string(6) "Africa"
}
```
