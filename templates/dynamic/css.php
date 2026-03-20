<?php
/**
 * reads css files and send them to the browser on the fly with correction
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_CSS
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2021 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 1.0
 * @link      http://phpsysinfo.sourceforge.net
 */

$file = isset($_GET['name']) ? basename(htmlspecialchars($_GET['name'])) : null;
$increase = isset($_GET['increase']) ? (($_GET['increase']>0)? $_GET['increase'] : 0) : 0;

if ($file != null) {
    $css = $file.'.css';

    header('content-type: text/css');

    if (file_exists($css) && is_readable($css)) {
        $filecontent = file_get_contents($css);
        if ($increase == 0) {
            echo $filecontent;
        } else {
            $lines = preg_split("/\r?\n/", $filecontent, -1, PREG_SPLIT_NO_EMPTY);
            $block = '';
            foreach ($lines as $line) {
                if (preg_match('/^(.+)\{/', $line, $tmpbuf)) {
                    $block = strtolower(trim($tmpbuf[1]));
                    echo $line."\n";
                } elseif (($block === 'body') || ($block === '.fullsize')) {
                    if (preg_match('/^(\s*_?width\s*:\s*)(\d+)(px.*)/', $line, $widthbuf)) {
                        echo $widthbuf[1].($widthbuf[2]+$increase).$widthbuf[3]."\n";
                    } elseif (preg_match('/^(\s*background\s*:.+)(url)/', $line, $widthbuf)) {
                        echo $widthbuf[1].";\n";
                    } else echo $line."\n";
                } elseif ($block === '.halfsize') {
                    if (preg_match('/^(\s*_?width\s*:\s*)(\d+)(px.*)/', $line, $widthbuf)) {
                        echo $widthbuf[1].($widthbuf[2]+$increase/2).$widthbuf[3]."\n";
                    } else echo $line."\n";
                } else {
                    echo $line."\n";
                }
            }
        }
    }
}
