<?php
/**
 * DiskLoad Plugin, which displays disks load
 *
 * @category  PHP
 * @package   PSI_Plugin_DiskLoad
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2023 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   $Id: class.diskload.inc.php 661 2023-02-10 09:13:52Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
class DiskLoad extends PSI_Plugin
{
    /**
     * variable, which holds the result before the xml is generated out of this array
     * @var array
     */
    private $_result;

    /**
     * read the data into an internal array and also call the parent constructor
     *
     * @param String $enc encoding
     */
    public function __construct($enc)
    {
        parent::__construct(__CLASS__, $enc);

        $this->_result = array();
    }

    /**
     * doing all tasks to get the required informations that the plugin needs
     * result is stored in an internal array
     *
     * @return void
     */
    public function execute()
    {
        if ((PSI_OS == 'WINNT') || (defined('PSI_EMU_HOSTNAME') && !defined('PSI_EMU_PORT'))) {
            $diskphys = array();
            $disklogi = array();
            try {
                $wmi = WINNT::getcimv2wmi();
                $diskphys = WINNT::getWMI($wmi, 'Win32_PerfFormattedData_PerfDisk_PhysicalDisk', array('Name', 'PercentIdleTime'));
                $disklogi = WINNT::getWMI($wmi, 'Win32_PerfFormattedData_PerfDisk_LogicalDisk', array('Name', 'PercentIdleTime'));

            } catch (Exception $e) {
            }
            foreach ($diskphys as $disk_item) if (isset($disk_item['Name']) && (trim($disk_item['Name']) !== '') && (trim($disk_item['Name']) !== '_Total') && isset($disk_item['PercentIdleTime']) && (trim($disk_item['PercentIdleTime']) !== '')) {
                $this->_result[] = $disk_item;
            }
            foreach ($disklogi as $disk_item) if (isset($disk_item['Name']) && (trim($disk_item['Name']) !== '') && (trim($disk_item['Name']) !== '_Total') && isset($disk_item['PercentIdleTime']) && (trim($disk_item['PercentIdleTime']) !== '')) {
                $this->_result[] = $disk_item;
            }
        }

    }

   /**
     * generates the XML content for the plugin
     *
     * @return SimpleXMLElement entire XML content for the plugin
     */
    public function xml()
    {
        foreach ($this->_result as $disk_item) {
            $xmldiskload_disk = $this->xml->addChild("Disk");
            $xmldiskload_disk ->addAttribute('Name', trim($disk_item['Name']));
            $xmldiskload_disk ->addAttribute('Load', min(max(100 - trim($disk_item['PercentIdleTime']), 0), 100));
        }

        return $this->xml->getSimpleXmlElement();
    }
}
