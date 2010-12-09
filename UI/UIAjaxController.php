<?php
//  UIAjaxController.php
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

class UIAjaxController extends UIViewController {
    
    public $vars;
    
    public function indexAction() {
        if (!STRequest::isAjax()) throw new UI404Error("Invalid Ajax Call");
        
        $this->vars = STRequest::isPost()?STRequest::postParams():STRequest::getParams();
        $method = $this->vars->method;
        unset($this->vars->method);
        if (!method_exists($this, $method)) throw new UI404Error("Invalid Ajax Call");
        
        $this->$method();
    }
    
}

?>