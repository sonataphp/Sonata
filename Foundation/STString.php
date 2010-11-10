<?
//
//  STString.php
//  Sonata/Foundation
//
//  Created by Roman Efimov on 6/10/2010.
//

class STString extends STObject implements ArrayAccess {
    
    protected $s;
    
    public function __construct($s) {
        $this->s = $s;
    }
    
    public function __invoke($s) {
        $this->s = $s;
    }
    
    public function __toString() {
        return $this->s;    
    }
    
    public function capitalize() {
        $this->s = ucwords($this->s);
        return $this;
    }
    
    public function length() {
        return strlen($this->s);
    }
    
    public function replace($from, $to) {
        $this->s = str_replace($from, $to, $this->s);
        return $this;
    }
    
    public function offsetSet($offset, $value) {
        if (is_array($value)) $value = implode("", $value);
        $this->s .= $value;
    }
    
    public function offsetExists($offset) {
        //
    }
    
    public function offsetUnset($offset) {
        //
    }
    
    public function offsetGet($offset) {
        return $this->s[$offset];
    }
}

function STString($s) {
    return new STString($s);
}

?>