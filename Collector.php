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
     * @param Iterator $iterator
     * @return self
     */
    public static function fromIterator(Iterator $iterator) : self
    {
        return new static($iterator);
    }

    /**
     * @param array $items
     * @return self
     */
    public function fromArray(array $items)
    {
        return static::fromIterator(new ArrayIterator($items));
    }

    /**
     * @param Generator $generator
     * @return self
     */
    public static function fromGenerator(Generator $generator) : self
    {
        return static::fromIterator($generator);
    }

    /**
     * @param callable $callable
     * @return self
     */
    public static function fromCallable(callable $callable) : self
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

        return static::fromGenerator($generator);
    }

    /**
     * @param callable $callback
     * @return self
     */
    public function map(callable $callback) : self
    {
        return static::fromCallable(function () use ($callback) : Generator {
            foreach ($this->iterator as $key => $item) {
                yield $key => $callback($item, $key);
            }
        });
    }

    /**
     * @param callable $callback
     * @return self
     */
    public function flatMap(callable $callback) : self
    {
        return static::fromCallable(function () use ($callback) : Generator {
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
     * @return self
     */
    public function filter(callable $callback = null) : self
    {
        if (null === $callback) {
            $callback = function ($item) {
                return $item == true;
            };
        }

        return static::fromCallable(function () use ($callback) : Generator {
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
     * @return self
     */
    public function collapse() : self
    {
        return static::fromCallable(function () : Generator {
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
     * @return self
     */
    public function flip() : self
    {
        return static::fromCallable(function () : Generator {
            foreach ($this->iterator as $key => $item) {
                yield $item => $key;
            }
        });
    }

    /**
     * @param callable $callback
     * @return self
     */
    public function keyBy(callable $callback) : self
    {
        return static::fromCallable(function () use ($callback) : Generator {
             foreach ($this->iterator as $key => $item) {
                 yield $callback($item, $key) => $item;
             }
        });
    }

    /**
     * @return self
     */
    public function values() : self
    {
        return static::fromCallable(function () {
            foreach ($this->iterator as $item) {
                yield $item;
            }
        });
    }

    /**
     * @return self
     */
    public function keys() : self
    {
        return static::fromCallable(function () {
            foreach ($this->iterator as $key => $item) {
                yield $key;
            }
        });
    }

    /**
     * @param self[] ...$zips
     * @return self
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
    public function getIterator() : Iterator
    {
        return $this->iterator;
    }
}
