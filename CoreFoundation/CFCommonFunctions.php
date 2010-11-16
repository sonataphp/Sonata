<?
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
 * UTF-8 htmlentities
 *
 * @param string $s input html
 * @return string output html string
 */
function html($s) {
    return htmlentities($s, ENT_QUOTES, 'UTF-8');
}

/*
 * UTF-8 htmlentities
 * Sonata syntax standards compliant alias for html function
 *
 * @param string $s input html
 * @return string output html string
 */
function CFHtml($s) {
    return html($s);
}

/*
 * UTF-8 htmlentities, mostly used for post cleanups
 *
 * @param string $val input html
 * @return string output html string
 */
function CFHtmlPost($s) {
    if (is_array($s)) {
	    return $s;
	} else {
	   return htmlentities($s, ENT_QUOTES, 'UTF-8');
	}
}

/*
 * Truncates string
 *
 * @param string $str input string
 * @param integer $max maximum number of characters
 * @param string $after string appendix
 * @return string truncated string
 */
function CFTruncate($str, $max, $after='...') {
    $len = strlen($str);
    return $len > $max+strlen($after) ? substr($str, 0, $max).$after : $str;
}

/*
 * Closes html tags
 *
 * @param string $html input html
 * @return string output html
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
 * Generates password
 *
 * @param integer $length password length
 * @param integer $strength password strength
 * @return string generated password
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
 * Tries to restore string from alias
 *
 * @param string $s input string
 * @param string $symbol symbol to replace "-"
 * @param bool $capitalize need to capitalize words?
 * @return string output string
 */
function CFRestoreTitleFromSlug($s, $symbol = ' ', $capitalize = true) {
    $s=str_replace("-", " ", $s);
    if ($capitalize) $s=ucwords($s);
    $s=str_replace(" ", $symbol, $s);
    return $s;
}
 
/*
 * Gets slug from string (i.e. post title)
 *
 * @param string $str input string
 * @param array $replace replacement values
 * @param string $delimiter delimiter
 * @return string slug
 */  
function CFGetSlug($str, $replace=array(), $delimiter='-') {
    if( !empty($replace) ) {
        $str = str_replace((array)$replace, ' ', $str);
    }

    $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
    $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
    $clean = strtolower(trim($clean, '-'));
    $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

    return $clean;
}

/*
 * Creates page limit for convenient SQL usage,
 * doesn't include LIMIT word.
 *
 * @param int $limit maximum items to show
 * @param int $page page number
 * @return string limit separated with comma
 */
function CFPageLimit($limit, $page) {
    $page=$page-1;
    if ($page < 0) $page=0;
    $page=$page*$limit;
    $page=$page.", ".$limit;
    
    return $page;
}

/*
 * Creates page limit for convenient SQL usage,
 * includes LIMIT word
 *
 * @param int $limit maximum items to show
 * @param int $page page number
 * @return string limit separated with comma
 */
function CFGetPageLimit($limit, $page) {        
    $page=$page-1;
    if ($page < 0) $page=0;
    $page=$page*$limit;
    $page=" LIMIT ".$page.", ".$limit;
    
    return $page;
}

/*
 * Gets file extension from filename
 *
 * @param string $fileName file name
 * @return string extension
 */
function CFFileExtension($fileName) {
    return substr($fileName, strrpos($fileName, '.') + 1);
}

/*
 * Gets file extension from filename using fileinfo
 *
 * @param string $fileName file name
 * @return string extension
 */
function CFFileExtensionReal($fileName) {
    $info = pathinfo($fileName);
    return $info['extenstion'];
}

?>