<?php
header("Content-Type: text/plain");
$filename = '/proc/cpuinfo';
echo "----------".$filename."----------\n";
echo $contents=file_get_contents($filename);
if ((strlen($contents)>0)&&(substr($contents, -1)!="\n")) {
    echo "\n";
}
