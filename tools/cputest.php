<?php
header("Content-Type: text/plain");
$filename = '/proc/cpuinfo';
$contents=file_get_contents($filename);
echo "----------".$filename."----------\n";
if (strlen($contents)>0) {
    if (substr($contents, -1)!="\n") {
        $contents.="\n";
    }
    $contents = preg_replace('/^(\s*serial\s*:\s*)(\S+)/im', '$1xxxxxxxxxxxxxxxx', $contents);
    echo $contents;
}
