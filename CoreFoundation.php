<?
//
//  CoreFoundation.php
//  Sonata/CoreFoundation
//
//  Created by Roman Efimov on 6/10/2010.
//  Copyright Roman Efimov 2010. All rights reserved.
//

define('CFFrameworkPath', dirname(__FILE__).DIRECTORY_SEPARATOR);
set_include_path( get_include_path().PATH_SEPARATOR.CFFrameworkPath);

require_once "CoreFoundation/CFBase.php";

CFIncludePathAdd(CFAppPath."Application/");
CFIncludePathAdd(CFAppPath."Classes/");
CFIncludePathAdd(CFAppPath."Classes/Controllers/");
CFIncludePathAdd(CFAppPath."Classes/Views/");
CFIncludePathAdd(CFAppPath."Classes/Models/");
CFIncludePathAdd(CFAppPath."Classes/Helpers/");
CFIncludePathAdd(CFAppPath."Library/");

require_once "CoreFoundation/CFRange.php";
require_once "CoreFoundation/CFCommonFunctions.php";
require_once "CoreFoundation/CFDate.php";
require_once "CoreFoundation/CFArray.php";
require_once "CoreFoundation/CFTimezoneTable.php";
require_once "CoreFoundation/CFMime.php";
?>