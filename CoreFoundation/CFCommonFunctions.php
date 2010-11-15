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
function truncate($str, $max, $after='...') {
    $len = strlen($str);
    return $len > $max+strlen($after) ? substr($str, 0, $max).$after : $str;
}

/*
 * Closes html tags
 *
 * @param string $html input html
 * @return string output html
 */
function closeTags($html) {
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
function generatePassword($length = 9, $strength = 0) {
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
 * Gets alias from string (i.e. post title)
 *
 * @param string $s input string
 * @param bool $replace_slash need to replace slash?
 * @return string alias
 */
function getAlias($s, $replace_slash = false) {
    $s=str_replace("'", "-", $s);
    $s=str_replace(",", "-", $s);
    $s=str_replace("&amp;", "-", $s);
    $s=str_replace("&", "-", $s);
    $s=str_replace(".", "", $s);
    $s=str_replace("\"", "-", $s);
    if ($replace_slash) $s=str_replace("/", "-", $s);
    $s=str_replace(" ", "-", $s);
    $s=str_replace("---", "-", $s);
    $s=str_replace("--", "-", $s);
    $s=preg_replace('/[^(\x20-\x7F)]*/','', $s);
    $s=strtolower($s);
    return $s;
}

/*
 * Tries to restore string from alias
 *
 * @param string $s input string
 * @param string $symbol symbol to replace "-"
 * @param bool $capitalize need to capitalize words?
 * @return string output string
 */
function restoreAlias($s, $symbol = ' ', $capitalize = true) {
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
function getSlug($str, $replace=array(), $delimiter='-') {
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
 * Splits array
 *
 * @param array $array input array
 * @return array splitted array
 */
function array_split($array) {           
    $end=count($array);
    $half = ($end % 2 )?  ceil($end/2): $end/2;
    return array(array_slice($array,0,$half),array_slice($array,$half));
}

/*
 * Remove empty values from array
 *
 * @param array $array input array
 * @return array updated array
 */
function array_remove_empty($arr){
	$narr = array();
	while(list($key, $val) = each($arr)) {
	    if (is_array($val)){
            $val = array_remove_empty($val);
            if (count($val)!=0) {
                 $narr[$key] = $val;
            }
	    } else {
            if (trim($val) != ""){
               $narr[$key] = $val;
            }
	    }
	}
	unset($arr); 
	return $narr; 
}

?>