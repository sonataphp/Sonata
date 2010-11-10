<?php

class UIFlashMessage {
        static function set($msg, $type = NOTICE) {
              //  if ($type == NOTICE) self::clear(ERROR);
                $_SESSION['flash'.$type] = $msg;
        }
        
        static function clear($type) {
                unset($_SESSION['flash'.$type]);
        }
        
        static function get($type, $before = '', $after = '') {
                if ($_SESSION['flash'.$type]) {
                        $msg = $_SESSION['flash'.$type];
                        unset($_SESSION['flash'.$type]);
                        return $before.$msg.$after;
                }
        }
}

?>