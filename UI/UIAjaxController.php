<?php

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