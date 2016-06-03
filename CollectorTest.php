<?php

namespace Collector\Collector;

use phpunit\framework\TestCase;


/**
 * Class CollectorTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CollectorTest extends TestCase
{
    public function testCollectionCanBeConstructed()
    {
        $generator = function () {
            yield 1;
        };

        $collection = Collector::fromCallable($generator);
        $this->assertInstanceOf(Collector::class, $collection);
    }

    public function testMap()
    {
        $generator = function () {
            yield 'first' => 'taylor';
            yield 'last' => 'otwell';
        };

        $c = Collector::fromCallable($generator);
        $data = $c->map(function ($item, $key) {
            return $key.'-'.strrev($item);
        });
        $this->assertEquals(['first' => 'first-rolyat', 'last' => 'last-llewto'], $data->asArray());
    }

    public function testFlatMap()
    {
        $generator = function () {
            yield ['name' => 'taylor', 'hobbies' => ['programming', 'basketball']];
            yield ['name' => 'adam', 'hobbies' => ['music', 'powerlifting']];
        };

        $c = Collector::fromCallable($generator);
        $data = $c->flatMap(function ($person) {
            return $person['hobbies'];
        });

        $this->assertEquals(['programming', 'basketball', 'music', 'powerlifting'], $data->asArray());
    }

    public function testFilter()
    {
        $generator = function () {
            yield ['id' => 1, 'name' => 'Hello'];
            yield ['id' => 2, 'name' => 'World'];
        };

        $c = Collector::fromCallable($generator);
        $this->assertEquals([1 => ['id' => 2, 'name' => 'World']], $c->filter(function ($item) {
            return $item['id'] == 2;
        })->asArray());

        $generator = function () {
            yield '';
            yield 'Hello';
            yield '';
            yield 'World';
        };

        $c = Collector::fromCallable($generator);
        $this->assertEquals([1 => 'Hello', 3 => 'World'], $c->filter()->asArray());

        $generator = function () {
            yield 'id' => 1;
            yield 'first' => 'Hello';
            yield 'second' => 'World';
        };

        $c = Collector::fromCallable($generator);
        $this->assertEquals(['first' => 'Hello', 'second' => 'World'], $c->filter(function ($item, $key) {
            return $key != 'id';
        })->asArray());
    }

    public function testCollapse()
    {
        $object1 = new \StdClass;
        $object2 = new \StdClass;
        $generator = function () use ($object1, $object2) {
            yield [$object1];
            yield [$object2 ];
        };

        $c = Collector::fromCallable($generator);
        $this->assertEquals([$object1, $object2], $c->collapse()->asArray());
    }

    public function testFlip()
    {
        $c = $this->getCollection(['name' => 'taylor', 'framework' => 'laravel']);
        $this->assertEquals(['taylor' => 'name', 'laravel' => 'framework'], $c->flip()->asArray());
    }

    public function testKeyByClosure()
    {
        $c = $this->getCollection([
            ['firstname' => 'Taylor', 'lastname' => 'Otwell', 'locale' => 'US'],
            ['firstname' => 'Lucas', 'lastname' => 'Michot', 'locale' => 'FR'],
        ]);
        $result = $c->keyBy(function ($item, $key) {
            return strtolower($key.'-'.$item['firstname'].$item['lastname']);
        });
        $this->assertEquals([
            '0-taylorotwell' => ['firstname' => 'Taylor', 'lastname' => 'Otwell', 'locale' => 'US'],
            '1-lucasmichot'  => ['firstname' => 'Lucas', 'lastname' => 'Michot', 'locale' => 'FR'],
        ], $result->asArray());
    }

    public function testValues()
    {
        $c = $this->getCollection([['id' => 1, 'name' => 'Hello'], ['id' => 2, 'name' => 'World']]);
        $this->assertEquals([['id' => 2, 'name' => 'World']], $c->filter(function ($item) {
            return $item['id'] == 2;
        })->values()->asArray());
    }

    public function testKeys()
    {
        $c = $this->getCollection(['name' => 'taylor', 'framework' => 'laravel']);
        $this->assertEquals(['name', 'framework'], $c->keys()->asArray());
    }

    public function testZip()
    {
        $c = $this->getCollection([1, 2, 3]);
        $c = $c->zip($this->getCollection([4, 5, 6]));
        $array = $c->asArray();
        $this->assertInstanceOf(Collector::class, $c);
        $this->assertInstanceOf(Collector::class, $array[0]);
        $this->assertInstanceOf(Collector::class, $array[1]);
        $this->assertInstanceOf(Collector::class, $array[2]);
        $this->assertCount(3, $array);
        $this->assertEquals([1, 4], $array[0]->asArray());
        $this->assertEquals([2, 5], $array[1]->asArray());
        $this->assertEquals([3, 6], $array[2]->asArray());
        $c = $this->getCollection([1, 2, 3]);
        $c = $c->zip($this->getCollection([4, 5, 6]), $this->getCollection([7, 8, 9]));
        $array = $c->asArray();
        $this->assertCount(3, $array);
        $this->assertEquals([1, 4, 7], $array[0]->asArray());
        $this->assertEquals([2, 5, 8], $array[1]->asArray());
        $this->assertEquals([3, 6, 9], $array[2]->asArray());
        $c = $this->getCollection([1, 2, 3]);
        $c = $c->zip($this->getCollection([4, 5, 6]), $this->getCollection([7]));
        $array = $c->asArray();
        $this->assertCount(3, $array);
        $this->assertEquals([1, 4, 7], $array[0]->asArray());
        $this->assertEquals([2, 5, null], $array[1]->asArray());
        $this->assertEquals([3, 6, null], $array[2]->asArray());
    }

    /**
     * @param array $items
     * @return Collector
     */
    private function getCollection(array $items)
    {
        return Collector::fromCallable(function () use ($items) {
            foreach ($items as $key => $item) {
                yield $key => $item;
            }
        });
    }
}
