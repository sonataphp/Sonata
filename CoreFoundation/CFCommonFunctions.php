<?php
//  CFCommonFunctions.php
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

/*
 *  UTF-8 htmlentities.
 *
 *  @param string $inputString Input string.
 *  @return string String parsed with htmlentities.
 */
function html($inputString) {
    return htmlentities($inputString, ENT_QUOTES, 'UTF-8');
}

/*
 *  UTF-8 htmlentities.
 *  Sonata syntax standards compliant alias for html function.
 *
 *  @param string $inputString Input string.
 *  @return string String parsed with htmlentities.
 */
function CFHtml($inputString) {
    return html($inputString);
}

/*
 *  UTF-8 htmlentities, used mostly for post cleanups.
 *
 *  @param string $inputString Input string.
 *  @return string String parsed with htmlentities.
 */
function CFHtmlPost($input) {
    if (is_array($input)) {
	    return $input;
	} else {
	   return htmlentities($input, ENT_QUOTES, 'UTF-8');
	}
}

/*
 *  Truncates string.
 *
 *  @param string $str Input string.
 *  @param integer $max Maximum number of characters.
 *  @param string $after String appendix.
 *  @return string Truncated string.
 */
function CFTruncate($str, $max, $after = '...') {
    $len = strlen($str);
    return $len > $max+strlen($after) ? substr($str, 0, $max).$after : $str;
}

/*
 *  Closes open HTML tags.
 *
 *  @param string $html Input HTML string.
 *  @return string Output HTML string.
 */
function CFCloseTags($html) {
    $arr_single_tags = array('meta','img','br','link','area');
    preg_match_all('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
    $openedtags = $result[1];
    preg_match_all('#</([a-z]+)>#iU', $html, $result);
    $closedtags = $result[1];
    $len_opened = count($openedtags);
    
    if (count($closedtags) == $len_opened)
        return $html;
    
    $openedtags = array_reverse($openedtags);
    
    for ($i=0; $i < $len_opened; $i++) {
        if (!in_array($openedtags[$i],$arr_single_tags)) {
            if (!in_array($openedtags[$i], $closedtags)) {
                if ($next_tag = $openedtags[$i+1]) {
                    $html = preg_replace('#</'.$next_tag.'#iU','</'.$openedtags[$i].'></'.$next_tag,$html);
                } else {
                    $html .= '</'.$openedtags[$i].'>';
                }
            }
        }
    }
    return $html;
}

/*
 *  Generates a password.
 *
 *  @param integer $length Password length.
 *  @param integer $strength Password strength.
 *  @return string Password string.
 */
function CFGeneratePassword($length = 9, $strength = 0) {
    $vowels = 'aeuy';
    $consonants = 'bdghjmnpqrstvz';
    if ($strength & 1) {
        $consonants .= 'BDGHJLMNPQRSTVWXZ';
    }
    if ($strength & 2) {
        $vowels .= "AEUY";
    }
    if ($strength & 4) {
        $consonants .= '23456789';
    }
    if ($strength & 8) {
        $consonants .= '@#$%';
    }
    
    $password = '';
    $alt = time() % 2;
    for ($i = 0; $i < $length; $i++) {
        if ($alt == 1) {
            $password .= $consonants[(rand() % strlen($consonants))];
            $alt = 0;
        } else {
            $password .= $vowels[(rand() % strlen($vowels))];
            $alt = 1;
        }
    }
    return $password;
}

/*
 *  Gets slug from string (i.e. post title).
 *
 *  @param string $str Input string.
 *  @param array $replace Array of characters to replace with spaces.
 *  @param string $delimiter Replace unappropriate symbols with delimiter.
 *  @param bool Skip forward slash (/) when parsing string.
 *  @return string Slug string.
 */  
function CFGetSlug($str, $replace=array(), $delimiter='-', $skipSlashes = true) {
    if( !empty($replace) ) {
        $str = str_replace((array)$replace, ' ', $str);
    }

    $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
    $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
    $clean = strtolower(trim($clean, '-'));
    $clean = preg_replace(($skipSlashes)?"/[_|+ -]+/":"/[\/_|+ -]+/", $delimiter, $clean);

    return $clean;
}

/*
 *  Tries to restore string from alias.
 *
 *  @param string $s Input string.
 *  @param string $symbol Symbol to replace "-".
 *  @param bool $capitalize Need to capitalize words?
 *  @return string Output string.
 */
function CFRestoreTitleFromSlug($s, $symbol = ' ', $capitalize = true) {
    $s=str_replace("-", " ", $s);
    if ($capitalize) $s=ucwords($s);
    $s=str_replace(" ", $symbol, $s);
    return $s;
}

/*
 *  Creates page limit for convenient SQL usage,
 *  doesn't include LIMIT word.
 *
 *  @param int $limit Maximum items to show.
 *  @param int $page Page number.
 *  @return string String with format "X, Y", where X is offset and Y is items count.
 */
function CFPageLimit($limit, $page) {
    $page=$page-1;
    if ($page < 0) $page=0;
    $page=$page*$limit;
    $page=$page.", ".$limit;
    
    return $page;
}

/*
 *  Creates page limit for convenient SQL usage,
 *  includes LIMIT word.
 *
 *  @param int $limit Maximum items to show.
 *  @param int $page Page number.
 *  @return string String with format "LIMIT X, Y", where X is offset and Y is items count.
 */
function CFGetPageLimit($limit, $page) {        
    $page=$page-1;
    if ($page < 0) $page=0;
    $page=$page*$limit;
    $page=" LIMIT ".$page.", ".$limit;
    
    return $page;
}

/*
 *  Gets file extension from filename by stripping last word before comma.
 *
 *  @param string $fileName Input filename.
 *  @return string File extension.
 */
function CFFileExtension($fileName) {
    return substr($fileName, strrpos($fileName, '.') + 1);
}

/*
 *  Gets real file extension, works with .tar.gz.
 *
 *  @param string $fileName Input filename.
 *  @return string File extension.
 */
function CFFileExtensionReal($fileName) {
    $info = pathinfo($fileName);
    return $info['extenstion'];
}

?>