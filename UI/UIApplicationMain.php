<?
//
//  UIApplicationMain.php
//  Sonata/UI
//
//  Created by Roman Efimov on 6/10/2010.
//

function UIApplicationMain($argc = 0, $argv = array(), $app) {
    STRequest::init();
    STServer::init();
    STSession::create();
    STFileCache::configure(CFAppPath."Cache/");
    $applicationDelegate = strval($app);
    $applicationDelegate::sharedApplication()->arguments = $argv;
    $applicationDelegate::sharedApplication()->applicationWillRun();
}

?>