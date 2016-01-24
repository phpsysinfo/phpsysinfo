<?php
echo "<!DOCTYPE html>";
echo "<meta charset=\"UTF-8\">";
echo "<title> </title>";
echo "<body>";

define('APP_ROOT', dirname(__FILE__).'/..');
require_once APP_ROOT.'/includes/interface/class.PSI_Interface_OS.inc.php';
require_once APP_ROOT.'/includes/os/class.OS.inc.php';
require_once APP_ROOT.'/includes/to/class.System.inc.php';
require_once APP_ROOT.'/includes/os/class.Linux.inc.php';
define('PSI_USE_VHOST', false);
define('PSI_DEBUG', false);
define('PSI_LOAD_BAR', false);

$log_file = "";
$lsb = true; //enable detection lsb_release -a
$lsbfile = true; //enable detection /etc/lsb-release

class PSI_Error
{
    public static function singleton()
    {
    }
}

class Parser
{
    public static function lspci()
    {
        return array();
    }
    public static function df()
    {
        return array();
    }
}

class CommonFunctions
{
    private static function _parse_log_file($string)
    {
        global $log_file;
        if (file_exists($log_file)) {
            $contents = @file_get_contents($log_file);
            $contents = preg_replace("/\r\n/", "\n", $contents);
            if ($contents && preg_match("/^\-\-\-\-\-\-\-\-\-\-".preg_quote($string, '/')."\-\-\-\-\-\-\-\-\-\-\n/m", $contents, $matches, PREG_OFFSET_CAPTURE)) {
                $findIndex = $matches[0][1];
                if (preg_match("/\n/m", $contents, $matches, PREG_OFFSET_CAPTURE, $findIndex)) {
                    $startIndex = $matches[0][1]+1;
                    if (preg_match("/^\-\-\-\-\-\-\-\-\-\-/m", $contents, $matches, PREG_OFFSET_CAPTURE, $startIndex)) {
                        $stopIndex = $matches[0][1];

                        return substr($contents, $startIndex, $stopIndex-$startIndex);
                    } else {
                        return substr($contents, $startIndex);
                    }
                }
            }
        }

        return false;
    }

    public static function rfts($strFileName, &$strRet, $intLines = 0, $intBytes = 4096, $booErrorRep = true)
    {
        global $lsb;
        global $lsbfile;
        if ($lsb || $lsbfile || ($strFileName != "/etc/lsb-release")) {
            $strRet=self::_parse_log_file($strFileName);
            if ($strRet && ($intLines == 1) && (strpos($strRet, "\n") !== false)) {
                $strRet=trim(substr($strRet, 0, strpos($strRet, "\n")));
            }

            return $strRet;
        } else {
            return false;
        }
    }

    public static function executeProgram($strProgramname, $strArgs, &$strBuffer, $booErrorRep = true)
    {
        global $lsb;
        $strBuffer = '';
        if ($strProgramname=='lsb_release') {
            return $lsb && ($strBuffer = self::_parse_log_file('lsb_release -a'));
        } else {
            return $strBuffer = self::_parse_log_file($strProgramname);
        }
    }

    public static function fileexists($strFileName)
    {
        global $log_file;
        global $lsb;
        global $lsbfile;
        if (file_exists($log_file)
            && ($lsb || $lsbfile || ($strFileName != "/etc/lsb-release"))
            && ($contents = @file_get_contents($log_file))
            && preg_match("/^\-\-\-\-\-\-\-\-\-\-".preg_quote($strFileName, '/')."\-\-\-\-\-\-\-\-\-\-\r?\n/m", $contents)) {
            return true;
        }

        return false;
    }

    public static function gdc()
    {
        return array();
    }
}

class _Linux extends Linux
{
    public function build()
    {
        parent::_distro();
    }
}

$system = new _Linux();
if ($handle = opendir(APP_ROOT.'/sample/distrotest')) {
    echo "<table cellpadding=\"2\" border=\"1\"  CELLSPACING=\"0\"";
    echo "<tr>";
    echo "<td>Distrotest sample</td>";
    echo "<td>Distro Name</td>";
    echo "<td>Distro Icon</td>";
    echo "<td>Distro Name (no lsb_release)</td>";
    echo "<td>Distro Icon (no lsb_release)</td>";
    echo "<td>Distro Name (no lsb_release and no /etc/lsb-release)</td>";
    echo "<td>Distro Icon (no lsb_release and no /etc/lsb-release)</td>";
    echo "</tr>";
    while (false !== ($entry = readdir($handle))) {
        if (($entry!=".")&&($entry!="..")) {
            if ($shandle = opendir(APP_ROOT."/sample/distrotest/$entry")) {
                while (false !== ($sentry = readdir($shandle))) {
                    if (($sentry!=".")&&($sentry!="..")) {
                        $log_file=APP_ROOT.'/sample/distrotest/'.$entry.'/'.$sentry;
                        echo "<tr>";
                        echo "<td>".$entry.'/'.$sentry."</td>";

                        $lsb = true;
                        $lsbfile = true;
                        $sys=$system->getSys();
                        $distro=$sys->getDistribution();
                        $icon=$sys->getDistributionIcon();
                        if ($icon == '') $icon="unknown.png";
                        if ($icon != $entry.'.png')
                            echo "<td style='color:red'>";
                        else
                            echo "<td>";
                        echo $distro."</td>";
                        if ($icon != $entry.'.png')
                            echo "<td style='color:red'>";
                        else
                            echo "<td>";
                        echo "<img src=\"../gfx/images/".$icon."\" height=\"16\" width=\"16\">";
                        echo $icon."</td>";
                        $sys->setDistribution("");
                        $sys->setDistributionIcon("");

                        $lsb = false;
                        $lsbfile = true;
                        $sys=$system->getSys();
                        $distro=$sys->getDistribution();
                        $icon=$sys->getDistributionIcon();
                        if ($icon == '') $icon="unknown.png";
                        if ($icon != $entry.'.png')
                            echo "<td style='color:red'>";
                        else
                            echo "<td>";
                        echo $distro."</td>";
                        if ($icon != $entry.'.png')
                            echo "<td style='color:red'>";
                        else
                            echo "<td>";
                        echo "<img src=\"../gfx/images/".$icon."\" height=\"16\" width=\"16\">";
                        echo $icon."</td>";
                        $sys->setDistribution("");
                        $sys->setDistributionIcon("");

                        $lsb = false;
                        $lsbfile = false;
                        $sys=$system->getSys();
                        $distro=$sys->getDistribution();
                        $icon=$sys->getDistributionIcon();
                        if ($icon == '') $icon="unknown.png";
                        if ($icon != $entry.'.png')
                            echo "<td style='color:red'>";
                        else
                            echo "<td>";
                        echo $distro."</td>";
                        if ($icon != $entry.'.png')
                            echo "<td style='color:red'>";
                        else
                            echo "<td>";
                        echo "<img src=\"../gfx/images/".$icon."\" height=\"16\" width=\"16\">";
                        echo $icon."</td>";
                        $sys->setDistribution("");
                        $sys->setDistributionIcon("");

                        echo "</tr>";
                    }
                }
                closedir($shandle);
            }
        }
    }
    echo "</table>";
    closedir($handle);
}
echo "</body>";
