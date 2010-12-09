<?php
//  CoreFoundation.php
//  Sonata/CoreFoundation
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