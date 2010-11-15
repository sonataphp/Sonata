<?
//  UIViewController.php
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

include "UI/UIViewParser.php";

class UIViewControllerException extends UI404Error {}

class UIViewController extends STObject {
    
    public $view = null;
    public $defaultViewPath = 'PHTMLs/';
    public $params;
    public $action;
    private $_isViewLoaded = false;
    
    // Creating Instances
    public function init() {
        
    }
    
    public function bufferizeTemplates() {
        $compiled = '';
        $this->_isViewLoaded = true;
        $phtmls = $this->view->subviews();
        if (is_array($phtmls) && (count($phtmls > 0)))
        foreach ($phtmls as $fileName) {
            ob_clean();
            ob_start();
            if (UIViewCache::isCached($fileName)) {
                $this->attachSubview(UIViewCache::getTemplate($fileName));
            } else {
                $this->attachSubview($this->defaultViewPath.$fileName.".phtml");
            }
            $partial = ob_get_clean();
            $compiled .= $partial;
            if (UIViewCache::needToCacheTemplate($fileName)) {
                UIViewCache::setTemplate($fileName, $partial);
            }
        }

        STBuffer::writeToBuffer('__view'.$this->className(), $compiled);
        $this->viewDidLoad();
    }
    
    public function attachSubview($fileName) {
        require $fileName;
    }
    
    public function presentViewController() {
        STBuffer::outputBufferToScreen('__view'.$this->className());
        $this->viewDidUnload();
    }
    
    public function presentViewControllerToString() {
        $buffer = STBuffer::outputBufferToString('__view'.$this->className());
        return $buffer;
    }
    
    public function viewDidLoad() {
        STBuffer::writeToBuffer('__view'.$this->className(),
                                UIViewParser::parse($this, STBuffer::outputBufferToString('__view'.$this->className()))
                                );
    }
    
    public function viewDidUnLoad() {
        STBuffer::cleanBuffer('__view'.$this->className());
    }
    
    public function isViewLoaded() {
        return $this->_isViewLoaded;
    }
    
    public function delegate($controller, $action) {
        $controller = new $controller();
        $action.= "Action";
        $controller->action = $this->action;
        $controller->params = $this->params;
        $controller->view = $this->view;
        $controller->$action();
    }
    
}

?>