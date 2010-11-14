<?

class UIViewParser {
    
    private static $delegate;
    
    private static function determineCssMedia($type) {
        return (is_array($type))?implode(",", $type):'';
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
        if (strpos($result, "post:") > 0) {
            $ar = array();
            preg_match_all("/\{\{[a-z\d_-]{1,}:([a-z\d_-]{1,})\}\}/i", $result, $ar);
            $result = isset(STRequest::postParams()->$ar[1][0])?html(STRequest::postParams()->$ar[1][0]):'';
        }
        if (strpos($result, "get:") > 0) {
            $ar = array();
            preg_match_all("/\{\{[a-z\d_-]{1,}:([a-z\d_-]{1,})\}\}/i", $result, $ar);
            $result = isset(STRequest::getParams()->$ar[1][0])?html(STRequest::getParams()->$ar[1][0]):'';
        }
        return $result;
    }
    
    public static function parse(&$delegate, $content) {
        self::$delegate = $delegate;
        $content = preg_replace_callback("/\{\{(?:[a-z0-9\_\:\s\-\/]+)\}\}/i",
                                         array('UIViewParser', 'callback'),
                                         $content);
        return $content;
    }
    
}

?>