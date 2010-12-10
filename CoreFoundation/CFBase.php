<?php
//  CFBase.php
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

// Setting locale to UTF-8
setlocale(LC_ALL, 'en_US.UTF8');

// Workaround for old PHP
if (!function_exists('get_called_class')) {
    function get_called_class($bt = false,$l = 1) {
        if (!$bt) $bt = debug_backtrace();
        if (!isset($bt[$l])) throw new Exception("Cannot find called class -> stack level too deep.");
        if (!isset($bt[$l]['type'])) {
            throw new Exception ('type not set');
        }
        else switch ($bt[$l]['type']) {
            case '::':
                $lines = file($bt[$l]['file']);
                $i = 0;
                $callerLine = '';
                do {
                    $i++;
                    $callerLine = $lines[$bt[$l]['line']-$i] . $callerLine;
                } while (strpos($callerLine,$bt[$l]['function']) === false);
                preg_match('/([a-zA-Z0-9\_]+)::'.$bt[$l]['function'].'/',
                            $callerLine,
                            $matches);
                if (!isset($matches[1])) {
                    // must be an edge case.
                    throw new Exception ("Could not find caller class: originating method call is obscured.");
                }
                switch ($matches[1]) {
                    case 'self':
                    case 'parent':
                        return get_called_class($bt,$l+1);
                    default:
                        return $matches[1];
                }
                // won't get here.
            case '->': switch ($bt[$l]['function']) {
                    case '__get':
                        // edge case -> get class of calling object
                        if (!is_object($bt[$l]['object'])) throw new Exception ("Edge case fail. __get called on non object.");
                        return get_class($bt[$l]['object']);
                    default: return $bt[$l]['class'];
                }
    
            default: throw new Exception ("Unknown backtrace method type");
        }
    }
}

/*
 *  Returns framework version
 * 
 */
function CFSonataVersion() {
    return '1.0';
}

/*
 *  CoreFoundation function to append include path (OS independent).
 *
 *  @param string $path Path to append.
 */
function CFIncludePathAdd ($path) {
    foreach (func_get_args() AS $path)     {
        if (!file_exists($path) OR (file_exists($path) && filetype($path) !== 'dir')) {
            trigger_error("Include path '{$path}' does not exist", E_USER_WARNING);
            continue;
        }
       
        $paths = explode(PATH_SEPARATOR, get_include_path());
       
        if (array_search($path, $paths) === false)
            array_push($paths, $path);
       
        set_include_path(implode(PATH_SEPARATOR, $paths));
    }
}

/*
 *  CoreFoundation function to remove include path (OS independent).
 *
 *  @param string $path Path to remove.
 */
function CFIncludePathRemove ($path) {
    foreach (func_get_args() AS $path) {
        $paths = explode(PATH_SEPARATOR, get_include_path());
       
        if (($k = array_search($path, $paths)) !== false)
            unset($paths[$k]);
        else
            continue;
       
        if (!count($paths)) {
            trigger_error("Include path '{$path}' can not be removed because it is the only", E_USER_NOTICE);
            continue;
        }
       
        set_include_path(implode(PATH_SEPARATOR, $paths));
    }
}

/*
 *  CoreFoundation function to hide sensible paths from debugging.
 *
 *  @param string $file Filepath.
 */
function CFDebugPath($filepath) {
    $filepath = str_replace(CFFrameworkPath, "{FRAMEWORK}/", $filepath);
    $filepath = str_replace(CFAppPath, "{APPLICATION}/", $filepath);
    return $filepath;
}

/*
 *  CoreFoundation wrapper for spl_autoload_register.
 *
 *  @param string $class Target class.
 *  @param string $method Target method.
 */
function CFAutoLoadRegister($class, $method) {
    spl_autoload_register(array($class, $method));
}

/*
 *  CoreFoundation wrapper for spl_autoload_unregister.
 *
 *  @param string $class Target class.
 *  @param string $method Target method.
 */
function CFAutoLoadUnregister($class, $method) {
    spl_autoload_unregister(array($class, $method));
}

/*
 *  CoreFoundation wrapper for set_error_handler.
 *
 *  @param string $class Target class.
 *  @param string $method Target method.
 */
function CFErrorHandlingSet($class, $method) {
    set_error_handler(array($class, $method));
}

/*
 *  CoreFoundation wrapper for restore_error_handler.
 * 
 */
function CFErrorHandlingRestore() {
    restore_error_handler();
}

/*
 *  CoreFoundation wrapper for set_exception_handler.
 *
 *  @param string $class Target class.
 *  @param string $method Target method.
 */
function CFExceptionHandlingSet($class, $method) {
    set_exception_handler(array($class, $method));
}

/*
 *  CoreFoundation wrapper for restore_exception_handler.
 *  
 */
function CFExceptionHandlingRestore() {
    restore_exception_handler();
}

/*
 *  CoreFoundation wrapper for register_shutdown_function.
 *
 *  @param string $class Target class.
 *  @param string $method Target method.
 */
function CFShutdownFunction($class, $method) {
    register_shutdown_function(array($class, $method));
}

/*
 *  CoreFoundation function CFErrorReporting sets error_reporting and php.ini
 *  display_errors parameters.
 *
 *  @param integer $errorReporting Error reporting level.
 *  @param int $displayErrors Display errors (1 or 0).
 */
function CFErrorReporting($errorReporting, $displayErrors) {
    error_reporting($errorReporting);
    ini_set('display_errors', $displayErrors);
}

// ====================== Memory functions ===================

/*
 *  CoreFoundation wrapper for memory_get_usage.
 *
 *  @return int Returns the amount of memory, in bytes, that's currently being allocated to PHP script.
 */
function CFMemoryRealUsage() {
    return memory_get_usage(true);
}

/*
 *  CoreFoundation wrapper for memory_get_peak_usage.
 *
 *  @return int Returns the peak of memory, in bytes, that's been allocated to PHP script.
 */
function CFMemoryPeakUsage() {
    return memory_get_peak_usage(true);
}

/*
 *  CoreFoundation function to get current memory limit.
 *  
 *  @return string Returns the memory limit set in php.ini.
 */
function CFMemoryLimit() {
    return ini_get("memory_limit");
}

?>