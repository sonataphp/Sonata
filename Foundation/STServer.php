<?
//  STServer.php
//  Sonata/Foundation
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

include "Headers/STServerInfoRecord.php";

class STServer extends STObject {
    
    final private function __construct() {}
    final private function __clone() {}
    
    private static $_record;
    
    public static function uriArguments($argumentId = 0, $tolower = true) {
        $params=explode('?', $_SERVER['REQUEST_URI']);
        $params=$params[0];
        $arguments=explode('/', $params);
        $arguments=array_remove_empty($arguments);
        if ($argumentId == '') return $arguments;
        return ($tolower)?strtolower($arguments[$argumentId]):$arguments[$argumentId];
    }
    
    public static function init() {
        if (!isset($_SERVER)) return null;
        $record = new STServerInfoRecord();
        $record->phpSelf = $_SERVER['PHP_SELF'];
        $record->argv = $_SERVER['argv'];
        $record->argc = $_SERVER['argc'];
        $record->gatewayInterface = $_SERVER['GATEWAY_INTERFACE'];
        $record->serverAddr = $_SERVER['SERVER_ADDR'];
        $record->serverName = $_SERVER['SERVER_NAME'];
        $record->serverSoftware = $_SERVER['SERVER_SOFTWARE'];
        $record->serverProtocol = $_SERVER['SERVER_PROTOCOL'];
        $record->requestMethod = $_SERVER['REQUEST_METHOD'];
        $record->requestTime = $_SERVER['REQUEST_TIME'];
        $record->queryString = $_SERVER['QUERY_STRING'];
        $record->documentRoot = $_SERVER['DOCUMENT_ROOT'];
        $record->httpAccept = $_SERVER['HTTP_ACCEPT'];
        $record->httpAcceptCharset = $_SERVER['HTTP_ACCEPT_CHARSET'];
        $record->httpAcceptEncoding = $_SERVER['HTTP_ACCEPT_ENCODING'];
        $record->httpAcceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $record->httpConnection = $_SERVER['HTTP_CONNECTION'];
        $record->httpHost = $_SERVER['HTTP_HOST'];
        $record->httpReferer = $_SERVER['HTTP_REFERER'];
        $record->httpUserAgent = $_SERVER['HTTP_USER_AGENT'];
        $record->https = $_SERVER['HTTPS'];
        $record->remoteAddr = $_SERVER['REMOTE_ADDR'];
        $record->remoteHost = $_SERVER['REMOTE_HOST'];
        $record->remotePort = $_SERVER['REMOTE_PORT'];
        $record->scriptFilename = $_SERVER['SCRIPT_FILENAME'];
        $record->serverAdmin = $_SERVER['SERVER_ADMIN'];
        $record->serverPort = $_SERVER['SERVER_PORT'];
        $record->serverSignature = $_SERVER['SERVER_SIGNATURE'];
        $record->pathTranslated = $_SERVER['PATH_TRANSLATED'];
        $record->scriptName = $_SERVER['SCRIPT_NAME'];
        $record->requestUri = $_SERVER['REQUEST_URI'];
        $record->phpAuthDigest = $_SERVER['PHP_AUTH_DIGEST'];
        $record->phpAuthUser = $_SERVER['PHP_AUTH_USER'];
        $record->phpAuthPw = $_SERVER['PHP_AUTH_PW'];
        $record->authType = $_SERVER['AUTH_TYPE'];
        $record->pathInfo = $_SERVER['PATH_INFO'];
        $record->origPathInfo = $_SERVER['ORIG_PATH_INFO'];
        $record->uniqueId = $_SERVER['UNIQUE_ID'];
        $record->httpCookie = $_SERVER['HTTP_COOKIE'];
        $record->contentLength = $_SERVER['CONTENT_LENGTH'];
        foreach ($_SERVER as $item => $value) {
            if (strpos($item, "HTTP_X") > -1) {
                $s = str_replace("HTTP_X_", "", $item);
                $record->httpX->$s = $value;
            }
        }
        self::$_record = $record;
    }
    
    public static function info() {
        return self::$_record;
    }

}

?>