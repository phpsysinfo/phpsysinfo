<?php
/**
 * Basic OS Class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI OS class
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: class.OS.inc.php 699 2012-09-15 11:57:13Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * Basic OS functions for all OS classes
 *
 * @category  PHP
 * @package   PSI OS class
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
abstract class OS implements PSI_Interface_OS
{
    /**
     * object for error handling
     *
     * @var PSI_Error
     */
    protected $error;

    /**
     * block name
     *
     * @var string
     */
    protected $blockname = false;

    /**
     * @var System
     */
    protected $sys;

    /**
     * build the global Error object
     */
    public function __construct($blockname = false)
    {
        $this->error = PSI_Error::singleton();
        $this->sys = new System();
        $this->blockname = $blockname;
    }

    /**
     * get os specific encoding
     *
     * @see PSI_Interface_OS::getEncoding()
     *
     * @return string
     */
    public function getEncoding()
    {
        return PSI_SYSTEM_CODEPAGE;
    }

    /**
     * get os specific language
     *
     * @see PSI_Interface_OS::getLanguage()
     *
     * @return string
     */
    public function getLanguage()
    {
        return PSI_SYSTEM_LANG;
    }

    /**
     * get block name
     *
     * @see PSI_Interface_OS::getBlockName()
     *
     * @return string
     */
    public function getBlockName()
    {
        return $this->blockname;
    }

    /**
     * Number of Users
     *
     * @return void
     */
    protected function _users()
    {
        if (CommonFunctions::executeProgram('who', '', $strBuf, PSI_DEBUG)) {
            if (strlen($strBuf) > 0) {
                $lines = preg_split('/\n/', $strBuf);
                $this->sys->setUsers(count($lines));
            }
        } elseif (CommonFunctions::executeProgram('uptime', '', $buf, PSI_DEBUG) && preg_match("/,\s+(\d+)\s+user[s]?,/", $buf, $ar_buf)) {
        //} elseif (CommonFunctions::executeProgram('uptime', '', $buf) && preg_match("/,\s+(\d+)\s+user[s]?,\s+load average[s]?:\s+(.*),\s+(.*),\s+(.*)$/", $buf, $ar_buf)) {
            $this->sys->setUsers($ar_buf[1]);
        } else {
            $processlist = glob('/proc/*/cmdline', GLOB_NOSORT);
            if (is_array($processlist) && (($total = count($processlist)) > 0)) {
                $count = 0;
                $buf = "";
                for ($i = 0; $i < $total; $i++) {
                    if (CommonFunctions::rfts($processlist[$i], $buf, 0, 4096, false)) {
                        $name = str_replace(chr(0), ' ', trim($buf));
                        if (preg_match("/^-/", $name)) {
                            $count++;
                        }
                    }
                }
                if ($count > 0) {
                    $this->sys->setUsers($count);
                }
            }
        }
    }

    /**
     * IP of the Host
     *
     * @return void
     */
    protected function _ip()
    {
        if ((PSI_USE_VHOST === true) && !defined('PSI_EMU_HOSTNAME')) {
           if ((CommonFunctions::readenv('SERVER_ADDR', $result) || CommonFunctions::readenv('LOCAL_ADDR', $result)) //is server address defined
              && !strstr($result, '.') && strstr($result, ':')) { //is IPv6, quick version of preg_match('/\(([[0-9A-Fa-f\:]+)\)/', $result)
                $dnsrec = dns_get_record($this->sys->getHostname(), DNS_AAAA);
                if (isset($dnsrec[0]['ipv6'])) { //is DNS IPv6 record
                    $this->sys->setIp($dnsrec[0]['ipv6']); //from DNS (avoid IPv6 NAT translation)
                } else {
                    $this->sys->setIp(preg_replace('/^::ffff:/i', '', $result)); //from SERVER_ADDR or LOCAL_ADDR
                }
            } else {
                $this->sys->setIp(gethostbyname($this->sys->getHostname())); //IPv4 only
            }
        } elseif (((PSI_OS != 'WINNT') && !defined('PSI_EMU_HOSTNAME')) && (CommonFunctions::readenv('SERVER_ADDR', $result) || CommonFunctions::readenv('LOCAL_ADDR', $result))) {
            $this->sys->setIp(preg_replace('/^::ffff:/i', '', $result));
        } else {
            //$this->sys->setIp(gethostbyname($this->sys->getHostname()));
            $hn = $this->sys->getHostname();
            $ghbn = gethostbyname($hn);
            if (defined('PSI_EMU_HOSTNAME') && ($hn === $ghbn)) {
                $this->sys->setIp(PSI_EMU_HOSTNAME);
            } else {
                $this->sys->setIp($ghbn);
            }
        }
    }

    /**
     * MEM information from dmidecode
     *
     * @return void
     */
    protected function _dmimeminfo()
    {
        $banks = array();
        $buffer = '';
        if (defined('PSI_DMIDECODE_ACCESS') && (strtolower(PSI_DMIDECODE_ACCESS)=="data")) {
            CommonFunctions::rfts(PSI_APP_ROOT.'/data/dmidecode.txt', $buffer);
        } elseif (CommonFunctions::_findProgram('dmidecode')) {
            CommonFunctions::executeProgram('dmidecode', '-t 17', $buffer, PSI_DEBUG);
        }
        if (!empty($buffer)) {
            $banks = preg_split('/^(?=Handle\s)/m', $buffer, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($banks as $bank) if (preg_match('/^Handle\s/', $bank)) {
                $lines = preg_split("/\n/", $bank, -1, PREG_SPLIT_NO_EMPTY);
                $mem = array();
                foreach ($lines as $line) if (preg_match('/^\s+([^:]+):(.+)/' ,$line, $params)) {
                    if (preg_match('/^0x([A-F\d]+)/', $params2 = trim($params[2]), $buff)) {
                        $mem[trim($params[1])] = trim($buff[1]);
                    } elseif ($params2 != '') {
                        $mem[trim($params[1])] = $params2;
                    }
                }
                if (isset($mem['Size']) && preg_match('/^(\d+)\sMB$/', $mem['Size'], $size) && ($size[1] > 0)) {
                    $dev = new HWDevice();
                    $name = '';
                    if (isset($mem['Part Number']) && !preg_match("/^PartNum\d+$/", $part = $mem['Part Number']) && ($part != 'None') && ($part != 'NOT AVAILABLE')) {
                        $name = $part;
                    }
                    if (isset($mem['Locator']) && (($dloc = $mem['Locator']) != 'None')) {
                        if ($name != '') {
                            $name .= ' - '.$dloc;
                        } else {
                            $name = $dloc;
                        }
                    }
                    if (isset($mem['Bank Locator']) && (($bank = $mem['Bank Locator']) != 'None') && ($bank != 'Not Specified')) {
                        if ($name != '') {
                            $name .= ' in '.$bank;
                        } else {
                            $name = 'Physical Memory in '.$bank;
                        }
                    }
                    if ($name != '') {
                        $dev->setName(trim($name));
                    } else {
                        $dev->setName('Physical Memory');
                    }
                    if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                        if (isset($mem['Manufacturer']) && !preg_match("/^([A-F\d]{4}|[A-F\d]{12}|[A-F\d]{16})$/", $manufacturer = $mem['Manufacturer']) && !preg_match("/^Manufacturer\d+$/", $manufacturer) && ($manufacturer != 'None') && ($manufacturer != 'UNKNOWN')) {
                            $dev->setManufacturer($manufacturer);
                        }
                        $dev->setCapacity($size[1]*1024*1024);
                        $memtype = '';
                        if (isset($mem['Type']) && (($type = $mem['Type']) != 'None') && ($type != 'Other') && ($type != 'Unknown') && ($type != '<OUT OF SPEC>')) {
                            $memtype = $type;
                        }
                        if (isset($mem['Form Factor']) && (($form = $mem['Form Factor']) != 'None') && ($form != 'Other') && ($form != 'Unknown') && !preg_match('/ '.$form.'$/', $memtype)) {
                            $memtype .= ' '.$form;
                        }
                        if (isset($mem['Data Width']) && isset($mem['Total Width']) &&
                           preg_match('/^(\d+)\sbits$/', $mem['Data Width'], $dataw) && preg_match('/^(\d+)\sbits$/', $mem['Total Width'], $totalw) &&
                           ($dataw[1]  > 0) && ($totalw[1] >0) && ($dataw[1] < $totalw[1])) {
                            $memtype .= ' ECC';
                        }
                        if (($memtype = trim($memtype)) != '') {
                            $dev->setProduct($memtype);
                        }
                        if (isset($mem['Configured Clock Speed']) && preg_match('/^(\d+)\s(MHz|MT\/s)$/', $mem['Configured Clock Speed'], $clock) && ($clock[1] > 0)) {
                            $dev->setSpeed($clock[1]);
                        }
                        if (defined('PSI_SHOW_DEVICES_SERIAL') && PSI_SHOW_DEVICES_SERIAL &&
                           isset($mem['Serial Number']) && !preg_match("/^SerNum\d+$/", $serial = $mem['Serial Number']) && ($serial != 'None')) {
                            $dev->setSerial($serial);
                        }
                    }
                    $this->sys->setMemDevices($dev);
                }
            }
        }
    }

    /**
     * get the filled or unfilled (with default values) System object
     *
     * @see PSI_Interface_OS::getSys()
     *
     * @return System
     */
    final public function getSys()
    {
        $this->build();
        if (!$this->blockname || $this->blockname==='vitals') {
            $this->_ip();
        }
        if ((!$this->blockname || $this->blockname==='hardware') && (PSI_OS != 'WINNT') && !defined('PSI_EMU_HOSTNAME')) {
            $this->_dmimeminfo();
        }

        return $this->sys;
    }
}
