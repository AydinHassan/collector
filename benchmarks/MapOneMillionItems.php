<?php

use Collector\Collector;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class MapOneMillionItems
{

    const ONE_MILLION = 1000000;

    /**
     * @Revs(10)
     */
    public function benchCollectionGenerator()
    {
        $collection = Collector::collect(function () {
            for ($i = 0; $i <= static::ONE_MILLION; $i++) {
                yield $i;
            }
        });

        $result = $collection->map(function ($i) {
            return $i * 2;
        });
    }

    /**
     * @Revs(10)
     */
    public function benchCollectionArray()
    {
        $collection = Collector::collect(range(0, static::ONE_MILLION));

        $result = $collection->map(function ($i) {
            return $i * 2;
        });
    }

    /**
     * @Revs(10)
     */
    public function benchNativeArrayMap()
    {
        $result = array_map(function ($i) {
            return $i * 2;
        }, range(0, static::ONE_MILLION));
    }

    /**
     * @Revs(10)
     */
    public function benchForEachGenerator()
    {
        $generator = function () {
            for ($i = 0; $i <= static::ONE_MILLION; $i++) {
                yield $i;
            }
        };

        foreach ($generator as $i => $item) {
            $items[$i] = $item * 2;
        }
    }

    /**
     * @Revs(10)
     */
    public function benchForEachArray()
    {
        $items = range(0, static::ONE_MILLION);

        foreach ($items as $i => $item) {
            $items[$i] = $item * 2;
        }
    }
}
