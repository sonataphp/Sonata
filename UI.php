<?php
//  UI.php
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
require_once "UI/UIComponent.php";

if (!function_exists('main'))
    trigger_error("Application entry point function not found", E_USER_ERROR);

// Magic
main(isset($args)?$argc:0, isset($argv)?$argv:array());
?>