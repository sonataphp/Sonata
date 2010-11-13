<?
//
//  CFCommonFunctions.php
//  Sonata/CoreFoundation
//
//  Created by Roman Efimov on 6/10/2010.
//

function html($s) {
    return htmlentities($s, ENT_QUOTES, 'UTF-8');
}

function CFHtmlPost($val) {
    if (is_array($val)) {
	    return $val;
	} else {
	   return htmlentities($val, ENT_QUOTES, 'UTF-8');
	}
}

function truncate($str, $max, $after='...') {
    $len = strlen($str);
    return $len > $max+strlen($after) ? substr($str, 0, $max).$after : $str;
}

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

function restoreAlias($s, $symbol = ' ', $capitalize = true) {
    $s=str_replace("-", " ", $s);
    if ($capitalize) $s=ucwords($s);
    $s=str_replace(" ", $symbol, $s);
    return $s;
}
   
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


function CFPageLimit($limit, $page) {
    $page=$page-1;
    if ($page < 0) $page=0;
    $page=$page*$limit;
    $page=$page.", ".$limit;
    
    return $page;
}

function CFGetPageLimit($limit, $page) {        
    $page=$page-1;
    if ($page < 0) $page=0;
    $page=$page*$limit;
    $page=" LIMIT ".$page.", ".$limit;
    
    return $page;
}

function CFFileExtension($fileName) {
    return substr($fileName, strrpos($fileName, '.') + 1);
}

function array_split($array) {           
    $end=count($array);
    $half = ($end % 2 )?  ceil($end/2): $end/2;
    return array(array_slice($array,0,$half),array_slice($array,$half));
}

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