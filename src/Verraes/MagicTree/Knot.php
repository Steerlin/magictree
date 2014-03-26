<?php

namespace Verraes\MagicTree;

use ArrayAccess;
use Iterator;
use JsonSerializable;

final class Knot implements ArrayAccess, Iterator, JsonSerializable, Node
{
    protected $_children = [];

    public function remove($key)
    {
        unset($this->_children[$key]);
    }

    public function offsetGet($index)
    {
        if (!isset($this->_children[$index])) {
            $this->_children[$index] = new Knot();
        }
        return $this->_children[$index];
    }


    public function __get($name)
    {
        if (!isset($this->_children[$name])) {
            $this->_children[$name] = new Knot();
        }

        return $this->_children[$name];
    }

    public function offsetExists($offset)
    {
        return true;
    }

    public function __set($name, $value)
    {
        if (is_scalar($value)) {
            $this->_children[$name] = new Leaf($value);

        } else {
            $this->_children[$name] = new HashKnot($value);
        }
    }

    public function __call($name, $arguments)
    {
        $this->_children[$name] = $arguments[0];
        return $this;
    }


    public function offsetSet($offset, $value)
    {
        $this->_children[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        throw new \Exception("@todo  Implement offsetUnset() method.");
    }

    public function toArray()
    {
        $result = [];
        foreach ($this->_children as $key => $child) {
            $result[$key] = $child instanceof Node ? $child->toArray() : $child;
        }
        return $result;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return current($this->_children);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        next($this->_children);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return key($this->_children);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return false !== current($this->_children);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        reset($this->_children);
    }


    public function toAscii($indent = 0)
    {

        $output = '';

        foreach ($this->_children as $key => $child) {

            if($child instanceof Node) {
                $value = "\n" . $child->toAscii($indent + 1);
            } elseif(is_bool($child)) {
                $value = ': ' . ($child ? 'true':'false') . PHP_EOL;
            } else {
                $value = ': "' . $child . '"' . PHP_EOL;
            }

            $output .= str_repeat('  |', $indent) . '- ' . $key . $value;
        }

        return $output;
    }

    public function jsonSerialize()
    {
        return (object)array_combine(
            array_keys($this->_children),
            array_map(
                function ($child) {
                    return $child instanceof Node ? $child->jsonSerialize() : $child;
                },
                $this->_children
            )
        );
    }

    public function sort(callable $comparator)
    {
        uksort($this->_children, $comparator);
    }
}
