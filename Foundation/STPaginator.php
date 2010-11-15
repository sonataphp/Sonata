<?
//  STPaginator.php
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

class STPaginator {

    private static $options = array("div_id" => "",
                                    "div_class" => "paginator",
                                    "inner_wrapper" => 'ul',
                                    "items_wrapper" => "li",
                                    "prev_text" => "&lt;",
                                    "next_text" => "&gt;",
                                    "prev_no_link_text" => "",
                                    "next_no_link_text" => "",
                                    "dots" => "...",
                                    "is_ajax" => false);

    public static function configure($options = array()) {
        if ($options) 
          foreach ($options as $key => $value) {
              self::$options[$key] = $value;
          }
    }

    public static function addPagination($totalItems, $itemsPerPage, $queryPage, $appendix = '') {
        $options = self::$options;
        $cur_page = intval(STRequest::getParams()->page);
        $total_pages = ceil($totalItems / $itemsPerPage);
        if (($cur_page == '') || ($cur_page == 0))
            $cur_page = 1;
        $result = "";
        if ($total_pages > 1) {
            $pages = STPaginator::paginate($total_pages, $cur_page, 5);            
            
            if (strpos("#" . $queryPage, '?') > 0)
                $pg = "&page=";
            else
                $pg = "?page=";
                
            $link_prev_page = $queryPage . $pg . intval($cur_page - 1) . $appendix;
            $link_next_page = $queryPage . $pg . intval($cur_page + 1) . $appendix;
            $link_cur_page = $queryPage . $pg . intval($cur_page) . $appendix;
            
            if ($options['is_ajax']) {
                $link_prev_page = "javascript:;";
                $link_next_page = "javascript:;";
                $link_cur_page = "javascript:;";
                $rel_prev_page = 'rel="'.intval($cur_page - 1).'"';
                $rel_next_page = 'rel="'.intval($cur_page + 1).'"';
                $rel_cur_page = 'rel="'.intval($cur_page).'"';
            }
            
            if ($cur_page > 1)
                $result .= '    <'.$options['items_wrapper'].'><a '.$rel_prev_page.' href="' . $link_prev_page . '">'.$options['prev_text'].'</a></'.self::$options['items_wrapper'].'>'."\r\n";
            else
                $result .= '    <'.$options['items_wrapper'].' class="nolink">'.$options['prev_no_link_text'].'</'.$options['items_wrapper'].'>'."\r\n";
            foreach ($pages as $page) {
                if (isset($page['url'])) {
                    $page_url =  $queryPage . $pg . intval($page['text']) . $appendix;
                    if ($options['is_ajax']) {
                        $page_url = 'javascript:;';
                        $rel_page = 'rel="'.intval($page['text']).'"';
                    }
                    $result .= '    <'.$options['items_wrapper'].'><a '.$rel_page.' href="'.$page_url. '">' . $page['text'] . '</a></'.$options['items_wrapper'].'>'."\r\n";
                } else {
                    if ($page['text'] == $cur_page)
                        $result .= '    <'.self::$options['items_wrapper'].'><a class="selected" '.$rel_cur_page.' href="'.$link_cur_page . '">' . $page['text'] . '</a></'.$options['items_wrapper'].'>'."\r\n";
                    else
                        $result .= $page['text'];
                }
            }
        }
        if ($total_pages > 0) {
            if ($cur_page != $total_pages) {
                $result .= '    <'.$options['items_wrapper'].'><a '.$rel_next_page.' href="' . $link_next_page . '">'.$options['next_text'].'</a></'.$options['items_wrapper'].'>'."\r\n";
            } elseif ($total_pages > 1)
                $result .= '    <'.$options['items_wrapper'].' class="nolink">'.$options['next_no_link_text'].'</'.$options['items_wrapper'].'>'."\r\n";
        }
        if ($options['div_id'] != '') $div_id = 'id="'.$options['div_id'].'" ';
        if ($result != '')
            $result = '<div '.$div_id.'class="'.$options['div_class'].'">'."\r\n".'  <'.$options['inner_wrapper'].'>'."\r\n" . $result . '  </'.$options['inner_wrapper'].'>'."\r\n".'</div>'."\r\n";
        return $result;
    }
	
	
    private static function paginate ($total_pages, $current_page, $paginate_limit)  {
        $page_array = array ();
        $dotshow = true;
        for ( $i = 1; $i <= $total_pages; $i ++ ) {
            if ($i == 1 || $i == $total_pages || ($i >= $current_page - $paginate_limit && $i <= $current_page + $paginate_limit) ) {
                $dotshow = true;
                if ($i != $current_page)
                  $page_array[$i]['url'] = $i;
                $page_array[$i]['text'] = strval ($i);
            }
            else if ($dotshow == true) {
                $dotshow = false;
                $page_array[$i]['text'] = "    <".self::$options['items_wrapper'].">".self::$options['dots']."</".self::$options['items_wrapper'].">\r\n";
            }
        }
        return $page_array;
    }
}

?>