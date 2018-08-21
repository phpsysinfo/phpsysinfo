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
        if (PSI_USE_VHOST === true) {
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
        } else {
            if (CommonFunctions::readenv('SERVER_ADDR', $result) || CommonFunctions::readenv('LOCAL_ADDR', $result)) {
                $this->sys->setIp(preg_replace('/^::ffff:/i', '', $result));
            } else {
                $this->sys->setIp(gethostbyname($this->sys->getHostname()));
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

        return $this->sys;
    }
}
