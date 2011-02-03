<?php
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
    
    private static function serverParam($param) {
        return isset($_SERVER[$param])?$_SERVER[$param]:"";
    }
    
    public static function subdomain() {
        $domain = str_replace(".", "\\.", STRegistry::get("Base_Domain"));
        $subdomain = preg_replace('/^(?:([^\.]+)\.)?'.$domain.'$/', '\1', $_SERVER['SERVER_NAME']);
        return $subdomain;
    }
    
    public static function init() {
        if (!isset($_SERVER)) return null;
        $record = new STServerInfoRecord();
        $record->phpSelf = self::serverParam('PHP_SELF');
        $record->argv = self::serverParam('argv');
        $record->argc = self::serverParam('argc');
        $record->gatewayInterface = self::serverParam('GATEWAY_INTERFACE');
        $record->serverAddr = self::serverParam('SERVER_ADDR');
        $record->serverName = self::serverParam('SERVER_NAME');
        $record->serverSoftware = self::serverParam('SERVER_SOFTWARE');
        $record->serverProtocol = self::serverParam('SERVER_PROTOCOL');
        $record->requestMethod = self::serverParam('REQUEST_METHOD');
        $record->requestTime = self::serverParam('REQUEST_TIME');
        $record->queryString = self::serverParam('QUERY_STRING');
        $record->documentRoot = self::serverParam('DOCUMENT_ROOT');
        $record->httpAccept = self::serverParam('HTTP_ACCEPT');
        $record->httpAcceptCharset = self::serverParam('HTTP_ACCEPT_CHARSET');
        $record->httpAcceptEncoding = self::serverParam('HTTP_ACCEPT_ENCODING');
        $record->httpAcceptLanguage = self::serverParam('HTTP_ACCEPT_LANGUAGE');
        $record->httpConnection = self::serverParam('HTTP_CONNECTION');
        $record->httpHost = self::serverParam('HTTP_HOST');
        $record->httpReferer = self::serverParam('HTTP_REFERER');
        $record->httpUserAgent = self::serverParam('HTTP_USER_AGENT');
        $record->https = self::serverParam('HTTPS');
        $record->remoteAddr = self::serverParam('REMOTE_ADDR');
        $record->remoteHost = self::serverParam('REMOTE_HOST');
        $record->remotePort = self::serverParam('REMOTE_PORT');
        $record->scriptFilename = self::serverParam('SCRIPT_FILENAME');
        $record->serverAdmin = self::serverParam('SERVER_ADMIN');
        $record->serverPort = self::serverParam('SERVER_PORT');
        $record->serverSignature = self::serverParam('SERVER_SIGNATURE');
        $record->pathTranslated = self::serverParam('PATH_TRANSLATED');
        $record->scriptName = self::serverParam('SCRIPT_NAME');
        $record->requestUri = self::serverParam('REQUEST_URI');
        $record->phpAuthDigest = self::serverParam('PHP_AUTH_DIGEST');
        $record->phpAuthUser = self::serverParam('PHP_AUTH_USER');
        $record->phpAuthPw = self::serverParam('PHP_AUTH_PW');
        $record->authType = self::serverParam('AUTH_TYPE');
        $record->pathInfo = self::serverParam('PATH_INFO');
        $record->origPathInfo = self::serverParam('ORIG_PATH_INFO');
        $record->uniqueId = self::serverParam('UNIQUE_ID');
        $record->httpCookie = self::serverParam('HTTP_COOKIE');
        $record->contentLength = self::serverParam('CONTENT_LENGTH');
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