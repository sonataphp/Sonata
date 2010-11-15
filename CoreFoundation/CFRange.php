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
    
    public function copyFromArray($array) {
        $this->container = $array;
    }
    
    public function __construct($location, $length) {
        for ($i = $location; $i <= $location+$length; $i++)
            $this->container[] = $i;
    }
    
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
    
    public function serialize() {
        return serialize($this->container);
    }
    public function unserialize($data) {
        $this->container = unserialize($data);
    }
    
    public function count() {
        return count($this->container);
    }
}

function CFRangeMake($location, $length) {
    return new CFRange($location, $length);
}

function CFRangeFromString($string) {
    $range = new CFRange(0, 0);
    $range->copyFromArray(explode(",", $string));
    return $range;
}

?>