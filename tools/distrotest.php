<?php
header("Content-Type: text/plain");
$filemaskarray=array("/etc/*-release",
                     "/etc/*_release",
                     "/etc/*-version",
                     "/etc/*_version",
                     "/etc/version",
                     "/etc/DISTRO_SPECS",
                     "/etc/eisfair-system",
                     "/usr/share/doc/tc/release.txt",
                     "/etc/synoinfo.conf",
                     "/etc/salix-update-notifier.conf",
                     "/system/build.prop");
$fp = popen("lsb_release -a 2>/dev/null", "r");
if (is_resource($fp)) {
    echo "----------lsb_release -a----------\n";
    $contents="";
    while (!feof($fp)) {
        echo $contents=fgets($fp, 4096);
    }
    if ((strlen($contents)>0)&&(substr($contents, -1)!="\n")){
        echo "<-----no new line at end\n";
    }
    pclose($fp);
}

foreach ($filemaskarray as $filemask) {
    foreach (glob($filemask) as $filename) {
        echo "----------".$filename."----------\n";
        echo $contents=file_get_contents($filename);
        if ((strlen($contents)>0)&&(substr($contents, -1)!="\n")){
            echo "<-----no new line at end\n";
        }
        //readfile($filename);
    }
}
?>
