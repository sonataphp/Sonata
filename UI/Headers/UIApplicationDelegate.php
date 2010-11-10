<?php

interface UIApplicationDelegate {
    
    public function applicationRun();
    public function applicationTerminate();
    
    public function applicationDidFinishLaunching();
    public function applicationWillTerminate();
    public function applicationWillRun();
    
    public function settingsFromObject($data);
    
    public static function handleRoutesWithRouter($router);
}

?>