<?php

/*
 * This file is part of the Сáша framework.
 *
 * (c) tchiotludo <http://github.com/tchiotludo>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Cawa\Orm;

class Collection implements \Countable, \IteratorAggregate, \ArrayAccess
{
    /**
     * An array containing the entries of this collection.
     *
     * @var array
     */
    protected $elements = [];

    /**
     * Initializes a new ArrayCollection.
     *
     * @param array $elements
     */
    public function __construct(array $elements = [])
    {
        $this->elements = $elements;
    }

    /**
     * Reset ArrayCollection with this array.
     *
     * @param array $elements
     *
     * @return $this|self
     */
    public function fromArray(array $elements = []) : self
    {
        $this->elements = $elements;

        return $this;
    }

    /**
     * Gets a native PHP array representation of the collection.
     *
     * @return array
     */
    public function toArray() : array
    {
        return $this->elements;
    }

    /**
     * Sets the internal iterator to the first element in the collection and returns this element.
     *
     * @return mixed
     */
    public function first()
    {
        return reset($this->elements);
    }

    /**
     * Sets the internal iterator to the last element in the collection and returns this element.
     *
     * @return mixed
     */
    public function last()
    {
        return end($this->elements);
    }

    /**
     * Sets the internal iterator to the last element in the collection and returns this element.
     *
     * @return mixed
     */
    public function random()
    {
        $keys = array_keys($this->elements);

        return $this->elements[$keys[rand(0, sizeof($keys) - 1)]];
    }

    /**
     * Gets the key/index of the element at the current iterator position.
     *
     * @return int|string
     */
    public function key()
    {
        return key($this->elements);
    }

    /**
     * Moves the internal iterator position to the next element and returns this element.
     *
     * @return mixed
     */
    public function next()
    {
        return next($this->elements);
    }

    /**
     * Gets the element of the collection at the current iterator position.
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->elements);
    }

    /**
     * Removes the element at the specified index from the collection.
     *
     * @param string|int $key the kex/index of the element to remove
     *
     * @return mixed the removed element or NULL, if the collection did not contain the element
     */
    public function remove($key)
    {
        if (!isset($this->elements[$key]) && !array_key_exists($key, $this->elements)) {
            return null;
        }

        $removed = $this->elements[$key];
        unset($this->elements[$key]);

        return $removed;
    }

    /**
     * Removes the specified element from the collection, if it is found.
     * The comparison is not strict (==), they have the same attributes and values,
     * and are instances of the same class.
     *
     * @param mixed $element the element to remove
     *
     * @return bool tRUE if this collection contained the specified element, FALSE otherwise
     */
    public function removeElement($element) : bool
    {
        $key = array_search($element, $this->elements);

        if ($key === false) {
            return false;
        }

        unset($this->elements[$key]);

        return true;
    }

    /**
     * Removes the specified element from the collection, if it is found.
     * The comparison is strict (===), they refer to the same instance of the same class.
     *
     * @param mixed $element the element to remove
     *
     * @return bool tRUE if this collection contained the specified element, FALSE otherwise
     */
    public function removeInstance($element) : bool
    {
        $key = array_search($element, $this->elements, true);

        if ($key === false) {
            return false;
        }

        unset($this->elements[$key]);

        return true;
    }

    /**
     * Remove from current all element find by property or method value.
     *
     * @param string $method property or method
     * @param mixed $value the comparison value
     *
     * @return $this a new collection with remove element
     */
    public function removeFind(string $method, $value) : self
    {
        $list = $this->find($method, $value);
        foreach ($list as $element) {
            $this->removeInstance($element);
        }

        return $list;
    }

    /**
     * Clears the collection, removing all elements.
     *
     * @return $this|self
     */
    public function clear() : self
    {
        $this->elements = [];

        return $this;
    }

    /**
     * Required by interface ArrayAccess.
     *
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $this->containsKey($offset);
    }

    /**
     * Required by interface ArrayAccess.
     *
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Required by interface ArrayAccess.
     *
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if (!isset($offset)) {
            return $this->add($value);
        }

        return $this->set($offset, $value);
    }

    /**
     * Required by interface ArrayAccess.
     *
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        return $this->remove($offset);
    }

    /**
     * Checks whether the collection contains an element with the specified key/index.
     *
     * @param string|int $key the key/index to check for
     *
     * @return bool tRUE if the collection contains an element with the specified key/index,
     *              FALSE otherwise
     */
    public function containsKey($key) : bool
    {
        return isset($this->elements[$key]) || array_key_exists($key, $this->elements);
    }

    /**
     * Checks whether an element is contained in the collection.
     * The comparison is not strict (==), they have the same attributes and values,
     * and are instances of the same class.
     * This is an O(n) operation, where n is the size of the collection.
     *
     * @param mixed $element the element to search for
     *
     * @return bool tRUE if the collection contains the element, FALSE otherwise
     */
    public function contains($element) : bool
    {
        return in_array($element, $this->elements);
    }

    /**
     * Checks whether an reference is contained in the collection.
     * The comparison is strict (===), they refer to the same instance of the same class.
     * This is an O(n) operation, where n is the size of the collection.
     *
     * @param mixed $element the element to search for
     *
     * @return bool tRUE if the collection contains the element, FALSE otherwise
     */
    public function containsInstance($element) : bool
    {
        return in_array($element, $this->elements, true);
    }

    /**
     * Tests for the existence of an element that satisfies the given predicate.
     *
     * @param callable $callable the predicate
     *
     * @return bool tRUE if the predicate is TRUE for at least one element, FALSE otherwise
     */
    public function exists(callable $callable) : bool
    {
        foreach ($this->elements as $key => $element) {
            if ($callable($key, $element)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compare with current collection, add missing, remove unnecessary on current.
     *
     * @param Collection $collection
     *
     * @return $this|self
     */
    public function diff(Collection $collection) : self
    {
        foreach ($this->elements as $element) {
            if (!$collection->contains($element)) {
                $this->removeElement($element);
            }
        }

        foreach ($collection as $element) {
            if (!$this->contains($element)) {
                $this->add($element);
            }
        }

        return $this;
    }

    /**
     * Gets the index/key of a given element. The comparison of two elements is strict,
     * that means not only the value but also the type must match.
     * For objects this means reference equality.
     *
     * @param mixed $element the element to search for
     *
     * @return int|string|bool the key/index of the element or FALSE if the element was not found
     */
    public function indexOf($element)
    {
        return array_search($element, $this->elements, true);
    }

    /**
     * Gets the element at the specified key/index.
     *
     * @param string|int $key the key/index of the element to retrieve
     *
     * @return mixed
     */
    public function get($key)
    {
        return isset($this->elements[$key]) ? $this->elements[$key] : null;
    }

    /**
     * Gets all keys/indices of the collection.
     *
     * @return array the keys/indices of the collection, in the order of the corresponding
     *               elements in the collection
     */
    public function getKeys() : array
    {
        return array_keys($this->elements);
    }

    /**
     * Reset all keys/indices of the collection.
     * To used only on numeric index !
     *
     * @return $this|self
     */
    public function resetIndex() : self
    {
        $this->elements = array_values($this->elements);

        return $this;
    }

    /**
     * Gets all values of the collection.
     *
     * @return array the values of all elements in the collection, in the order they
     *               appear in the collection
     */
    public function getValues() : array
    {
        return array_values($this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function count() : int
    {
        return count($this->elements);
    }

    /**
     * Sets an element in the collection at the specified key/index.
     *
     * @param string|int $key   the key/index of the element to set
     * @param mixed      $value the element to set
     *
     * @return $this|self
     */
    public function set($key, $value) : self
    {
        $this->elements[$key] = $value;

        return $this;
    }

    /**
     * Adds an element at the end of the collection.
     *
     * @param array $elements the elements to add
     *
     * @return $this|self
     */
    public function add(...$elements) : self
    {
        foreach ($elements as $element) {
            $this->elements[] = $element;
        }

        return $this;
    }

    /**
     * Checks whether the collection is empty (contains no elements).
     *
     * @return bool tRUE if the collection is empty, FALSE otherwise
     */
    public function isEmpty() : bool
    {
        return empty($this->elements);
    }

    /**
     * Required by interface IteratorAggregate.
     *
     * {@inheritdoc}
     */
    public function getIterator() : \ArrayIterator
    {
        return new \ArrayIterator($this->elements);
    }

    /**
     * Applies the given function to each element in the collection and returns
     * a new collection with the elements returned by the function.
     *
     * @param callable $callable
     *
     * @return $this|self
     */
    public function apply(callable $callable) : self
    {
        return new static(array_map($callable, $this->elements));
    }

    /**
     * Call the given method to each element in the collection and returns
     * a new collection with return values for each call.
     *
     * @param string $method
     * @param mixed ...$vars
     *
     * @return $this|self
     */
    public function call(string $method, ...$vars) : self
    {
        return new static(array_map(function ($element) use ($method, $vars) {
            return call_user_func_array([$element, $method], $vars);
        }, $this->elements));
    }

    /**
     * Returns all the elements of this collection that satisfy the callable $callable.
     * The order of the elements is preserved.
     *
     * @param callable $callable the predicate used for filtering
     *
     * @return $this|self a collection with the results of the filter operation
     */
    public function filter(callable $callable) : self
    {
        return new static(array_filter($this->elements, $callable, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * @param string $method
     * @param mixed  $item
     * @param bool $isMethod
     * @param array $args args to pass to method
     *
     * @return mixed
     */
    private function elementMethodCall(string $method, $item, bool &$isMethod = null, array $args = [])
    {
        if (is_array($item)) {
            return $item[$method];
        }

        if (is_null($isMethod)) {
            $isMethod = method_exists($item, $method);
        }

        if ($isMethod) {
            return call_user_func_array([$item, $method], $args);
        } else {
            return $item->$method;
        }
    }

    /**
     * Return a new collection find by property or method value.
     *
     * @param string $method property or method
     * @param mixed $value the comparison value
     * @param array $args args to pass to method
     *
     * @return $this|self
     */
    public function find(string $method, $value, array $args = [])
    {
        $isMethod = null;

        return new static(array_filter($this->elements, function ($item) use ($method, $value, &$isMethod, $args) {
            return $this->elementMethodCall($method, $item, $isMethod, $args) === $value;
        }));
    }

    /**
     * Return a new collection find by property or method value different.
     *
     * @param string $method property or method
     * @param mixed $value the comparison value
     * @param array $args args to pass to method
     *
     * @return $this|Collection
     */
    public function findDifferent(string $method, $value, array $args = [])
    {
        $isMethod = null;

        return new static(array_filter($this->elements, function ($item) use ($method, $value, &$isMethod, $args) {
            return $this->elementMethodCall($method, $item, $isMethod, $args) !== $value;
        }));
    }

    /**
     * Return one element find by property or method value.
     *
     * @param string $method property or method
     * @param mixed $value the comparison value
     * @param array $args args to pass to
     *
     * @return mixed|null
     */
    public function findOne(string $method, $value, array $args = [])
    {
        $return = $this->find($method, $value, $args);

        if ($return->count() > 1) {
            throw new \OverflowException(sprintf(
                "Too many element return (%s), needed only one for '%s' = '%s'",
                $return->count(),
                $method,
                is_object($value) ? get_class($value) : $value
            ));
        }

        return $return->count() ? $return->first() : null;
    }

    /**
     * Tests whether the given callable $callable holds for all elements of this collection.
     *
     * @param callable $callable the predicate
     *
     * @return bool tRUE, if the predicate yields TRUE for all elements, FALSE otherwise
     */
    public function forAll(callable $callable) : bool
    {
        foreach ($this->elements as $key => $element) {
            if (!$callable($key, $element)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Partitions this collection in two collections according to a predicate.
     * Keys are preserved in the resulting collections.
     *
     * @param callable $callable $p The predicate on which to partition
     *
     * @return $this[]|self[] An array with two elements. The first element contains the collection
     *     of elements where the predicate returned TRUE, the second element
     *     contains the collection of elements where the predicate returned FALSE.
     */
    public function partition(callable $callable) : array
    {
        $matches = $noMatches = [];

        foreach ($this->elements as $key => $element) {
            if ($callable($element, $key)) {
                $matches[$key] = $element;
            } else {
                $noMatches[$key] = $element;
            }
        }

        return [new static($matches), new static($noMatches)];
    }

    /**
     * Partitions this collection in collections according to a predicate returnr value.
     * Keys are not preserved in the resulting collections.
     *
     * @param callable $callable $p The predicate on which to partition
     *
     * @return $this[]|self[] An array of collection indexed by predicate return value
     */
    public function partitions(callable $callable) : array
    {
        /** @var self[] $return */
        $return = [];

        foreach ($this->elements as $key => $element) {
            $value = $callable($element, $key);

            if (!isset($return[$value])) {
                $return[$value] = new static();
            }

            $return[$value]->add($element);
        }

        return $return;
    }

    /**
     * Extracts a slice of $length elements starting at position $offset from the Collection.
     *
     * If $length is null it returns all elements from $offset to the end of the Collection.
     * Keys have to be preserved by this method. Calling this method will only return the
     * selected slice and NOT change the elements contained in the collection slice is called on.
     *
     * @param int      $offset the offset to start from
     * @param int|null $length the maximum number of elements to return, or null for no limit
     *
     * @return $this|self
     */
    public function slice($offset, $length = null) : self
    {
        return new static(array_slice($this->elements, $offset, $length, true));
    }

    /**
     * Merge current collection and passed collection.
     * Return a new collection instance and don't alter current one.
     *
     * @param Collection $collection
     *
     * @return $this|self
     */
    public function merge(Collection $collection) : self
    {
        $return = clone $this;
        $return->elements = array_merge($return->elements, $collection->elements);

        return $return;
    }

    /**
     * Sort current collection with a callable base on values
     * Key association is not maintains.
     *
     * @param callable $sortFunction
     *
     * @return $this|self
     */
    public function sort(callable $sortFunction) : self
    {
        usort($this->elements, $sortFunction);

        return $this;
    }

    /**
     * Sort current collection with a callable base on values
     * Key association is maintains.
     *
     * @param callable $sortFunction
     *
     * @return $this|self
     */
    public function sortByKey(callable $sortFunction) : self
    {
        uksort($this->elements, $sortFunction);

        return $this;
    }

    /**
     * Sort current collection with a callable base on values
     * Key association is maintains.
     *
     * @param callable $sortFunction
     *
     * @return $this|self
     */
    public function sortAssociative(callable $sortFunction) : self
    {
        uasort($this->elements, $sortFunction);

        return $this;
    }

    /**
     * @param bool $preserveKeys
     *
     * @return $this|Collection
     */
    public function reverse($preserveKeys = false) : self
    {
        return new static(array_reverse($this->elements, $preserveKeys));
    }

    /**
     * @param array $fields
     *
     * @return self
     */
    public function sortBy(array $fields) : self
    {
        if ($this->count() == 0) {
            return new static($this->elements);
        }

        /** @var array|bool[] $isMethod */
        $isMethod = [];
        $values = [];

        foreach ($fields as $name => $options) {
            foreach ($this->elements as $key => $element) {
                if (is_array($element)) {
                    $values[$name][$key] = $element[$name];
                } else {
                    if (!array_key_exists($name, $isMethod)) {
                        $isMethod[$name] = method_exists($element, $name);
                    }

                    if ($isMethod[$name]) {
                        $values[$name][$key] = call_user_func([$element, $name]);
                    } else {
                        $values[$name][$key] = $element->$name;
                    }
                }
            }
        }

        $args = [];

        foreach ($fields as $name => $options) {
            $args[] = $values[$name];
            $args[] = $options;
        }

        $return = $this->elements;
        $args[] = &$return;

        call_user_func_array('array_multisort', $args);

        return new static($return);
    }

    /**
     * Swap 2 elements by index
     *
     * @param string|int $index
     * @param string|int $newIndex
     *
     * @return bool
     */
    public function swapIndex($index, $newIndex) : bool
    {
        if ($newIndex < 0 || $newIndex > sizeof($this->elements)) {
            return false;
        }

        $object = $this->elements[$index];
        $newObject = $this->elements[$newIndex];
        $this->elements[$newIndex] = $object;
        $this->elements[$index] = $newObject;

        return true;
    }

    /**
     * Move element down
     *
     * @param int $index
     *
     * @return bool
     */
    public function moveDown(int $index) : bool
    {
        return $this->swapIndex($index, $index - 1);
    }

    /**
     * Move element top
     *
     * @param int $index
     *
     * @return bool
     */
    public function moveUp(int $index) : bool
    {
        return $this->swapIndex($index, $index + 1);
    }

    /**
     * @param string $method property or method
     * @param bool $returnValue
     *
     * @return $this|Collection
     */
    public function getDistinct(string $method, bool $returnValue = true)
    {
        $isMethod = null;

        $array = [];

        foreach ($this->elements as $item) {
            $value = $this->elementMethodCall($method, $item, $isMethod);

            if (!in_array($value, $array, true)) {
                $array[] = $returnValue ? $value : $item;
            }
        }

        return new static(array_unique($array, SORT_REGULAR));
    }

    /**
     * @param string $method
     * @param bool $min
     *
     * @return array
     */
    private function getMinMax(string $method, bool $min) : array
    {
        $isMethod = null;
        $returnValue = null;
        $returnItem = null;

        foreach ($this->elements as $item) {
            $value = $this->elementMethodCall($method, $item, $isMethod);

            $ok = is_null($returnValue) ||
                ($min && $value < $returnValue) ||
                (!$min && $value > $returnValue);

            if ($ok) {
                $returnValue = $value;
                $returnItem = $item;
            }
        }

        return [$returnValue, $returnItem];
    }

    /**
     * @param string $method
     *
     * @return array
     */
    public function getMin(string $method) : array
    {
        return $this->getMinMax($method, true);
    }

    /**
     * @param string $method
     *
     * @return array
     */
    public function getMax(string $method) : array
    {
        return $this->getMinMax($method, false);
    }

    /**
     * @return $this|self
     */
    public function shuffle() : self
    {
        $return = new static($this->elements);
        shuffle($return->elements);

        return $return;
    }
}
