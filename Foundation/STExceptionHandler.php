<?php
//  STExceptionHandler.php
//  Sonata/Foundation
//
// Copyright 2008-2009 Kohana Team
// Based on Kohana Exception Handler
//
// Modified to fit Sonata Framework syntax standards by Roman Efimov <romefimov@gmail.com>
//
// License: http://kohanaframework.org/license
//

class STFatalException extends ErrorException {}

class STExceptionHandler extends STObject {
    
    private static $errors = array(8, 2048);
    
    private static $isProduction = false;
    
    public static $shutdownErrors = array(E_PARSE, E_ERROR, E_USER_ERROR, E_COMPILE_ERROR);
    public static $phpErrors = array(
		E_ERROR              => 'Fatal Error',
		E_USER_ERROR         => 'User Error',
		E_PARSE              => 'Parse Error',
		E_WARNING            => 'Warning',
		E_USER_WARNING       => 'User Warning',
		E_STRICT             => 'Strict',
		E_NOTICE             => 'Notice',
		E_RECOVERABLE_ERROR  => 'Recoverable Error',
        E_DEPRECATED         => 'Deprecated'
	);
    
    public static function exceptionText(Exception $e) {
        return sprintf('%s [ %s ]: %s ~ %s [ %d ]',
                get_class($e), $e->getCode(), strip_tags($e->getMessage()), CFDebugPath($e->getFile()), $e->getLine());
    }
    
    
    public static function skipErrors($errors) {
        self::$errors = $errors;
    }
    
    public static function setProductionMode() {
        self::$isProduction = true;
    }
    
    public static function trace(array $trace = NULL) {
            if ($trace === NULL)
            {
                    // Start a new trace
                    $trace = debug_backtrace();
            }

            // Non-standard function calls
            $statements = array('include', 'include_once', 'require', 'require_once');

            $output = array();
            foreach ($trace as $step) {
                    if ( ! isset($step['function'])) {
                            // Invalid trace step
                            continue;
                    }

                    if (isset($step['file']) AND isset($step['line'])) {
                            // Include the source of this step
                            $source = STExceptionHandler::debugSource($step['file'], $step['line']);
                    }

                    if (isset($step['file']))
                    {
                            $file = $step['file'];

                            if (isset($step['line']))
                            {
                                    $line = $step['line'];
                            }
                    }

                    // function()
                    $function = $step['function'];

                    if (in_array($step['function'], $statements)) {
                            if (empty($step['args'])) {
                                    // No arguments
                                    $args = array();
                            }
                            else {
                                    // Sanitize the file path
                                    $args = array($step['args'][0]);
                            }
                    }
                    elseif (isset($step['args'])) {
                            if (strpos($step['function'], '{closure}') !== FALSE) {
                                    // Introspection on closures in a stack trace is impossible
                                    $params = NULL;
                            }
                            else {
                                    if (isset($step['class'])) {
                                            if (method_exists($step['class'], $step['function'])) {
                                                    $reflection = new ReflectionMethod($step['class'], $step['function']);
                                            }
                                            else {
                                                    $reflection = new ReflectionMethod($step['class'], '__call');
                                            }
                                    }
                                    else {
                                            $reflection = new ReflectionFunction($step['function']);
                                    }

                                    // Get the function parameters
                                    $params = $reflection->getParameters();
                            }

                            $args = array();

                            foreach ($step['args'] as $i => $arg) {
                                    if (isset($params[$i])) {
                                            // Assign the argument by the parameter name
                                            $args[$params[$i]->name] = $arg;
                                    }
                                    else {
                                            // Assign the argument by number
                                            $args[$i] = $arg;
                                    }
                            }
                    }

                    if (isset($step['class']))
                    {
                            // Class->method() or Class::method()
                            $function = $step['class'].$step['type'].$step['function'];
                    }

                    $output[] = array(
                            'function' => $function,
                            'args'     => isset($args)   ? $args : NULL,
                            'file'     => isset($file)   ? $file : NULL,
                            'line'     => isset($line)   ? $line : NULL,
                            'source'   => isset($source) ? $source : NULL,
                    );

                    unset($function, $args, $file, $line, $source);
            }

            return $output;
    }
    
	public static function debugSourcePlain($file, $line_number, $padding = 5) {
        
        if ( ! $file OR ! is_readable($file)) {
                // Continuing will cause errors
                return FALSE;
        }

        // Open the file and set the line position
        $file = fopen($file, 'r');
        $line = 0;

        // Set the reading range
        $range = array('start' => $line_number - $padding, 'end' => $line_number + $padding);

        // Set the zero-padding amount for line numbers
        $format = '% '.strlen($range['end']).'d';

        $source = '';
        while (($row = fgets($file)) !== FALSE) {
            // Increment the line number
            if (++$line > $range['end'])
                    break;

            if ($line >= $range['start']) {
                // Make the row safe for output

                if ($line === $line_number) {
                        // Apply highlighting to this row
						$row = '# '.sprintf($format, $line).' '.$row;
                        $row = "[ERROR] ".$row;
                } else {
					$row = '        # '.sprintf($format, $line).' '.$row;
				}

                // Add to the captured source
                $source .= $row;
            }
        }

        // Close the file
        fclose($file);

        return $source;
    }
	
    public static function debugSource($file, $line_number, $padding = 5) {
        
        if ( ! $file OR ! is_readable($file)) {
                // Continuing will cause errors
                return FALSE;
        }

        // Open the file and set the line position
        $file = fopen($file, 'r');
        $line = 0;

        // Set the reading range
        $range = array('start' => $line_number - $padding, 'end' => $line_number + $padding);

        // Set the zero-padding amount for line numbers
        $format = '% '.strlen($range['end']).'d';

        $source = '';
        while (($row = fgets($file)) !== FALSE) {
            // Increment the line number
            if (++$line > $range['end'])
                    break;

            if ($line >= $range['start']) {
                // Make the row safe for output
                $row = htmlspecialchars($row, ENT_NOQUOTES, 'utf-8');

                // Trim whitespace and sanitize the row
                $row = '<span class="number">'.sprintf($format, $line).'</span> '.$row;

                if ($line === $line_number) {
                        // Apply highlighting to this row
                        $row = '<span class="line highlight">'.$row.'</span>';
                }
                else {
                        $row = '<span class="line">'.$row.'</span>';
                }

                // Add to the captured source
                $source .= $row;
            }
        }

        // Close the file
        fclose($file);

        return '<pre class="source"><code>'.$source.'</code></pre>';
    }
    
    public static function exceptionHandler(Exception $e) {
		if (CFDebugErrors != 'YES') {
			if ($e->getCode() >= 400 && $e->getCode() < 600) {
				header('Content-Type: text/html; charset=utf-8', TRUE, 404);
				die(1);
			}
			if ( ! headers_sent()) {
				STNotificationCenter::postNotification("STStandardException", "standardException", array("error" => $error, "code" => $code, "file" => $file, "line" => $line));
				// Make sure the proper content type is sent with a 500 status
				header('Content-Type: text/html; charset=utf-8', TRUE, 500);
				echo '<h1>Internal Server Error</h1>';
				die(1);
			} else {
				return;
			}
		}
        try
            {
                // Get the exception information
                $type    = get_class($e);
                $code    = $e->getCode();
                $message = $e->getMessage();
                $file    = $e->getFile();
                $line    = $e->getLine();

                // Create a text version of the exception
                $error = STExceptionHandler::exceptionText($e);

                // TODO: write to log

               /* if (Kohana::$is_cli)
                {
                        // Just display the text of the exception
                        echo "\n{$error}\n";

                        return TRUE;
                }*/

                // Get the exception backtrace
                $trace = $e->getTrace();


                if ($e instanceof ErrorException) {
                    //echo 'here';
                    if (isset(STExceptionHandler::$phpErrors[$code])) {
                            // Use the human-readable error name
                            $code = STExceptionHandler::$phpErrors[$code];
                    }

                    if (version_compare(PHP_VERSION, '5.3', '<')) {
                            // Workaround for a bug in ErrorException::getTrace() that exists in
                            // all PHP 5.2 versions. @see http://bugs.php.net/bug.php?id=45895
                            for ($i = count($trace) - 1; $i > 0; --$i) {
                                    if (isset($trace[$i - 1]['args'])) {
                                            // Re-position the args
                                            $trace[$i]['args'] = $trace[$i - 1]['args'];

                                            // Remove the args
                                            unset($trace[$i - 1]['args']);
                                    }
                            }
                    }
                }
                
                if ( ! headers_sent()) {
                        // Make sure the proper content type is sent with a 500 status
                        header('Content-Type: text/html; charset=utf-8', TRUE, 500);
                }
           

                // Start an output buffer
                ob_start();
                // Unique error identifier
                $error_id = uniqid('error');
                if (!STRequest::isAjax() && !STRegistry::get("__error_output_plain")):
                ?>
                <style type="text/css">
                #sonata_error { background: #ddd; font-size: 1em; font-family:sans-serif; text-align: left; color: #111; }
                #sonata_error h1,
                #sonata_error h2 { margin: 0; padding: 1em; font-size: 1em; font-weight: normal; background: #911; color: #fff; }
                        #sonata_error h1 a,
                        #sonata_error h2 a { color: #fff; }
                #sonata_error h2 { background: #222; }
                #sonata_error h3 { margin: 0; padding: 0.4em 0 0; font-size: 1em; font-weight: normal; }
                #sonata_error p { margin: 0; padding: 0.2em 0; }
                #sonata_error a { color: #1b323b; }
                #sonata_error pre { overflow: auto; white-space: pre-wrap; }
                #sonata_error table { width: 100%; display: block; margin: 0 0 0.4em; padding: 0; border-collapse: collapse; background: #fff; }
                        #sonata_error table td { border: solid 1px #ddd; text-align: left; vertical-align: top; padding: 0.4em; }
                #sonata_error div.content { padding: 0.4em 1em 1em; overflow: hidden; }
                #sonata_error pre.source { margin: 0 0 1em; padding: 0.4em; background: #fff; border: dotted 1px #b7c680; line-height: 1.2em; }
                        #sonata_error pre.source span.line { display: block; }
                        #sonata_error pre.source span.highlight { background: #f0eb96; }
                                #sonata_error pre.source span.line span.number { color: #666; }
                #sonata_error ol.trace { display: block; margin: 0 0 0 2em; padding: 0; list-style: decimal; }
                        #sonata_error ol.trace li { margin: 0; padding: 0; }
                .js .collapsed { display: none; }
                </style>
                <script type="text/javascript">
                document.documentElement.className = 'js';
                function koggle(elem)
                {
                        elem = document.getElementById(elem);
                
                        if (elem.style && elem.style['display'])
                                // Only works with the "style" attr
                                var disp = elem.style['display'];
                        else if (elem.currentStyle)
                                // For MSIE, naturally
                                var disp = elem.currentStyle['display'];
                        else if (window.getComputedStyle)
                                // For most other browsers
                                var disp = document.defaultView.getComputedStyle(elem, null).getPropertyValue('display');
                
                        // Toggle the state of the "display" style
                        elem.style.display = disp == 'block' ? 'none' : 'block';
                        return false;
                }
                </script>
                <div id="sonata_error">
                        <h1><span class="type"><?php echo $type ?> [ <?php echo $code ?> ]:</span> <span class="message"><?php echo $message ?></span></h1>
                        <div id="<?php echo $error_id ?>" class="content">
                                <p><span class="file"><?php echo CFDebugPath($file) ?> [ <?php echo $line ?> ]</span></p>
                                <?php echo STExceptionHandler::debugSource($file, $line) ?>
                                <ol class="trace">
                                <?php foreach (STExceptionHandler::trace($trace) as $i => $step): ?>
                                        <li>
                                                <p>
                                                        <span class="file">
                                                                <?php if ($step['file']): $source_id = $error_id.'source'.$i; ?>
                                                                        <a href="#<?php echo $source_id ?>" onclick="return koggle('<?php echo $source_id ?>')"><?php echo CFDebugPath($step['file']) ?> [ <?php echo $step['line'] ?> ]</a>
                                                                <?php else: ?>
                                                                        {<?php echo __('PHP internal call') ?>}
                                                                <?php endif ?>
                                                        </span>
                                                        &raquo;
                                                        <?php echo $step['function'] ?>(<?php if ($step['args']): $args_id = $error_id.'args'.$i; ?><a href="#<?php echo $args_id ?>" onclick="return koggle('<?php echo $args_id ?>')"><?php echo __('arguments') ?></a><?php endif ?>)
                                                </p>
                                                <?php if (isset($args_id)): ?>
                                                <div id="<?php echo $args_id ?>" class="collapsed">
                                                        <table cellspacing="0">
                                                        <?php foreach ($step['args'] as $name => $arg): ?>
                                                                <tr>
                                                                        <td><code><?php echo $name ?></code></td>
                                                                        <td><pre><?php echo STExceptionHandler::dump($arg) ?></pre></td>
                                                                </tr>
                                                        <?php endforeach ?>
                                                        </table>
                                                </div>
                                                <?php endif ?>
                                                <?php if (isset($source_id)): ?>
                                                        <pre id="<?php echo $source_id ?>" class="source collapsed"><code><?php echo $step['source'] ?></code></pre>
                                                <?php endif ?>
                                        </li>
                                        <?php unset($args_id, $source_id); ?>
                                <?php endforeach ?>
                                </ol>
                        </div>
                        <h2><a href="#<?php echo $env_id = $error_id.'environment' ?>" onclick="return koggle('<?php echo $env_id ?>')"><?php echo __('Environment') ?></a></h2>
                        <div id="<?php echo $env_id ?>" class="content collapsed">
                                <?php $included = get_included_files() ?>
                                <h3><a href="#<?php echo $env_id = $error_id.'environment_included' ?>" onclick="return koggle('<?php echo $env_id ?>')"><?php echo __('Included files') ?></a> (<?php echo count($included) ?>)</h3>
                                <div id="<?php echo $env_id ?>" class="collapsed">
                                        <table cellspacing="0">
                                                <?php foreach ($included as $file): ?>
                                                <tr>
                                                        <td><code><?php echo CFDebugPath($file) ?></code></td>
                                                </tr>
                                                <?php endforeach ?>
                                        </table>
                                </div>
                                <?php $included = get_loaded_extensions() ?>
                                <h3><a href="#<?php echo $env_id = $error_id.'environment_loaded' ?>" onclick="return koggle('<?php echo $env_id ?>')"><?php echo __('Loaded extensions') ?></a> (<?php echo count($included) ?>)</h3>
                                <div id="<?php echo $env_id ?>" class="collapsed">
                                        <table cellspacing="0">
                                                <?php foreach ($included as $file): ?>
                                                <tr>
                                                        <td><code><?php echo CFDebugPath($file) ?></code></td>
                                                </tr>
                                                <?php endforeach ?>
                                        </table>
                                </div>
                                <?php foreach (array('_SESSION', '_GET', '_POST', '_FILES', '_COOKIE', '_SERVER') as $var): ?>
                                <?php if (empty($GLOBALS[$var]) OR ! is_array($GLOBALS[$var])) continue ?>
                                <h3><a href="#<?php echo $env_id = $error_id.'environment'.strtolower($var) ?>" onclick="return koggle('<?php echo $env_id ?>')">$<?php echo $var ?></a></h3>
                                <div id="<?php echo $env_id ?>" class="collapsed">
                                        <table cellspacing="0">
                                                <?php foreach ($GLOBALS[$var] as $key => $value): ?>
                                                <tr>
                                                        <td><code><?php echo $key ?></code></td>
                                                        <td><pre><?php echo STExceptionHandler::dump($value) ?></pre></td>
                                                </tr>
                                                <?php endforeach ?>
                                        </table>
                                </div>
                                <?php endforeach ?>
                        </div>
                </div>
				<?php else:
				echo "Error: ".$type ?> [ <?php echo $code ?> ]: <?php echo $message."\n\n".
				"File: ".CFDebugPath($file)." on line ".$line."\n\n------------------------------------------------------------------------------\n\n". STExceptionHandler::debugSourcePlain($file, $line) ?>
				<?php endif; ?>
                <?php
                echo ob_get_clean();

                return TRUE;
            }
            catch (Exception $e)
            {
                // Clean the output buffer if one exists
                ob_get_level() and ob_clean();

              //  // Display the exception text
                echo STExceptionHandler::exceptionText($e), "\n";

                // Exit with an error status
                die(1);
            }
    }
    
    public static function dump($value, $length = 128) {
		return STExceptionHandler::_dump($value, $length);
    }
    
    protected static function _dump( & $var, $length = 128, $level = 0) {
            if ($var === NULL) {
                    return '<small>NULL</small>';
            }
            elseif (is_bool($var)) {
                    return '<small>bool</small> '.($var ? 'TRUE' : 'FALSE');
            }
            elseif (is_float($var)) {
                    return '<small>float</small> '.$var;
            }
            elseif (is_resource($var))
            {
                    if (($type = get_resource_type($var)) === 'stream' AND $meta = stream_get_meta_data($var))
                    {
                            $meta = stream_get_meta_data($var);

                            if (isset($meta['uri']))
                            {
                                    $file = $meta['uri'];

                                    if (function_exists('stream_is_local'))
                                    {
                                            // Only exists on PHP >= 5.2.4
                                            if (stream_is_local($file))
                                            {
                                                    $file = CFDebugPath($file);
                                            }
                                    }

                                    return '<small>resource</small><span>('.$type.')</span> '.htmlspecialchars($file, ENT_NOQUOTES, 'utf-8');
                            }
                    }
                    else
                    {
                            return '<small>resource</small><span>('.$type.')</span>';
                    }
            }
            elseif (is_string($var))
            {
                    if (strlen($var) > $length)
                    {
                            // Encode the truncated string
                            $str = htmlspecialchars(substr($var, 0, $length), ENT_NOQUOTES, 'utf-8').'&nbsp;&hellip;';
                    }
                    else
                    {
                            // Encode the string
                            $str = htmlspecialchars($var, ENT_NOQUOTES, 'utf-8');
                    }

                    return '<small>string</small><span>('.strlen($var).')</span> "'.$str.'"';
            }
            elseif (is_array($var))
            {
                    $output = array();

                    // Indentation for this variable
                    $space = str_repeat($s = '    ', $level);

                    static $marker;

                    if ($marker === NULL)
                    {
                            // Make a unique marker
                            $marker = uniqid("\x00");
                    }

                    if (empty($var))
                    {
                            // Do nothing
                    }
                    elseif (isset($var[$marker]))
                    {
                            $output[] = "(\n$space$s*RECURSION*\n$space)";
                    }
                    elseif ($level < 5)
                    {
                            $output[] = "<span>(";

                            $var[$marker] = TRUE;
                            foreach ($var as $key => & $val)
                            {
                                    if ($key === $marker) continue;
                                    if ( ! is_int($key))
                                    {
                                            $key = '"'.htmlspecialchars($key, ENT_NOQUOTES, 'utf-8').'"';
                                    }

                                    $output[] = "$space$s$key => ".STExceptionHandler::_dump($val, $length, $level + 1);
                            }
                            unset($var[$marker]);

                            $output[] = "$space)</span>";
                    }
                    else
                    {
                            // Depth too great
                            $output[] = "(\n$space$s...\n$space)";
                    }

                    return '<small>array</small><span>('.count($var).')</span> '.implode("\n", $output);
            }
            elseif (is_object($var))
            {
                    // Copy the object as an array
                    $array = (array) $var;

                    $output = array();

                    // Indentation for this variable
                    $space = str_repeat($s = '    ', $level);

                    $hash = spl_object_hash($var);

                    // Objects that are being dumped
                    static $objects = array();

                    if (empty($var))
                    {
                            // Do nothing
                    }
                    elseif (isset($objects[$hash]))
                    {
                            $output[] = "{\n$space$s*RECURSION*\n$space}";
                    }
                    elseif ($level < 10)
                    {
                            $output[] = "<code>{";

                            $objects[$hash] = TRUE;
                            foreach ($array as $key => & $val)
                            {
                                    if ($key[0] === "\x00")
                                    {
                                            // Determine if the access is protected or protected
                                            $access = '<small>'.($key[1] === '*' ? 'protected' : 'private').'</small>';

                                            // Remove the access level from the variable name
                                            $key = substr($key, strrpos($key, "\x00") + 1);
                                    }
                                    else
                                    {
                                            $access = '<small>public</small>';
                                    }

                                    $output[] = "$space$s$access $key => ".STExceptionHandler::_dump($val, $length, $level + 1);
                            }
                            unset($objects[$hash]);

                            $output[] = "$space}</code>";
                    }
                    else
                    {
                            // Depth too great
                            $output[] = "{\n$space$s...\n$space}";
                    }

                    return '<small>object</small> <span>'.get_class($var).'('.count($array).')</span> '.implode("\n", $output);
            }
            else {
            
                    return '<small>'.gettype($var).'</small> '.htmlspecialchars(print_r($var, TRUE), ENT_NOQUOTES, 'utf-8');
            }
    }
    
    
    public static function errorHandler($code, $error, $file = NULL, $line = NULL) {
        if (in_array($code, self::$errors)) return TRUE;
       
        if (CFDebugErrors == 'YES') {
			STNotificationCenter::postNotification("STStandardException", "standardException", array("error" => $error, "code" => $code, "file" => $file, "line" => $line));
            STExceptionHandler::exceptionHandler(new ErrorException($error, $code, 0, $file, $line));
            die(1);
        } else {
            if ( ! headers_sent()) {
				    STNotificationCenter::postNotification("STStandardException", "standardException", array("error" => $error, "code" => $code, "file" => $file, "line" => $line));
                    // Make sure the proper content type is sent with a 500 status
                    header('Content-Type: text/html; charset=utf-8', TRUE, 500);
                    echo '<h1>Internal Server Error</h1>';
                    die(1);
            }
        }
    }
    
    public static function shutdownHandler() {
        if (CFDebugErrors == 'YES') {
            if ($error = error_get_last() AND in_array($error['type'], STExceptionHandler::$shutdownErrors)) {
				ob_get_level();
				ob_clean();
                STExceptionHandler::exceptionHandler(new STFatalException($error['message'], $error['type'], 0, $error['file'], $error['line']));
				STNotificationCenter::postNotification("STCriticalException", "criticalException", $error);
                die(1);
            }
        } else {
            if ( ! headers_sent()) {
				if ($error = error_get_last() AND in_array($error['type'], STExceptionHandler::$shutdownErrors)) {
					ob_get_level();
					ob_clean();
					STNotificationCenter::postNotification("STCriticalException", "criticalException", $error);
					// Make sure the proper content type is sent with a 500 status
					header('Content-Type: text/html; charset=utf-8', TRUE, 500);
					echo '<h1>Internal Server Error</h1>';
					die(1);
				}    
            }
        }
    }
}

if (defined("CFStandardExceptionHandler") && CFStandardExceptionHandler == 'YES') {
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
} else {
	CFErrorReporting(0, 1);
	CFErrorHandlingSet('STExceptionHandler', 'errorHandler');
	CFExceptionHandlingSet('STExceptionHandler', 'exceptionHandler');
	CFShutdownFunction('STExceptionHandler', 'shutdownHandler');
	STNotificationCenter::addObserver("STCriticalException", "STApplicationErrorHandler", "criticalException");
	STNotificationCenter::addObserver("STStandardException", "STApplicationErrorHandler", "standardException");
}

?>