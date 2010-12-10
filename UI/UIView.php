<?php
//  UIView.php
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

require_once "Headers/UIViewHeader.php";

class UIView extends STObject {
    
    public $delegate;
    private $title;
    private $description;
    private $keywords;
    private $subviews = array();
    private $styles = array();
    private $stylesIE6 = array();
    private $stylesIE7 = array();
    private $stylesIE67 = array();
    private $stylesIEAll = array();
    private $scripts = array();
    private $scriptsOriginal = array();
    private $stylesOriginal = array();
    
    public function init() {}
    
    public function initWithDelegate(UIViewController $delegate) {
        $this->delegate($delegate)
             ->init();
    }
    
    public function initWithPhtmlNames($phtmls) {
        $this->addSubview(func_get_args())
             ->init();
    }
    
    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }
    
    public function getTitle() {
        return $this->title;
    }
    
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }
    
    public function getDescription() {
        return $this->description;
    }
    
    public function setKeywords($keywords) {
        $this->keywords = $keywords;
        return $this;
    }
    
    public function getKeywords() {
        return $this->keywords;
    }
    
    public function delegate($delegate) {
        $this->delegate = $delegate;
        return $this;
    }

    public function subviews() {
        return $this->subviews;
    }
    
    private function parseStylesArgs($args) {
        $type = $args[0];
        $media = $args[1];
        for ($i=0; $i < 2; $i++)
            array_shift($args);
            
        switch ($type) {
            case UIViewStylesAnyBrowser:
                foreach ($args as $arg) {
                    $this->styles[] = array("type" => $media, "src" => UIApplicationCheckProtocol(UIApplication::sharedApplication()->settings->paths->styles.$arg));
                    $this->stylesOriginal['all'][] = array("type" => $media, "src" => $arg);
                }
                break;
            case UIViewStylesIE6:
                foreach ($args as $arg) {
                    $this->stylesIE6[] = array("type" => $media, "src" => UIApplicationCheckProtocol(UIApplication::sharedApplication()->settings->paths->styles.$arg));
                    $this->stylesOriginal['ie6'][] = array("type" => $media, "src" => $arg);
                }
                break;
            case UIViewStylesIE7:
                foreach ($args as $arg) {
                    $this->stylesIE7[] = array("type" => $media, "src" => UIApplicationCheckProtocol(UIApplication::sharedApplication()->settings->paths->styles.$arg));
                    $this->stylesOriginal['ie7'][] = array("type" => $media, "src" => $arg);
                }
                break;
            case UIViewStylesIE67:
                foreach ($args as $arg) {
                    $this->stylesIE67[] = array("type" => $media, "src" => UIApplicationCheckProtocol(UIApplication::sharedApplication()->settings->paths->styles.$arg));
                    $this->stylesOriginal['ie67'][] = array("type" => $media, "src" => $arg);
                }
                break;
            case UIViewStylesIEAll:
                foreach ($args as $arg){
                    $this->stylesIEAll[] = array("type" => $media, "src" => UIApplicationCheckProtocol(UIApplication::sharedApplication()->settings->paths->styles.$arg));
                    $this->stylesOriginal['ieall'][] = array("type" => $media, "src" => $arg);
                }
                break;
        }
    }
    
    public function setStylesForBrowser($type, $media, $list) {
        $args = func_get_args();
        switch ($type) {
            case UIViewStylesAnyBrowser:
                $this->styles = $this->stylesOriginal['all'] = array();
                break;
            case UIViewStylesIE6:
                $this->stylesIE6 = $this->stylesOriginal['ie6'] = array();
                break;
            case UIViewStylesIE7:
                $this->stylesIE7 = $this->stylesOriginal['ie7'] = array();
                break;
            case UIViewStylesIE67:
                $this->stylesIE67 = $this->stylesOriginal['ie67'] = array();
                break;
            case UIViewStylesIEAll:
                $this->stylesIEAll = $this->stylesOriginal['ieall'] = array();
                break;
        }
        $this->parseStylesArgs($args);
        return $this;
    }
    
    public function addStylesForBrowser($type, $media, $list) {
        $args = func_get_args();
        $this->parseStylesArgs($args);
        return $this;
    }
    
    public function getStylesForBrowser($type = UIViewStylesAnyBrowser) {
        switch ($type) {
            case UIViewStylesAnyBrowser:
                return $this->styles;
                break;
            case UIViewStylesIE6:
                return $this->stylesIE6;
                break;
            case UIViewStylesIE7:
                return $this->stylesIE7;
                break;
            case UIViewStylesIE67:
                return $this->stylesIE67;
                break;
            case UIViewStylesIEAll:
                return $this->stylesIEAll;
                break;
        }
    }
    
    private function parseScriptsArgs($args) {
        if (!$args) trigger_error(__("No javascripts to attach"), E_USER_ERROR);
        foreach ($args as $arg) {
            $this->scripts[] = UIApplicationCheckProtocol(UIApplication::sharedApplication()->settings->paths->javascripts.$arg);
            $this->scriptsOriginal[] = $arg;
        }
        
        return $this;
    }
    
    public function setScripts() {
        $this->scripts = array();
        $this->scriptsOriginal = array();
        $args = func_get_args();
        $this->parseScriptsArgs($args);
        return $this;
    }
    
    public function addScripts() {
        $args = func_get_args();
        $this->parseScriptsArgs($args);
        return $this;
    }
    
    public function minifyScripts() {
        if (!$this->scriptsOriginal) return $this;
        $scripts = str_replace("=", "", base64_encode(implode(",", $this->scriptsOriginal))).".js";
        $this->scripts = array(UIApplicationCheckProtocol(UIApplicationUrl()."minify/js/".$scripts));
        return $this;
    }
    
    private function minifyCssPath($array) {
        if (!$array) return array();
        $src = array();
        foreach ($array as $row) {
            $src[] = $row['src'];
        }
        return $src;
    }
    
    public function minifyStyles() {
        if ($this->stylesOriginal['all']) {
            $styles['all'] = str_replace("=", "", base64_encode(implode(",", $this->minifyCssPath($this->stylesOriginal['all'])))).".css";
            $this->styles = array(array("type" => $this->styles[0]['type'], "src" => UIApplicationCheckProtocol(UIApplicationUrl()."minify/css/all/".$styles['all'])));
        }
        if ($this->stylesOriginal['ie6']) {
            $styles['ie6'] = str_replace("=", "", base64_encode(implode(",", $this->minifyCssPath($this->stylesOriginal['ie6'])))).".css";
            $this->stylesIE6 = array(array("type" => $this->stylesOriginal['ie6'][0]['type'], "src" => UIApplicationCheckProtocol(UIApplicationUrl()."minify/css/ie6/".$styles['ie6'])));
        }
        if ($this->stylesOriginal['ie7']) {
            $styles['ie7'] = str_replace("=", "", base64_encode(implode(",", $this->minifyCssPath($this->stylesOriginal['ie7'])))).".css";
            $this->stylesIE7 = array(array("type" => $this->stylesOriginal['ie7'][0]['type'], "src" => UIApplicationCheckProtocol(UIApplicationUrl()."minify/css/ie7/".$styles['ie7'])));
        }
        if ($this->stylesOriginal['ie67']) {
            $styles['ie67'] = str_replace("=", "", base64_encode(implode(",", $this->minifyCssPath($this->stylesOriginal['ie67'])))).".css";
            $this->stylesIE67 = array(array("type" => $this->stylesOriginal['ie67'][0]['type'], "src" => UIApplicationCheckProtocol(UIApplicationUrl()."minify/css/ie67/".$styles['ie67'])));
        }
        if ($this->stylesOriginal['ieall']) {
            $styles['ieall'] = str_replace("=", "", base64_encode(implode(",", $this->minifyCssPath($this->stylesOriginal['ieall'])))).".css";
            $this->stylesIEAll = array(array("type" => $this->stylesOriginal['ieall'][0]['type'], "src" => UIApplicationCheckProtocol(UIApplicationUrl()."minify/css/ieall/".$styles['ieall'])));
        }
        
        return $this;
    }
    
    public function getScripts() {
        return $this->scripts;
    }
    
    public function addSubview($phtml = array()) {
        $subviews = func_get_args();
        if ((count($subviews) == 1) && is_array($phtml))
            $subviews = $phtml;
        if (!$this->delegate->isViewLoaded())
            $this->subviews = array_merge($this->subviews, $subviews); else {
                $args = $subviews;
                if (!$args) trigger_error(__("No subviews to add"), E_USER_ERROR);
                foreach ($args as $arg)
                    $this->delegate->attachSubview($this->delegate->defaultTemplatesPath.$arg.".phtml");
            }
        if (method_exists($this, 'didAddSubview'))
            $this->didAddSubView($subviews);
        return $this;
    }
    
    // alias for addSubview
    public function setLayout($phtml = array()) {
        $phtml = func_get_args();
        $this->subviews = array();
        $this->addSubview($phtml);
        return $this;
    }
    
}

function UIView() {
    return new UIView();
}

?>