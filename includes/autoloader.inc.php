<?php 
/**
 * class autoloader
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: autoloader.inc.php 335 2009-09-25 07:58:30Z bigmichi1 $
 * @link      http://phpsysinfo.sourceforge.net
 */
 
error_reporting(E_ALL | E_STRICT);

/**
 * automatic loading classes when using them
 *
 * @param string $class_name name of the class which must be loaded
 *
 * @return void
 */
function __autoload($class_name)
{
    $class_name = str_replace('-', '', $class_name);
    $dirs = array('/plugins/'.$class_name.'/', '/includes/', '/includes/interface/', '/includes/to/', '/includes/to/device/', '/includes/os/', '/includes/mb/', '/includes/plugin/', '/includes/xml/', '/includes/web/', '/includes/error/', '/includes/js/', '/includes/output/', '/includes/ups/');
    
    foreach ($dirs as $dir) {
        if (file_exists(APP_ROOT.$dir.'class.'.$class_name.'.inc.php')) {
            include_once APP_ROOT.$dir.'class.'.$class_name.'.inc.php';
            return;
        }
    }
    
    $error = Error::singleton();
    
    $error->addError("_autoload(\"".$class_name."\")", "autoloading of class file (class.".$class_name.".inc.php) failed!");
    $error->errorsAsXML();
}

/**
 * sets a user-defined error handler function
 *
 * @param integer $level   contains the level of the error raised, as an integer.
 * @param string  $message contains the error message, as a string.
 * @param string  $file    which contains the filename that the error was raised in, as a string.
 * @param integer $line    which contains the line number the error was raised at, as an integer.
 *
 * @return void
 */
function errorHandlerPsi($level, $message, $file, $line)
{
    $error = Error::singleton();
    $error->addPhpError("errorHandlerPsi : ", "Level : ".$level." Message : ".$message." File : ".$file." Line : ".$line);
}

set_error_handler('errorHandlerPsi');
?>
