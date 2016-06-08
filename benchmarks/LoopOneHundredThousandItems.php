<?php

use Collector\Collector;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class LoopOneHundredThousandItems
{

    /**
     * @Revs(10)
     */
    public function benchCollectionGenerator()
    {
        $collection = Collector::collect(function () {
            for ($i = 0; $i <= 100000; $i++) {
                yield $i;
            }
        });

        $collection->each(function ($i) {

        });
    }

    /**
     * @Revs(10)
     */
    public function benchCollectionArray()
    {
        $collection = Collector::collect(range(0, 100000));

        $collection->each(function ($i) {

        });
    }

    public function benchForeachArray()
    {
        $array = range(0, 100000);
        foreach ($array as $i) {

        }
    }
}
