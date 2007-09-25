<?php 
define("GzipCompressionLevel",9); 

function gzipCompressionHandler($content) 
{ 
    preg_match_all("{[\w\-]+}",$_SERVER["HTTP_ACCEPT_ENCODING"],$matches); 
    $encoding=false; 
    if(in_array("x-gzip",$matches[0])) $encoding="x-gzip"; 
    if(in_array("gzip",$matches[0])) $encoding="gzip"; 
    if($encoding!==false && function_exists("gzcompress")) { 
      $header="\x1f\x8b\x08\x00\x00\x00\x00\x00"; 
      $compressed=substr(gzcompress($content, GzipCompressionLevel),0,-4); 
      $trailer=pack("V",crc32($content)).pack("V",strlen($content)); 
      $content=$header.$compressed.$trailer; 
      @header("Content-Encoding: $encoding"); 
      @header("Content-Length: ".strlen($content)); 
    } 
  return $content;
} 

if(isset($_SERVER['HTTP_ACCEPT_ENCODING'])){$AE = $_SERVER['HTTP_ACCEPT_ENCODING'];}
 else{$AE = @$_SERVER['HTTP_TE'];}

$support_gzip = (strpos($AE, 'gzip') !== FALSE) && $_CONFIG['force_gzip'];

if($support_gzip){
 ob_start("gzipCompressionHandler"); 
 $gzpstatus = "on";
} 
else 
{
 ob_start('');
 $gzpstatus = "off";
}

?>