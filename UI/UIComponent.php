<?php
//  UIComponent.php
//  Sonata/UI
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

abstract class UIComponent extends UIViewController {
    
    protected $content;
    protected $delegate;
    
    public function setDelegate($delegate) {
        $this->delegate = $delegate;
    }
    
    public function __construct($content) {
        $this->content = $content;
        $xml = simplexml_load_string($content);
        foreach ($xml->attributes() as $key => $attribute) {
            $this->$key = strval($attribute);
        }
    }
    
    abstract public function renderComponent();

}

?>