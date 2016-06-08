<?php

namespace Collector;

use ArrayIterator;
use Generator;
use InvalidArgumentException;
use Iterator;
use IteratorAggregate;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Collector implements IteratorAggregate
{
    /**
     * @var Iterator
     */
    private $iterator;

    /**
     * @param Iterator $iterator
     */
    private function __construct(Iterator $iterator)
    {
        $this->iterator = $iterator;
    }

    /**
     * @param static|callable|Iterator|array $items
     * @return Collector
     */
    public static function collect($items)
    {
        //self
        if ($items instanceof self) {
            return static::fromIterator($items->getIterator());
        }

        //generators
        if (is_callable($items)) {
            return static::fromCallable($items);
        }

        //arrays
        if (is_array($items)) {
            return static::fromArray($items);
        }

        //iterators & generators
        if ($items instanceof Iterator) {
            return static::fromIterator($items);
        }

        throw new InvalidArgumentException(sprintf('$items is invalid, should be array, iterator, generator'));
    }

    /**
     * @param Iterator $iterator
     * @return static
     */
    public static function fromIterator(Iterator $iterator)
    {
        return new static($iterator);
    }

    /**
     * @param array $items
     * @return static
     */
    public static function fromArray(array $items)
    {
        return static::fromIterator(new ArrayIterator($items));
    }

    /**
     * @param callable $callable
     * @return static
     */
    public static function fromCallable(callable $callable)
    {
        $generator = $callable();

        if (!$generator instanceof Generator) {
            throw new InvalidArgumentException(
                sprintf(
                    'callable must return an instance of Generator, got "%s"',
                    is_object($generator) ? get_class($generator) : gettype($generator)
                )
            );
        }

        return static::fromIterator($generator);
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function map(callable $callback)
    {
        return static::fromCallable(function () use ($callback) {
            foreach ($this->iterator as $key => $item) {
                yield $key => $callback($item, $key);
            }
        });
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function flatMap(callable $callback)
    {
        return static::fromCallable(function () use ($callback) {
            foreach ($this->iterator as $key => $item) {
                $items = $callback($item);
                if (!is_array($items)) {
                    continue;
                }

                foreach ($items as $innerItem) {
                    yield $innerItem;
                }
            }
        });
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function filter(callable $callback = null)
    {
        if (null === $callback) {
            $callback = function ($item) {
                return $item == true;
            };
        }

        return static::fromCallable(function () use ($callback) {
            foreach ($this->iterator as $key => $item) {
                if ($callback($item, $key)) {
                    yield $key => $item;
                }
            }
        });
    }

    /**
     * @param callable $callback
     */
    public function each(callable $callback)
    {
        foreach ($this->iterator as $key => $item) {
            $callback($item, $key);
        }
    }

    /**
     * @return static
     */
    public function collapse()
    {
        return static::fromCallable(function () {
            foreach ($this->iterator as $key => $item) {
                if (!is_array($item)) {
                    continue;
                }

                foreach ($item as $innerItem) {
                    yield $innerItem;
                }
            }
        });
    }

    /**
     * @return static
     */
    public function flip()
    {
        return static::fromCallable(function () {
            foreach ($this->iterator as $key => $item) {
                yield $item => $key;
            }
        });
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function keyBy(callable $callback)
    {
        return static::fromCallable(function () use ($callback) {
             foreach ($this->iterator as $key => $item) {
                 yield $callback($item, $key) => $item;
             }
        });
    }

    /**
     * @return static
     */
    public function values()
    {
        return static::fromCallable(function () {
            foreach ($this->iterator as $item) {
                yield $item;
            }
        });
    }

    /**
     * @return static
     */
    public function keys()
    {
        return static::fromCallable(function () {
            foreach ($this->iterator as $key => $item) {
                yield $key;
            }
        });
    }

    /**
     * @param static[] ...$zips
     * @return static
     */
    public function zip(self ...$zips)
    {
        return static::fromCallable(function () use ($zips) {
            foreach ($this->iterator as $key => $value) {

                $generator = function () use ($value, $zips) {
                    yield $value;
                    foreach ($zips as $zip) {
                        yield $zip->getIterator()->current();
                        $zip->getIterator()->next();
                    }
                };

                yield static::fromCallable($generator);
            }
        });
    }

    /**
     * @return array
     */
    public function asArray()
    {
        return iterator_to_array($this->iterator);
    }

    /**
     * @return Iterator
     */
    public function getIterator()
    {
        return $this->iterator;
    }
}
