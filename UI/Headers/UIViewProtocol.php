<?php

define("UIViewStylesAnyBrowser", 1001);
define("UIViewStylesIE6", 1002);
define("UIViewStylesIE7", 1003);
define("UIViewStylesIE67", 1004);
define("UIViewStylesIEAll", 1005);

define('UIViewMediaAll',        "all");
define('UIViewMediaProjection', "projection");
define('UIViewMediaScreen',     "screen");
define('UIViewMediaTty',        "tty");
define('UIViewMediaHandheld',   "handheld");
define('UIViewMediaPrint',      "print");
define('UIViewMediaBraille',    "braille");
define('UIViewMediaAural',      "aural");
define('UIViewMediaTv',         "tv");

interface UIViewProtocol {
    // Creating Instances
    public function init();
    
    // Settings constanst
    public function setTitle($title);
    public function getTitle();
    public function setDescription($description);
    public function getDescription();
    public function setKeywords($keywords);
    public function getKeywords();
    
    // Displaying
    public function initWithPhtmlNames($phtml);
    public function setStylesForBrowser($type, $list);
    public function setScripts();
    
    // Managing the View Hierarchy
    public function addSubview($phtml);
    
    // Observing Changes
    public function didAddSubView($arguments);
}

?>