<?php

include "Headers/UIViewControllerDelegate.php";
include "UI/UIViewParser.php";

class UIViewControllerException extends UI404Error {}

class UIViewController extends STObject/* implements UIViewControllerDelegate */{
    
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