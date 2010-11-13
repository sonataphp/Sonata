<?
//
//  STCSV.php
//  Sonata/Foundation
//
//  Created by Roman Efimov on 6/10/2010.
//

class STCSV extends STObject {
  
    private $config;
    
    public function __construct($delimiter = ',', $enclosure = '"', $return = "\r") {
        $this->config['delimiter'] = $delimiter;
        $this->config['enclosure'] = $enclosure;
        $this->config['return'] = $return;
        return $this;
    }
    
    public function sendHeaders($filename) {
            header("Content-type: text/csv");
            header("Content-disposition:  attachment; filename=".$filename);
        }
        
    public function arrayToCsvString($array) {
        if (!$array) throw new Exception("Array shouldn't be empty");
        foreach ($array as &$row) {
            if ($row)
            foreach ($row as &$item) {
                $item = $this->config['enclosure'].str_replace($this->config['enclosure'],
                                                               $this->config['enclosure'].$this->config['enclosure'],
                                                               $item).
                        $this->config['enclosure'];
            }
            $row = implode($this->config['delimiter'], $row);
        }
        $array = implode($this->config['return'], $array);
        return $array;
    }
    
    public function arrayToCsvFile($fileName, $array) {
        $file = new STFile($fileName);
        $file->write($this->arrayToCsvString($array), $fileName); 
    }
    
    private function parse($data, $delimiter = ',', $enclosure = '"'){
        $data = str_replace(array("\r\n", "\n", "\n\r"), "\r", $data);
        $newline = "\r";
        $pos = $last_pos = -1;
        $end = strlen($data);
        $row = 0;
        $quote_open = false;
        $trim_quote = false;

        $return = array();

        for ($i = -1;; ++$i){
            ++$pos;
            $comma_pos = strpos($data, $delimiter, $pos);
            $quote_pos = strpos($data, $enclosure, $pos);
            $newline_pos = strpos($data, $newline, $pos);

            $pos = min(($comma_pos === false) ? $end : $comma_pos, ($quote_pos === false) ? $end : $quote_pos, ($newline_pos === false) ? $end : $newline_pos);

            $char = (isset($data[$pos])) ? $data[$pos] : null;
            $done = ($pos == $end);
            if ($done || $char == $delimiter || $char == $newline){
                if ($quote_open && !$done){
                    continue;
                }

                $length = $pos - ++$last_pos;
                if ($trim_quote){
                    --$length;
                }
                $return[$row][] = ($length > 0) ? str_replace($enclosure . $enclosure, $enclosure, substr($data, $last_pos, $length)) : '';
                if ($done){
                    break;
                }
                $last_pos = $pos;
                if ($char == $newline){
                    ++$row;
                }
                $trim_quote = false;
            }
            else if ($char == $enclosure){
                if ($quote_open == false){
                    $quote_open = true;
                    $trim_quote = false;
                    if ($last_pos + 1 == $pos){
                        ++$last_pos;
                    }
                }
                else {
                    $quote_open = false;
                    $trim_quote = true;
                }
            }
        }

        return $return;
    }
    
    
    public function csvStringToArray($string) {
        return $this->parse($string,
                            $this->config['delimiter'],
                            $this->config['enclosure']);
    }
        
        
    public function csvFileToArray($fileName) {
        $file = new STFile($fileName);
        return $this->csvStringToArray($file->read());
    }
}
?>