<?
//  STString.php
//  Sonata/Foundation
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
    
    public function replace($from, $to = '') {
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