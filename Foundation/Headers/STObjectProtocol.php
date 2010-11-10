<?

interface STObjectProtocol {
    
    public function className();
    public function parentClassName();
    public function isMemberOfClass($class_name);
    public function isSubclassOfClass($class_name);
    public function isKindOfClass($class_name);
    public function performSelector($function, $argv);
    
}

?>