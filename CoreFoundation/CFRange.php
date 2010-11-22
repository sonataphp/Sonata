<?
//  CFRange.php
//  Sonata/CoreFoundation
//
// Copyright 2010 Roman Efimov <romefimov@gmail.com>
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//    http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.
//

class CFRange implements IteratorAggregate, ArrayAccess, Serializable, Countable {
    
    private $container;
    
    /*
     *  Creates CFRange object.
     *
     *  @param int $location The start index (0 is the first, as in C arrays).
     *  @param int $length The number of items in the range (can be 0).
     */
    public function __construct($location, $length) {
        for ($i = $location; $i <= $location+$length; $i++)
            $this->container[] = $i;
    }
    
    /*
     *  Copies range from the array.
     *
     *  @param array $array Array to copy ranges from.
     */
    public function copyFromArray($array) {
        $this->container = $array;
    }
    
    /*
     *  Creates a new iterator from the object instance.
     *
     *  @return CFRange A new ArrayIterator object.
     */
    public function getIterator() {
        return new ArrayIterator($this->container);
    }
    
    public function offsetSet($offset, $value) {
        $this->container[$offset] = $value;
    }
    
    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }
    
    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }
    
    public function offsetGet($offset) {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }
    
    /*
     *  Serializes range.
     *
     *  @return string Serialized range.
     */
    public function serialize() {
        return serialize($this->container);
    }
    public function unserialize($data) {
        $this->container = unserialize($data);
    }
    
    /*
     *  Counts items in the container object.
     *
     *  @return int Number of items in the range.
     */
    public function count() {
        return count($this->container);
    }
}

// ===== CoreFoundation functions that operate on ranges =======

/*
 *  Creates a new CFRange from the specified values.
 *
 *  @param int $location The start index (0 is the first, as in C arrays).
 *  @param int $length The number of items in the range (can be 0).
 *  @return CFRange A new CFRange object.
 */
function CFRangeMake($location, $length) {
    return new CFRange($location, $length);
}

/*
 *  Creates a new CFRange from the string, values separated with $separator.
 *
 *  @param string $string String to make range from.
 *  @param string $separator Values separator, "," is default.
 *  @return CFRange A new CFRange object.
 */
function CFRangeMakeFromString($string, $separator = ',') {
    $range = new CFRange(0, 0);
    $range->copyFromArray(explode($separator, $string));
    return $range;
}

?>