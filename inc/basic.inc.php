<?php
class MTimer
{
        var $starttime=0;
        function start() {
                $mtime = microtime();
                $mtime = explode (' ', $mtime);
                $mtime = $mtime[1] + $mtime[0];
                $this->starttime = $mtime;
        }
        function stop() {
                $mtime = microtime();
                $mtime = explode (' ', $mtime);
                $mtime = $mtime[1] + $mtime[0];
                $endtime = $mtime;
                $totaltime = round (($endtime - $this->starttime), 5);
                return $totaltime;
        }
}
function sortbylevel($a, $b) 
{
   if(!is_array($a) OR !is_array($b)) return 0;
   if ($a['level'] == $b['level']) {
       return strcmp($a['name'], $b['name']);
   }
   return ($a['level'] > $b['level']) ? -1 : 1;
}
function sortbyplayers($a, $b) 
{
   if ($a['players'] == $b['players']) {
       return 0;
   }
   return ($a['players'] > $b['players']) ? -1 : 1;
}
class Net_CheckIP
{
    function check_ip($ip)
    {
        $oct = explode('.', $ip);
        if (count($oct) != 4) {
            return false;
        }

        for ($i = 0; $i < 4; $i++) {
            if (!is_numeric($oct[$i])) {
                return false;
            }

            if ($oct[$i] < 0 || $oct[$i] > 255) {
                return false;
            }
        }

        return true;
    }
}
if(!function_exists('get_headers'))
{
	function get_headers($url,$format=0) {
	       $url_info=parse_url($url);
	       $port = isset($url_info['port']) ? $url_info['port'] : 80;
	       $fp=fsockopen($url_info['host'], $port, $errno, $errstr, 30);
	       if($fp) {
	           if(!$url_info['path']){
	                         $url_info['path'] = "/";
	                     }
	                     if($url_info['path'] && !$url_info['host']){
	                       $url_info['host'] = $url_info['path'];
	                       $url_info['path'] = "/";
	                     }
	                     if( $url_info['host'][(strlen($url_info['host'])-1)] == "/" ){
	                       $url_info['host'][(strlen($url_info['host'])-1)] = "";
	                     }
	                     if(!@$url_array['scheme']){
	                         $url_array['scheme'] = "http"; //we always use http links
	                       }
	                     $head = "HEAD ".@$url_info['path'];
	                     if( $url_info['query'] ){
	                         $head .= "?".@$url_info['query'];
	                       }
	                       //print_r($url_info);
	           $head .= " HTTP/1.0\r\nHost: ".@$url_info['host']."\r\n\r\n";
	           //echo $head;
	                     fputs($fp, $head);
	           while(!feof($fp)) {
	               if($header=trim(fgets($fp, 1024))) {
	                   if($format == 1) {
	                       $h2 = explode(':',$header);
	                       // the first element is the http header type, such as HTTP/1.1 200 OK,
	                       // it doesn't have a separate name, so we have to check for it.
	                       if($h2[0] == $header) {
	                           $headers['status'] = $header;
	                       }
	                       else {
	                           $headers[strtolower($h2[0])] = trim($h2[1]);
	                       }
	                   }
	                   else {
	                       $headers[] = $header;
	                   }
	               }
	           }
	           return $headers;
	       }
	       else {
	           return false;
	       }
	   }
}
?>