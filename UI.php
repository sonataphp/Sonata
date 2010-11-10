<?
//
//  UI.php
//  Sonata/UI
//
//  Created by Roman Efimov on 6/10/2010.
//  Copyright Roman Efimov 2010. All rights reserved.
//

require_once "Foundation.php";
require_once "UI/UIApplicationError.php";
require_once "UI/UIApplicationMain.php";
require_once "UI/UIApplication.php";
require_once "UI/UIViewController.php";
require_once "UI/UIView.php";
require_once "UI/UIViewCache.php";
require_once "UI/UIAjaxController.php";
require_once "UI/UIMinifierViewController.php";
require_once "UI/UIFlashMessage.php";

if (!function_exists('main'))
    trigger_error("Application entry point function not found", E_USER_ERROR);
    
main($argc, $argv);
?>