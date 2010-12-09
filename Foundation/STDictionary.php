<?php
//  STDictionary.php
//  Sonata/Foundation
//
// Copyright 2010 Jonty Wareing <http://www.jeremyjohnstone.com>
// Based on pList Parser
//
// Modified to fit Sonata Framework syntax by Roman Efimov <romefimov@gmail.com>
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

/* @uses XMLReader */

class STDictionary extends STArray {
    
    /* @var XMLReader */ 
    private $reader;
    protected $data;
    
    public function __construct() {
        $this->reader = new XMLReader();
    }
    
    public function initWithContentsOfFile($file) {
        $file = CFAppPath.$file;
        if(basename($file) == $file) {
            throw new Exception("Non-relative file path expected", 1);
        }
        $this->reader->open("file://" . $file);
        $this->data = $this->process();
        if ($this->data)
        foreach ($this->data as $key => $value) {
            $this[$key] = $value;
        }
        unset($this->data);
        return $this;
    }

    public function initWithContentsOfString($string) {
        $this->reader->XML($string);
        $this->data = $this->process();
        return $this;
    }
    
    public function allValues() {
        return $this->data;
    }
    
    private function process() {
        // plist's always start with a doctype, use it as a validity check
        $this->reader->read();
        if($this->reader->nodeType !== XMLReader::DOC_TYPE || $this->reader->name !== "plist") {
            throw new Exception(sprintf("Error parsing plist. nodeType: %d -- Name: %s", $this->reader->nodeType, $this->reader->name), 2);
        }
        
        // as one additional check, the first element node is always a plist
        if(!$this->reader->next("plist") || $this->reader->nodeType !== XMLReader::ELEMENT || $this->reader->name !== "plist") {
            throw new Exception(sprintf("Error parsing plist. nodeType: %d -- Name: %s", $this->reader->nodeType, $this->reader->name), 3);
        }
        
        $plist = array();	
        while($this->reader->read()) {
            if($this->reader->nodeType == XMLReader::ELEMENT) {
                    $plist[] = $this->parseNode();
            }
        }
        if(count($plist) == 1 && $plist[0]) {
            // Most plists have a dict as their outer most tag
            // So instead of returning an array with only one element
            // return the contents of the dict instead
            return $plist[0];
        } else {
            return $plist;
        }
    }
    
    private function parseNode() {
        // If not an element, nothing for us to do
        if($this->reader->nodeType !== XMLReader::ELEMENT) return;
        
        switch($this->reader->name) {
            case 'data': 
                    return base64_decode($this->getNodeText());
                    break;
            case 'real':
                    return floatval($this->getNodeText());
                    break;
            case 'string':
                    return $this->getNodeText();
                    break;
            case 'integer':
                    return intval($this->getNodeText());
                    break;
            case 'date':
                    return $this->getNodeText();
                    break;
            case 'true':
                    return true;
                    break;
            case 'false':
                    return false;
                    break;
            case 'array':
                    return $this->parseArray();
                    break;
            case 'dict':
                    return $this->parseDict();
                    break;
            default:
                    // per DTD, the above is the only valid types
                    throw new Exception(sprintf("Not a valid plist. %s is not a valid type", $this->name), 4);
        }			
    }
    
    private function parseDict() {
        $array = array();
        $this->nextOfType(XMLReader::ELEMENT);
        do {
            if($this->reader->nodeType !== XMLReader::ELEMENT || $this->reader->name !== "key") {
                    // If we aren't on a key, then jump to the next key
                    // per DTD, dicts have to have <key><somevalue> and nothing else
                    if(!$this->reader->next("key")) {
                            // no more keys left so per DTD we are done with this dict
                            return $array;
                    }
            }
            $key = $this->getNodeText();
            $this->nextOfType(XMLReader::ELEMENT);
            $array[$key] = $this->parseNode();
            $this->nextOfType(XMLReader::ELEMENT, XMLReader::END_ELEMENT);
        } while($this->reader->nodeType && !$this->isNodeOfTypeName(XMLReader::END_ELEMENT, "dict"));
        return $array;
    }
    
    private function parseArray() {
        $array = array();
        $this->nextOfType(XMLReader::ELEMENT);
        do {
            $array[] = $this->parseMode();
            // skip over any whitespace
            $this->nextOfType(XMLReader::ELEMENT, XMLReader::END_ELEMENT);
        } while($this->reader->nodeType && !$this->isNodeOfTypeName(XMLReader::END_ELEMENT, "array"));
        return $array;
    }
    
    private function getNodeText() {
        $string = $this->reader->readString();
        // now gobble up everything up to the closing tag
        $this->nextOfType(XMLReader::END_ELEMENT);
        return $string;
    }
    
    private function nextOfType() {
        $types = func_get_args();
        // skip to next
        $this->reader->read();
        // check if it's one of the types requested and loop until it's one we want
        while($this->reader->nodeType && !(in_array($this->reader->nodeType, $types))) {
            // node isn't of type requested, so keep going
            $this->reader->read();
        }
    }
    
    private function isNodeOfTypeName($type, $name) {
        return $this->reader->nodeType === $type && $this->reader->name === $name;
    }
}

function STDictionary() {
    return new STDictionary();
}

?>