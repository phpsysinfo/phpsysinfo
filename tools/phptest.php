<?php
function echoinfo($extension) {
    echo "                                    ".$extension;
    switch(strtolower($extension)) {
        case 'json':
            echo " - reqired for bootstrap and xml json mode";
            break;
        case 'simplexml':
        case 'pcre':
        case 'dom':
        case 'xml':
//            echo " - reqired";
            break;
        case 'com_dotnet':
            echo " - reqired on WINNT systems";
            break;
        case 'snmp':
            echo " - reqired for snmppinfo plugin and snmpups";
            break;
        case 'pfsense':
            echo " - loaded on pfSense system";
            break;
        case 'mbstring':
            echo " - reqired on non UTF-8 and non CP437 systems";
            break;
        case 'xsl':
            echo " - reqired for static mode";
    }
    echo "\n";
}

header('Content-Type: text/plain');

echo "SERVER SOFTWARE: ".$_SERVER["SERVER_SOFTWARE"]."\n";
echo "PHP VERSION: ".PHP_VERSION;
if (version_compare("5.1.3", PHP_VERSION, ">")) {
    echo " - PHP 5.1.3 or greater is required!!!";
}
echo "\n";

echo "PHP EXTENSIONS:\n";
$arrReq = array('simplexml', 'pcre', 'xml', 'dom', 'mbstring', 'com_dotnet', 'json', 'xsl', 'snmp', 'pfsense');
$extensions = get_loaded_extensions();
$extarray = array();
foreach ($extensions as $extension) {
    $extarray[strtolower($extension)] = $extension;
}

$first = true;
foreach ($arrReq as $extension) if (isset($extarray[$extension])) {
    if ($first) {
        echo "                requred loaded:\n";
        $first = false;
    }
    echoinfo($extarray[$extension]);
}
$first = true;
foreach ($arrReq as $extension) if (!isset($extarray[$extension])) {
    if ($first) {
        echo "                requred not loaded:\n";
        $first = false;
    }
    echoinfo($extension);
}
$first = true;
foreach ($extarray as $extlower=>$extension) if (!in_array($extlower, $arrReq, true)) {
    if ($first) {
        echo "                others loaded:\n";
        $first = false;
    }
    echoinfo($extension);
}
