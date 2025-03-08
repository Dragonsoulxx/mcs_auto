<?php
// muttics login details for auto exchange.
$user = 'root'; // multics http user
$pass = 'root'; // multics http pass
$url = 'multics-exchange.com'; // your multics host
$port = '5500'; // multics http port, (tip: display or empty multics port to close exchagne)
 
 
$portcache = '4545';  // your cache port
$editor_cache = '1';  // multics editor id where cache.cfg file include.


// you can set mutiple cache as well.
$cache_deta = "
CACHE PEER: r82s3.multics-exchange.com 4300 1
CACHE PEER: r100.multics-exchange.com 4444 1
CACHE PEER: r100.multics-exchange.com 5005 1
CACHE PEER: r100.multics-exchange.com 5555 1
";


$portcccam = '12000'; // your cccam port
$editor_cccam = '2'; // multics editor id where cccam.cfg file include.


$portmgcamd = '28000'; // your mgcamd port
$editor_mgcamd = '3'; // multics editor id where mgcamd.cfg file include.

$keys = "01 02 03 04 05 06 07 08 09 10 11 12 13 14"; // your deskey.
$multics_version = 'R82 HB';


// exchnge line check delay, if you mutlics server slow load line status due to load reason increase the delay number, normal value = 10
$delay_sec = '10';


?>
