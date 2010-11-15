<?
//  STImage.php
//  Sonata/Foundation
//
// Copyright 2010 Roman Efimov <romefimov@gmail.com>
//                Dan Sosedoff <http://blog.sosedoff.com>
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

class STImage {
  
  final private function __construct() {}
  final private function __clone() {}

  /**
   * 	Check if file is really image
   * 	Signatures (HEX):
   *  	ff d8 ff e0 => JPG
   * 		ff d8 ff e1 => JPEG
   * 		89 50 4e 47 => PNG
   * 		47 49 46 38 => GIF
   */
   public static function fileIsImage($file, &$type = null) {
           if (file_exists($file) && is_readable($file)) {
                   // image signatures
                   $imgSignatures = array('ffd8ffe0','ffd8ffe1', '89504e47','47494638');
                   $imgTypes = array('ffd8ffe0' => IMAGETYPE_JPEG, 'ffd8ffe1' => IMAGETYPE_JPEG,
                                     '89504e47' => IMAGETYPE_PNG, '47494638' => IMAGETYPE_GIF);
                   $result = false;
   
                   $handle = fopen($file,"rb");
                   if ($handle) {
                           $buff = fread($handle,4);
                           if ($buff) {
                                   $sign = '';
                                   for ($i=0;$i<4;$i++) $sign .= sprintf("%x",ord($buff[$i]));
                                   if (in_array($sign,$imgSignatures))  {
                                      $type = $imgTypes[$sign];
                                      $result = true;
                                   }
                           }
                           fclose($handle);
                   }
                   return $result;
           }
           return false;
   }
   
   public static function dataIsImage($data, &$type = null) {
           // image signatures
           $imgSignatures = array('ffd8ffe0','ffd8ffe1', '89504e47','47494638');
           $imgTypes = array('ffd8ffe0' => IMAGETYPE_JPEG, 'ffd8ffe1' => IMAGETYPE_JPEG,
                             '89504e47' => IMAGETYPE_PNG, '47494638' => IMAGETYPE_GIF);
           $result = false;

         
           $buff = substr($data, 0, 4);
           if ($buff) {
                   $sign = '';
                   for ($i=0;$i<4;$i++) $sign .= sprintf("%x",ord($buff[$i]));
                   if (in_array($sign,$imgSignatures))  {
                      $type = $imgTypes[$sign];
                      $result = true;
                   }
           }
               
           return $result;
   }
 
   public static function resizeImageProportional($nw, $nh, $source, $stype, $dest = null, $is_file = true) {

       if ($is_file) {
         $size = getimagesize($source);
         $w = $size[0];
         $h = $size[1];
         switch ($stype) {
             case IMAGETYPE_GIF:
                 $simg = imagecreatefromgif($source);
                 break;
             case IMAGETYPE_JPEG:
                 $simg = imagecreatefromjpeg($source);
                 break;
             case IMAGETYPE_PNG:
                 $simg = imagecreatefrompng($source);
                 break;
         }
       } else {
           $simg = imagecreatefromstring($source);
           $w = imagesx($simg);
           $h = imagesy($simg);
       }
       
       
       $dimg = imagecreatetruecolor($nw, $nh);
       
       $wm = $w / $nw;
       $hm = $h / $nh;
       
       $h_height = $nh / 2;
       $w_height = $nw / 2;
       
       if ($w > $h) {
           if ($hm == 0)
               $hm = 1;
           $adjusted_width = $w / $hm;
           $half_width = $adjusted_width / 2;
           $int_width = $half_width - $w_height;
           
           imagecopyresampled($dimg, $simg, -$int_width, 0, 0, 0, $adjusted_width, $nh, $w, $h);
       } elseif (($w < $h) || ($w == $h)) {
           $adjusted_height = $h / $wm;
           $half_height = $adjusted_height / 2;
           $int_height = $half_height - $h_height;
           
           imagecopyresampled($dimg, $simg, 0, -$int_height, 0, 0, $nw, $adjusted_height, $w, $h);
       } else {
           imagecopyresampled($dimg, $simg, 0, 0, 0, 0, $nw, $nh, $w, $h);
       }
       if ($dest != null)
           imagejpeg($dimg, $dest, 100);
       else
           return $dimg;
   }
   
   public static function resizeImageProportionalWithOffset($nw, $nh, $source, $stype, $dest = null, $is_file = true) {

       if ($is_file) {
         $size = getimagesize($source);
         $w = $size[0];
         $h = $size[1];
         switch ($stype) {
             case IMAGETYPE_GIF:
                 $simg = imagecreatefromgif($source);
                 break;
             case IMAGETYPE_JPEG:
                 $simg = imagecreatefromjpeg($source);
                 break;
             case IMAGETYPE_PNG:
                 $simg = imagecreatefrompng($source);
                 break;
         }
       } else {
           $simg = imagecreatefromstring($source);
           $w = imagesx($simg);
           $h = imagesy($simg);
       }
       
       
       $dimg = imagecreatetruecolor($nw, $nh);
       
       $wm = $w / $nw;
       $hm = $h / $nh;
       
       $h_height = $nh / 2;
       $w_height = $nw / 2;
       
       if ($w > $h) {
           if ($hm == 0)
               $hm = 1;
           $adjusted_width = $w / $hm;
           $half_width = $adjusted_width / 2;
           $int_width = $half_width - $w_height;
           
           imagecopyresampled($dimg, $simg, -$int_width, 0, 0, 0, $adjusted_width, $nh, $w, $h);
       } elseif (($w < $h) || ($w == $h)) {
         
           $adjusted_height = $h / $wm;
           $half_height = $h_height;//round($adjusted_height / 3.5);
           $int_height = $half_height - $h_height;
          // echo 'here';
           imagecopyresampled($dimg, $simg, 0, -$int_height, 0, 0, $nw, $adjusted_height, $w, $h);
       } else {
           imagecopyresampled($dimg, $simg, 0, 0, 0, 0, $nw, $nh, $w, $h);
       }
       if ($dest != null)
           imagejpeg($dimg, $dest, 100);
       else
           return $dimg;
   }
   
   public static function smartImageResize($file, $width = 0, $height = 0, $proportional = false, $output = 'file', $is_file = true, $type = '') {
       $delete_original = true;
       $use_linux_commands = false;
       
       if ($height <= 0 && $width <= 0) {
           return false;
       }
       
       if ($is_file)
       $info = getimagesize($file); else {
          $info[2] = $type;
       }
       $image = '';
       
       $final_width = 0;
       $final_height = 0;
       if ($is_file)
       list($width_old, $height_old) = $info; else {
           $image = imagecreatefromstring($file);
           $width_old = imagesx($image);
           $height_old = imagesy($image);
       }
       

       
       if ($proportional) {
           if ($width == 0)
               $factor = $height / $height_old;
           elseif ($height == 0)
               $factor = $width / $width_old;
           else
               $factor = min($width / $width_old, $height / $height_old);
           
           $final_width = round($width_old * $factor);
           $final_height = round($height_old * $factor);
       } else {
           $final_width = ($width <= 0) ? $width_old : $width;
           $final_height = ($height <= 0) ? $height_old : $height;
       }
       
       if ($is_file)
       switch ($info[2]) {
           case IMAGETYPE_GIF:
               $image = imagecreatefromgif($file);
               break;
           case IMAGETYPE_JPEG:
               $image = imagecreatefromjpeg($file);
               break;
           case IMAGETYPE_PNG:
               $image = imagecreatefrompng($file);
               break;
           default:
               return false;
       }
       
       if (($width > $width_old) && ($height > $height_old)) {
         imagejpeg($image, $output, 80);
         return;
       }
       
       $image_resized = imagecreatetruecolor($final_width, $final_height);
       
       if (($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG)) {
           $trnprt_indx = imagecolortransparent($image);
           
           
           if ($trnprt_indx >= 0) {
               $trnprt_color = imagecolorsforindex($image, $trnprt_indx);
               
               
               $trnprt_indx = imagecolorallocate($image_resized, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
               
               
               imagefill($image_resized, 0, 0, $trnprt_indx);
               
               
               imagecolortransparent($image_resized, $trnprt_indx);
           } elseif ($info[2] == IMAGETYPE_PNG) {
               imagealphablending($image_resized, false);
               
               
               $color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
               
               
               imagefill($image_resized, 0, 0, $color);
               
               
               imagesavealpha($image_resized, true);
           }
       }
       
       imagecopyresampled($image_resized, $image, 0, 0, 0, 0, $final_width, $final_height, $width_old, $height_old);
       
       if ($delete_original) {
           if ($use_linux_commands)
               exec('rm ' . $file);
           else
               @unlink($file);
       }
       
       switch (strtolower($output)) {
           case 'browser':
               $mime = image_type_to_mime_type($info[2]);
               header("Content-type: $mime");
               $output = null;
               break;
           case 'file':
               $output = $file;
               break;
           case 'return':
               return $image_resized;
               break;
           default:
               break;
       }
       
       imagejpeg($image_resized, $output, 80);
       
       return true;
   }
}
?>