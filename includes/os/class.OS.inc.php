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
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
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
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
abstract class OS implements PSI_Interface_OS
{
    /**
     * object for error handling
     *
     * @var Error
     */
    protected $error;

    /**
     * @var System
     */
    protected $sys;

    /**
     * build the global Error object
     */
    public function __construct()
    {
        $this->error = PSI_Error::singleton();
        $this->sys = new System();
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
     * IP of the Host
     *
     * @return void
     */
    protected function ip()
    {
        if (PSI_USE_VHOST === true) {
            if ((($result = getenv('SERVER_ADDR')) || ($result = getenv('LOCAL_ADDR'))) //is server address defined
               && !strstr($result, '.') && strstr($result, ':')){ //is IPv6, quick version of preg_match('/\(([[0-9A-Fa-f\:]+)\)/', $result)
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
            if (($result = getenv('SERVER_ADDR')) || ($result = getenv('LOCAL_ADDR'))) {
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
        $this->ip();

        return $this->sys;
    }
}
