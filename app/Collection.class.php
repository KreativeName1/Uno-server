<?php

namespace App;

use Iterator;
use Exception;

class Collection implements Iterator
{
  private array $items;

  public function __construct()
  {
    $this->items = [];
  }

  // Add an item to the collection
  // If the collection is empty, any type of item can be added>can be added
  public function add(mixed $item)
  {
    if (empty($this->items) || gettype($item) === gettype($this->items[0])) {
      $this->items[] = $item;
    } else {
      throw new Exception("Invalid item type. List can only hold objects of type: " . gettype($this->items[0]));
    }
  }

  // Remove an item from the collection
  public function remove(mixed $item): void
  {
    $index = array_search($item, $this->items, true); // Strict comparison
    if ($index !== false) {
      unset($this->items[$index]);
      $this->items = array_values($this->items); // Reindex the array
    }
  }

  // Get an item from the collection by index
  public function get(int $index): mixed
  {
    if (isset($this->items[$index])) {
      return $this->items[$index];
    } else {
      throw new Exception("Index out of bounds");
    }
  }

  // Insert an item at a specific index
  public function insert(int $index, mixed $item): void
  {
    if (empty($this->items) || gettype($item) === gettype($this->items[0])) {
      array_splice($this->items, $index, 0, [$item]);
    } else {
      throw new Exception("Invalid item type. List can only hold objects of type: " . gettype($this->items[0]));
    }
  }


  // Update an item at a specific index
  // Replaces the item at the specified index with a new item
  public function update(int $index, mixed $newItem): void
  {
    if (!isset($this->items[$index])) {
      throw new Exception("Index out of bounds");
    }
    if (!empty($this->items) && gettype($newItem) !== gettype($this->items[0])) {
      throw new Exception("Invalid item type. List can only hold objects of type: " . gettype($this->items[0]));
    }
    $this->items[$index] = $newItem;
  }


  // Slice the collection at a specific index and length
  // Returns a new collection with the sliced items
  public function slice(int $start, int $length = null): Collection
  {
    $newCollection = new Collection();
    $newCollection->items = array_slice($this->items, $start, $length);
    return $newCollection;
  }

  // Filter the collection using a callback function
  // Returns a new collection with the filtered items
  public function filter(callable $callback): Collection
  {
    $newCollection = new Collection();
    $newCollection->items = array_filter($this->items, $callback);
    return $newCollection;
  }

  /// Map the collection using a callback function
  public function find(callable $callback): mixed
  {
    foreach ($this->items as $item) {
      if ($callback($item)) {
        return $item;
      }
    }
    return null;
  }

  // Get the number of items in the collection
  public function count(): int
  {
    return count($this->items);
  }

  // Clear the collection
  public function clear(): void
  {
    $this->items = [];
  }

  // Check if the collection contains an item
  public function contains(mixed $item): bool
  {
    return in_array($item, $this->items, true);
  }


  // Get the index of an item in the collection
  public function indexOf(mixed $item): int
  {
    $index = array_search($item, $this->items, true);
    return ($index !== false) ? $index : -1;
  }


  // Convert the collection to an array
  public function toArray(): array
  {
    return $this->items;
  }


  // Shuffle the items in the collection randomly
  public function shuffle(): bool
  {
    return shuffle($this->items);
  }


  // Gets the first item in the collection
  public function first(): mixed
  {
    return reset($this->items);
  }

  // Gets the last item in the collection
  public function last(): mixed
  {
    return end($this->items);
  }


  // Removes and returns the first item in the collection
  public function pop(): mixed
  {
    return array_pop($this->items);
  }


  // Following methods are required for the Iterator interface

  // Get the current item in the collection
  public function current(): mixed
  {
    return current($this->items);
  }

  // Move to the next item in the collection
  public function next(): void
  {
    next($this->items);
  }

  // Get the key of the current item in the collection
  public function key(): mixed
  {
    return key($this->items);
  }

  // Check if the current item is valid
  public function valid(): bool
  {
    return key($this->items) !== null;
  }

  // Reset the collection pointer
  public function rewind(): void
  {
    reset($this->items);
  }
}
