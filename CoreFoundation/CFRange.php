<?
//
//  CFRange.php
//  Sonata/CoreFoundation
//
//  Created by Roman Efimov on 6/10/2010.
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