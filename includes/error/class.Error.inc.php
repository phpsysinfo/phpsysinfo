<?php
/**
 * Error class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Error
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.Error.inc.php 569 2012-04-16 06:08:18Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * class for the error handling in phpsysinfo
 *
 * @category  PHP
 * @package   PSI_Error
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class Error
{
    /**
     * holds the instance of this class
     *
     * @static
     * @var object
     */
    private static $_instance;

    /**
     * holds the error messages
     *
     * @var array
     */
    private $_arrErrorList = array();

    /**
     * current number ob errors
     *
     * @var integer
     */
    private $_errors = 0;

    /**
     * initalize some used vars
     */
    private function __construct()
    {
        $this->_errors = 0;
        $this->_arrErrorList = array();
    }

    /**
     * Singleton function
     *
     * @return Error instance of the class
     */
    public static function singleton()
    {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }

        return self::$_instance;
    }

    /**
     * triggers an error when somebody tries to clone the object
     *
     * @return void
     */
    public function __clone()
    {
        trigger_error("Can't be cloned", E_USER_ERROR);
    }

    /**
     * adds an phpsysinfo error to the internal list
     *
     * @param string $strCommand Command, which cause the Error
     * @param string $strMessage additional Message, to describe the Error
     *
     * @return void
     */
    public function addError($strCommand, $strMessage)
    {
        $this->_addError($strCommand, $this->_trace($strMessage));
    }

    /**
     * adds an error to the internal list
     *
     * @param string $strCommand Command, which cause the Error
     * @param string $strMessage message, that describe the Error
     *
     * @return void
     */
    private function _addError($strCommand, $strMessage)
    {
        $index = count($this->_arrErrorList) + 1;
        $this->_arrErrorList[$index]['command'] = $strCommand;
        $this->_arrErrorList[$index]['message'] = $strMessage;
        $this->_errors++;
    }

    /**
     * add a config error to the internal list
     *
     * @param object $strCommand Command, which cause the Error
     * @param object $strMessage additional Message, to describe the Error
     *
     * @return void
     */
    public function addConfigError($strCommand, $strMessage)
    {
        $this->_addError($strCommand, "Wrong Value in phpsysinfo.ini for ".$strMessage);
    }

    /**
     * add a php error to the internal list
     *
     * @param object $strCommand Command, which cause the Error
     * @param object $strMessage additional Message, to describe the Error
     *
     * @return void
     */
    public function addPhpError($strCommand, $strMessage)
    {
        $this->_addError($strCommand, "PHP throws a error\n".$strMessage);
    }

    /**
     * adds a waraning to the internal list
     *
     * @param string $strMessage Warning message to display
     *
     * @return void
     */
    public function addWarning($strMessage)
    {
        $index = count($this->_arrErrorList) + 1;
        $this->_arrErrorList[$index]['command'] = "WARN";
        $this->_arrErrorList[$index]['message'] = $strMessage;
    }

    /**
     * converts the internal error and warning list to a XML file
     *
     * @return void
     */
    public function errorsAsXML()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $root = $dom->createElement("phpsysinfo");
        $dom->appendChild($root);
        $xml = new SimpleXMLExtended(simplexml_import_dom($dom), 'UTF-8');
        $generation = $xml->addChild('Generation');
        $generation->addAttribute('version', PSI_VERSION_STRING);
        $generation->addAttribute('timestamp', time());
        $xmlerr = $xml->addChild("Errors");
        foreach ($this->_arrErrorList as $arrLine) {
//            $error = $xmlerr->addCData('Error', $arrLine['message']);
            $error = $xmlerr->addChild('Error');
            $error->addAttribute('Message', $arrLine['message']);
            $error->addAttribute('Function', $arrLine['command']);
        }
        header("Cache-Control: no-cache, must-revalidate\n");
        header("Content-Type: text/xml\n\n");
        echo $xml->getSimpleXmlElement()->asXML();
        exit();
    }
    /**
     * add the errors to an existing xml document
     *
     * @param String $encoding encoding
     *
     * @return SimpleXmlElement
     */
    public function errorsAddToXML($encoding)
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $root = $dom->createElement("Errors");
        $dom->appendChild($root);
        $xml = simplexml_import_dom($dom);
        $xmlerr = new SimpleXMLExtended($xml, $encoding);
        foreach ($this->_arrErrorList as $arrLine) {
//            $error = $xmlerr->addCData('Error', $arrLine['message']);
            $error = $xmlerr->addChild('Error');
            $error->addAttribute('Message', $arrLine['message']);
            $error->addAttribute('Function', $arrLine['command']);
        }

        return $xmlerr->getSimpleXmlElement();
    }
    /**
     * check if errors exists
     *
     * @return boolean true if are errors logged, false if not
     */
    public function errorsExist()
    {
        if ($this->_errors > 0) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * generate a function backtrace for error diagnostic, function is genearally based on code submitted in the php reference page
     *
     * @param string $strMessage additional message to display
     *
     * @return string formatted string of the backtrace
     */
    private function _trace($strMessage)
    {
        $arrTrace = array_reverse(debug_backtrace());
        $strFunc = '';
        $strBacktrace = htmlspecialchars($strMessage)."\n\n";
        foreach ($arrTrace as $val) {
            // avoid the last line, which says the error is from the error class
            if ($val == $arrTrace[count($arrTrace) - 1]) {
                break;
            }
            $strBacktrace .= str_replace(APP_ROOT, ".", $val['file']).' on line '.$val['line'];
            if ($strFunc) {
                $strBacktrace .= ' in function '.$strFunc;
            }
            if ($val['function'] == 'include' || $val['function'] == 'require' || $val['function'] == 'include_once' || $val['function'] == 'require_once') {
                $strFunc = '';
            } else {
                $strFunc = $val['function'].'(';
                if (isset($val['args'][0])) {
                    $strFunc .= ' ';
                    $strComma = '';
                    foreach ($val['args'] as $val) {
                        $strFunc .= $strComma.$this->_printVar($val);
                        $strComma = ', ';
                    }
                    $strFunc .= ' ';
                }
                $strFunc .= ')';
            }
            $strBacktrace .= "\n";
        }

        return $strBacktrace;
    }
    /**
     * convert some special vars into better readable output
     *
     * @param mixed $var value, which should be formatted
     *
     * @return string formatted string
     */
    private function _printVar($var)
    {
        if (is_string($var)) {
            $search = array("\x00", "\x0a", "\x0d", "\x1a", "\x09");
            $replace = array('\0', '\n', '\r', '\Z', '\t');

            return ('"'.str_replace($search, $replace, $var).'"');
        } elseif (is_bool($var)) {
            if ($var) {
                return ('true');
            } else {
                return ('false');
            }
        } elseif (is_array($var)) {
            $strResult = 'array( ';
            $strComma = '';
            foreach ($var as $key=>$val) {
                $strResult .= $strComma.$this->_printVar($key).' => '.$this->_printVar($val);
                $strComma = ', ';
            }
            $strResult .= ' )';

            return ($strResult);
        }
        // anything else, just let php try to print it
        return (var_export($var, true));
    }
}
