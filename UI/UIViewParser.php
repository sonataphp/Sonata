<?
//  UIViewParser.php
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

class UIViewParser {
    
    private static $delegate;
    
    private static function determineCssMedia($type) {
        return (is_array($type))?implode(",", $type):'';
    }
    
    private static function filter($result, $params) {
        unset($params[0]);
        if (is_array($params) && count($params) > 0)
            foreach ($params as $param)
                if (function_exists($param))
                    $result = $param($result);
        return $result;
    }
    
    public static function callback($matches) {
        $result = $matches[0];
        switch ($result) {
            case "{{url}}":
                $result = UIApplicationUrl();
                break;
            
            case "{{url:SSL}}":
                $result = UIApplicationSetSSLProtocol(UIApplicationUrl());
                break;
            
            case "{{pageTitle}}":
                $result = self::$delegate->view->title;
                break;
            
            case "{{pageTitle}}":
                $result = self::$delegate->view->title;
                break;
            
            case "{{favicon}}":     
                $result = '<link rel="icon" href="'.UIApplication::sharedApplication()->settings->iconFile.'" type="image/x-icon" />'."\r\n".
                          '<link rel="shortcut icon" href="'.UIApplication::sharedApplication()->settings->iconFile.'" type="image/x-icon" />';
                break;
            
            case "{{applicationTitle}}":
                $result = UIApplicationTitle();
                break;
            
            case "{{location}}":
                $result = UIApplicationLocation();
                break;
            
            case "{{location:SSL}}":
                $result = UIApplicationSetSSLProtocol(UIApplicationLocation());
                break;
            
            case "{{metaDescription}}":
                $result = '<meta name="description" content="'.self::$delegate->view->description.'" />';
                break;
            
            case "{{metaKeywords}}":
                $result = '<meta name="keywords" content="'.self::$delegate->view->keywords.'" />';
                break;
            
            case "{{images}}":
                $result = UIApplicationCheckProtocol(UIApplication::sharedApplication()->settings->paths->images);
                break;
            
            case "{{javascripts}}":
                if (!self::$delegate->view->scripts) {
                    $result = '';
                    break;
                }
                foreach (self::$delegate->view->scripts as $script) {
                    $data[] = '<script type="text/javascript" src="'.$script.'"></script>';
                }
                $result = implode("\r\n", $data);
                break;
            
            case "{{styles}}":
                if (!self::$delegate->view->styles) {
                    $result = '';
                    break;
                }
                foreach (self::$delegate->view->styles as $style) {
                    $data[] = '<link rel="stylesheet" href="'.$style['src'].'" media="'.self::determineCssMedia($style['type']).'" type="text/css" />';
                }
                $result = implode("\r\n", $data);
                break;
            
            case "{{ie6styles}}":
                if (!self::$delegate->view->getStylesIE6()) {
                    $result = '';
                    break;
                }
                foreach (self::$delegate->view->getStylesIE6() as $style) {
                    $data[] = '<link rel="stylesheet" href="'.$style['src'].'" media="'.self::determineCssMedia($style['type']).'" type="text/css" />';
                }
                $result = implode("\r\n", $data);
                break;
            
            case "{{ie7styles}}":
                if (!self::$delegate->view->getStylesIE7()) {
                    $result = '';
                    break;
                }
                foreach (self::$delegate->view->getStylesIE7() as $style) {
                    $data[] = '<link rel="stylesheet" href="'.$style['src'].'" media="'.self::determineCssMedia($style['type']).'" type="text/css" />';
                }
                $result = implode("\r\n", $data);
                break;
            
            case "{{ie67styles}}":
                if (!self::$delegate->view->getStylesIE67()) {
                    $result = '';
                    break;
                }
                foreach (self::$delegate->view->getStylesIE67() as $style) {
                    $data[] = '<link rel="stylesheet" href="'.$style['src'].'" media="'.self::determineCssMedia($style['type']).'" type="text/css" />';
                }
                $result = implode("\r\n", $data);
                break;
            
            case "{{ieallstyles}}":
                if (!self::$delegate->view->getStylesIEAll()) {
                    $result = '';
                    break;
                }
                foreach (self::$delegate->view->getStylesIEAll() as $style) {
                    $data[] = '<link rel="stylesheet" href="'.$style['src'].'" media="'.self::determineCssMedia($style['type']).'" type="text/css" />';
                }
                $result = implode("\r\n", $data);
                break;
        }
        if ( (strpos($result, "var:") > 0) || (strpos($result, "post:")) || (strpos($result, "get:")) ) {
            $ar = array();
            preg_match_all("/\{\{[a-z\d_-]{1,}:([a-z\d_>-]{1,})\}\}/i", $result, $ar);
            $params = explode(">", $ar[1][0]);
            $var = $params[0];
            if (strpos($result, "var:") > 0)
                if (property_exists(self::$delegate, $var)) {
                    $result = self::$delegate->$var;
                    $result = self::filter($result, $params);
                }
            if (strpos($result, "post:")) {
                $result = isset(STRequest::postParams()->$var)?html(STRequest::postParams()->$var):'';
                $result = self::filter($result, $params);
            }
            if (strpos($result, "get:")) {
                $result = isset(STRequest::getParams()->$var)?html(STRequest::getParams()->$var):'';
                $result = self::filter($result, $params);
            }
        }
        return $result;
    }
    
    public static function parse(&$delegate, $content) {
        self::$delegate = $delegate;
        $content = preg_replace_callback("/\{\{(?:[a-z0-9\_\:\s\-\/\>]+)\}\}/i",
                                         array('UIViewParser', 'callback'),
                                         $content);
        return $content;
    }
    
}

?>