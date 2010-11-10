<?
//
//  CFBase.php
//  Sonata/CoreFoundation
//
//  Created by Roman Efimov on 6/10/2010.
//
setlocale(LC_ALL, 'en_US.UTF8');

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

function CFDebugPath($file) {
    $file = str_replace(CFFrameworkPath, "{FRAMEWORK}/", $file);
    $file = str_replace(CFAppPath, "{APPLICATION}/", $file);
    return $file;
}

function CFAutoLoadRegister($class, $method) {
    spl_autoload_register(array($class, $method));
}

function CFAutoLoadUnregister($class, $method) {
    spl_autoload_unregister(array($class, $method));
}

function CFErrorHandlingRestore() {
    restore_error_handler();
}

function CFExceptionHandlingRestore() {
    restore_exception_handler();
}

function CFExceptionHandlingSet($class, $method) {
    set_exception_handler(array($class, $method));
}

function CFErrorHandlingSet($class, $method) {
    set_error_handler(array($class, $method));
}

function CFShutdownFunction($class, $method) {
    register_shutdown_function(array($class, $method));
}

function CFErrorReporting($errorReporting, $displayErrors) {
    error_reporting($errorReporting);
    ini_set('display_errors', $displayErrors);
}

// Memory functions

function CFMemoryRealUsage() {
    return memory_get_usage(true);
}

function CFMemoryPeakUsage() {
    return memory_get_peak_usage(true);
}

function CFMemoryLimit() {
    return ini_get("memory_limit");
}

?>