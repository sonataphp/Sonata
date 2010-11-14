<?php

define("where", "__WHERE");
define("order", "__ORDER");
define("limit", "__LIMIT");

class ORQueryHelper {
    
    private static $params;
    private static $increment;
    
    public static function escape($v) {
        return DB::escape($v);
    }
    
    public static function setupReplace($params) {
        self::$increment = 0;
        self::$params = $params;
    }
    
    public static function replaceParam($s) {
        $val = self::$params[self::$increment];
        $result = ($val == intval($val))?$val:"'$val'";
        if ($result == '') $result = 0;
        self::$increment++;
        return $result;
    }
    
}


abstract class ORModel implements Countable, Iterator {
    
    protected $_tableName;
    protected $_primaryKey;
    private $vars;
    private $lastMethod;
    protected $data;
    private $position = 0;
    private $awaitingCall = false;
    private $__delegate;
    private $__query;
    private $__key;
    private $__foreignKey;
    private $__onduplicate;
    private $__leftJoin;
    private $_filtersEnabled = true;
    
    public function __construct() {
        $this->position = 0;
    }

    public function primaryKey() {
        return ($this->_primaryKey)?$this->_primaryKey:"id";
    }

    
    public function tableName() {
        return ($this->_tableName)?$this->_tableName:strtolower(str_replace("Model", "", get_class($this)));
    }
    
    protected function postProcess($fieldName, $value) {
        return $value;
    }
    
    private function onCall($delegate, $query) {
        $this->awaitingCall = true;
        $this->__delegate = $delegate;
        $this->__query = $query;
        return $this;
    }
    
    public function multipleMatch($delegate, $key, $foreignKey, $additionalQuery = '') {
        $this->onCall($delegate, "WHERE `$foreignKey` IN ({".$key."}) ".$additionalQuery);
        $this->__key = $key;
        $this->__foreignKey = $foreignKey;
        return $this;
    }
    
    public function singleMatch($args) {
        $args = func_get_args();
        $this->__leftJoin = $args;
    }
    
    public function onDuplicate($array) {
        $this->__onduplicate = $array;
    }
    
    public function enableFilters() {
        $this->_filtersEnabled = true;
    }
    
    public function disableFilters() {
        $this->_filtersEnabled = false;
    }
    
    private function copyVariables($object) {
        if (!$vars = get_object_vars($this)) return $this;
        foreach ($vars as $key => $val) {
            $this->$key = $object->$key;
        }
        return $this;
    }
    
    /*private function describe() {
        $this->vars = $this->describeModel($this);
    }*/
    
    private function describeModel($model) {
        if (!$vars = get_object_vars($model)) return;
        $_vars = array();
        foreach ($vars as $key => $val) {
            if ($key == '_settings') continue;
            /* Get a reflection object for the object instance var */
            try {
                $reflect = new ReflectionProperty($model, $key);
                if (!is_object($model->$key))
                if($reflect->isPublic()) {
                    $_vars[] = $model->tableName().'.`'.$key.'` as '.$model->tableName()."___".$key;
                }
            } catch (Exception $e) { }
        }
        return $_vars;
    }
    
    private function findAttributeByObject($object) {
        if (!$vars = get_object_vars($this)) return;
        $class = get_class($object);
        foreach ($vars as $key => $val) {
            if ($key == '_settings') continue;
            /* Get a reflection object for the object instance var */
            try {
                $reflect = new ReflectionProperty($this, $key);
                if (is_object($this->$key))
                if($reflect->isPublic()) {
                    if ($this->$key instanceof $class) {
                        return $key;
                    }
                }
            } catch (Exception $e) { }
        }
    }
    
    private function prepareQueryParams($params) {
        $params = ((count($params) == 1) && (is_array($params[0])))?$params[0]:$params;
        $q = array_shift($params);
        $params = array_map('ORQueryHelper::escape', $params);
        ORQueryHelper::setupReplace($params);
        $q = preg_replace_callback("/\?/", "ORQueryHelper::replaceParam", $q);
        return $q;
    }
    
    public function first() {
        $args = func_get_args();
        if (count($args) == 1){
            $this->all("WHERE ".$this->tableName().".".$this->primaryKey()." = '?' LIMIT 1", $args[0]);
            return $this->copyVariables($this->data[0]);
        }
        $this->all($args);
        return $this->copyVariables($this->data[0]);
    }
    
    protected function processResult($result) {
        foreach ($result as $row) {
            $var = new $this();
            
            foreach ($row as $key => $value) {
                $k = str_replace($this->tableName()."___", "", $key);
                if (property_exists($this, $k)) {
                    $var->$k = ($this->_filtersEnabled)?$this->postProcess($k, $value):$value;
                } else {
                    if ($this->__leftJoin) {
                        foreach ($this->__leftJoin as $join) {
                            if (strpos($k, $join[0]->tableName()) !== FALSE) {
                                $k = str_replace($join[0]->tableName()."___", "", $k);
                                $param = $this->findAttributeByObject($join[0]);
                                if ($param) {
                                    $var->$param->$k = ($this->_filtersEnabled)?$this->postProcess($k, $value):$value;
                                }
                            }
                        }
                    }
                }
            }
            $this->data[] = $var;
        }
    }
    
    public function all() {
        $this->vars = $this->describeModel($this);
        $this->data = array();
        
        $q = $this->prepareQueryParams(func_get_args());
        
        $joins = '';
        if ($this->__leftJoin) {
            foreach ($this->__leftJoin as $joinParam) {
                $this->vars = array_merge($this->vars, $this->describeModel($joinParam[0]));
                $joins.= "\r\nLEFT JOIN ".$joinParam[0]->tableName().
                         "    ON (".$joinParam[0]->tableName().".`".$joinParam[2]."` = ".$this->tableName().".`".$joinParam[1]."`".")";
            }
        }
        
        $query = "SELECT ".implode(", ", $this->vars)." FROM ".$this->tableName()." ".
                 $joins.
                 " ".$q;    
        $result = DB::all($query);

        if (!$result) return $this;
        $this->processResult($result);
        return $this;
    }
    
    public function custom() {
        $this->vars = $this->describeModel($this);
        $this->data = array();
        
        $q = $this->prepareQueryParams(func_get_args());
        $result = DB::all($q);

        if (!$result) return $this;
        $this->processResult($result);
        return $this;
    }
    
    public function count() {
        $this->rewind();
        return count($this->data);
    }
    
    public function update() {
        if (!$vars = get_object_vars($this)) return $this;
        $data = array();
        foreach ($vars as $key => $val) {
            try {
                $reflect = new ReflectionProperty($this, $key);
                if (!is_object($this->$key))
                if($reflect->isPublic()) {
                    if ($this->$key) {
                        $data[$key] = $val;
                    }
                }
            } catch (Exception $e) { }
        }
        $k = $this->primaryKey();
        DB::update($this->tableName(), $data, "`".$this->primaryKey()."` = ".$this->$k);
        return $this;
    }
    
    public function insert() {
        if (!$vars = get_object_vars($this)) return $this;
        $data = array();
        foreach ($vars as $key => $val) {
            try {
                $reflect = new ReflectionProperty($this, $key);
                if (!is_object($this->$key))
                if($reflect->isPublic()) {
                    if ($this->$key) {
                        $data[$key] = $val;
                    }
                }
            } catch (Exception $e) { }
        }
        if (is_array($this->__onduplicate) || $this->__onduplicate == "__IGNORE")
            DB::insertOnDuplicate($this->tableName(), $data, $this->__onduplicate); else
        DB::insert($this->tableName(), $data);
        return $this;
    }
    
    public function rewind() {
        if ($this->awaitingCall) {
            $keys = array();
            $foreign = $this->__foreignKey;
            $key = $this->__key;
            if (!strpos(strtoupper($this->__query), "LIMIT")) {
                foreach ($this->__delegate as $model) {
                    $keys[] = ORQueryHelper::escape($model->$key);
                }
                $keys = implode(", ", $keys);
            }
            if (!$keys) $keys = $this->__delegate->$key;
            $this->__query = str_replace("{".$key."}", $keys, $this->__query);
            $this->all($this->__query);
            
            $filtered = array();
            if ($this->data) {
                foreach ($this->data as &$item) {
                    if ($item->$foreign == $this->__delegate->$key) $filtered[] = $item;
                }
                $this->data = $filtered;
            }
        }
        $this->position = 0;
    }

    public function current() {
        $item = $this->data[$this->position];
        $item->data = $this->data;
        return $item;
    }

    public function key() {
        return $this->position;
    }

    public function next() {
        ++$this->position;
    }

    public function valid() {
        return isset($this->data[$this->position]);
    }
}

?>