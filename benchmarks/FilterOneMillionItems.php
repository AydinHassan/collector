<?php

use Collector\Collector;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class FilterOneMillionItems
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

        $result = $collection->filter(function ($i) {
            return $i % 2 === 0;
        });
    }

    /**
     * @Revs(10)
     */
    public function benchCollectionArray()
    {
        $collection = Collector::collect(range(0, static::ONE_MILLION));

        $result = $collection->filter(function ($i) {
            return $i % 2 === 0;
        });
    }

    /**
     * @Revs(10)
     */
    public function benchNativeArrayFilter()
    {
        $result = array_filter(range(0, static::ONE_MILLION), function ($i) {
            return $i % 2 === 0;
        });
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

        $filtered = [];
        foreach ($generator as $i => $item) {
            if ($i % 2 === 0) {
                $filtered[$i] = $item;
            }
        }
    }

    /**
     * @Revs(10)
     */
    public function benchForEachArray()
    {
        $items = range(0, static::ONE_MILLION);

        $filtered = [];
        foreach ($items as $i => $item) {
            if ($i % 2 === 0) {
                $filtered[$i] = $item;
            }
        }
    }
}
