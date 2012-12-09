<?php 
/**
 * XML Generation class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_XML
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.XML.inc.php 573 2012-05-02 13:46:31Z jacky672 $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * class for generation of the xml
 *
 * @category  PHP
 * @package   PSI_XML
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class XML
{
    /**
     * Sysinfo object where the information retrieval methods are included
     *
     * @var PSI_Interface_OS
     */
    private $_sysinfo;
    
    /**
     * @var System
     */
    private $_sys = null;
    
    /**
     * xml object with the xml content
     *
     * @var SimpleXMLExtended
     */
    private $_xml;
    
    /**
     * object for error handling
     *
     * @var Error
     */
    private $_errors;
    
    /**
     * array with all enabled plugins (name)
     *
     * @var array
     */
    private $_plugins;
    
    /**
     * plugin name if pluginrequest
     *
     * @var string
     */
    private $_plugin = '';
    
    /**
     * generate a xml for a plugin or for the main app
     *
     * @var boolean
     */
    private $_plugin_request = false;
    
    /**
     * generate the entire xml with all plugins or only a part of the xml (main or plugin)
     *
     * @var boolean
     */
    private $_complete_request = false;
    
    /**
     * doing some initial tasks
     * - generate the xml structure with the right header elements
     * - get the error object for error output
     * - get a instance of the sysinfo object
     *
     * @param boolean $complete   generate xml with all plugins or not
     * @param string  $pluginname name of the plugin
     *
     * @return void
     */
    public function __construct($complete = false, $pluginname = "")
    {
        $this->_errors = Error::singleton();
        if ($pluginname == "") {
            $this->_plugin_request = false;
            $this->_plugin = '';
        } else {
            $this->_plugin_request = true;
            $this->_plugin = $pluginname;
        }
        if ($complete) {
            $this->_complete_request = true;
        } else {
            $this->_complete_request = false;
        }
        $os = PHP_OS;
        $this->_sysinfo = new $os();
        $this->_plugins = CommonFunctions::getPlugins();
        $this->_xmlbody();
    }
    
    /**
     * generate common information
     *
     * @return void
     */
    private function _buildVitals()
    {
        $vitals = $this->_xml->addChild('Vitals');
        $vitals->addAttribute('Hostname', $this->_sys->getHostname());
        $vitals->addAttribute('IPAddr', $this->_sys->getIp());
        $vitals->addAttribute('Kernel', $this->_sys->getKernel());
        $vitals->addAttribute('Distro', $this->_sys->getDistribution());
        $vitals->addAttribute('Distroicon', $this->_sys->getDistributionIcon());
        $vitals->addAttribute('Uptime', $this->_sys->getUptime());
        $vitals->addAttribute('Users', $this->_sys->getUsers());
        $vitals->addAttribute('LoadAvg', $this->_sys->getLoad());
        if ($this->_sys->getLoadPercent() !== null) {
            $vitals->addAttribute('CPULoad', $this->_sys->getLoadPercent());
        }
    }
    
    /**
     * generate the network information
     *
     * @return void
     */
    private function _buildNetwork()
    {
        $network = $this->_xml->addChild('Network');
        $hideDevices = preg_split("/[\s]?,[\s]?/", PSI_HIDE_NETWORK_INTERFACE, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($this->_sys->getNetDevices() as $dev) {
            if (!in_array(trim($dev->getName()), $hideDevices)) {
                $device = $network->addChild('NetDevice');
                $device->addAttribute('Name', $dev->getName());
                $device->addAttribute('RxBytes', $dev->getRxBytes());
                $device->addAttribute('TxBytes', $dev->getTxBytes());
                $device->addAttribute('Err', $dev->getErrors());
                $device->addAttribute('Drops', $dev->getDrops());
                if ( defined('PSI_SHOW_NETWORK_INFOS') && PSI_SHOW_NETWORK_INFOS && ($dev->getInfo())) 
                    $device->addAttribute('Info', $dev->getInfo());
            }
        }
    }
    
    /**
     * generate the hardware information
     *
     * @return void
     */
    private function _buildHardware()
    {
        $dev = new HWDevice();
        $hardware = $this->_xml->addChild('Hardware');
        $pci = $hardware->addChild('PCI');
        foreach (System::removeDupsAndCount($this->_sys->getPciDevices()) as $dev) {
            $tmp = $pci->addChild('Device');
            $tmp->addAttribute('Name', $dev->getName());
            $tmp->addAttribute('Count', $dev->getCount());
        }
        $usb = $hardware->addChild('USB');
        foreach (System::removeDupsAndCount($this->_sys->getUsbDevices()) as $dev) {
            $tmp = $usb->addChild('Device');
            $tmp->addAttribute('Name', $dev->getName());
            $tmp->addAttribute('Count', $dev->getCount());
        }
        $ide = $hardware->addChild('IDE');
        foreach (System::removeDupsAndCount($this->_sys->getIdeDevices()) as $dev) {
            $tmp = $ide->addChild('Device');
            $tmp->addAttribute('Name', $dev->getName());
            $tmp->addAttribute('Count', $dev->getCount());
            if ($dev->getCapacity() !== null) {
                $tmp->addAttribute('Capacity', $dev->getCapacity());
            }
        }
        $scsi = $hardware->addChild('SCSI');
        foreach (System::removeDupsAndCount($this->_sys->getScsiDevices()) as $dev) {
            $tmp = $scsi->addChild('Device');
            $tmp->addAttribute('Name', $dev->getName());
            $tmp->addAttribute('Count', $dev->getCount());
            if ($dev->getCapacity() !== null) {
                $tmp->addAttribute('Capacity', $dev->getCapacity());
            }
        }

        $cpu = $hardware->addChild('CPU');
        foreach ($this->_sys->getCpus() as $oneCpu) {
            $tmp = $cpu->addChild('CpuCore');
            $tmp->addAttribute('Model', $oneCpu->getModel());
            if ($oneCpu->getCpuSpeed() !== 0) {
                $tmp->addAttribute('CpuSpeed', $oneCpu->getCpuSpeed());
            }
            if ($oneCpu->getTemp() !== null) {
                $tmp->addAttribute('CpuTemp', $oneCpu->getTemp());
            }
            if ($oneCpu->getBusSpeed() !== null) {
                $tmp->addAttribute('BusSpeed', $oneCpu->getBusSpeed());
            }
            if ($oneCpu->getCache() !== null) {
                $tmp->addAttribute('Cache', $oneCpu->getCache());
            }
            if ($oneCpu->getVirt() !== null) {
                $tmp->addAttribute('Virt', $oneCpu->getVirt());
            }
            if ($oneCpu->getBogomips() !== null) {
                $tmp->addAttribute('Bogomips', $oneCpu->getBogomips());
            }
            if ($oneCpu->getLoad() !== null) {
                $tmp->addAttribute('Load', $oneCpu->getLoad());
            }
        }
    }
    
    /**
     * generate the memory information
     *
     * @return void
     */
    private function _buildMemory()
    {
        $memory = $this->_xml->addChild('Memory');
        $memory->addAttribute('Free', $this->_sys->getMemFree());
        $memory->addAttribute('Used', $this->_sys->getMemUsed());
        $memory->addAttribute('Total', $this->_sys->getMemTotal());
        $memory->addAttribute('Percent', $this->_sys->getMemPercentUsed());
        $details = $memory->addChild('Details');
        if ($this->_sys->getMemApplication() !== null) {
            $details->addAttribute('App', $this->_sys->getMemApplication());
            $details->addAttribute('AppPercent', $this->_sys->getMemPercentApplication());
        }
        if ($this->_sys->getMemBuffer() !== null) {
            $details->addAttribute('Buffers', $this->_sys->getMemBuffer());
            $details->addAttribute('BuffersPercent', $this->_sys->getMemPercentBuffer());
        }
        if ($this->_sys->getMemCache() !== null) {
            $details->addAttribute('Cached', $this->_sys->getMemCache());
            $details->addAttribute('CachedPercent', $this->_sys->getMemPercentCache());
        }
        if (count($this->_sys->getSwapDevices()) > 0) {
            $swap = $memory->addChild('Swap');
            $swap->addAttribute('Free', $this->_sys->getSwapFree());
            $swap->addAttribute('Used', $this->_sys->getSwapUsed());
            $swap->addAttribute('Total', $this->_sys->getSwapTotal());
            $swap->addAttribute('Percent', $this->_sys->getSwapPercentUsed());
            $i = 1;
            foreach ($this->_sys->getSwapDevices() as $dev) {
                $swapMount = $swap->addChild('Mount');
                $this->_fillDevice($swapMount, $dev, $i++);
            }
        }
    }
    
    /**
     * fill a xml element with atrributes from a disk device
     *
     * @param SimpleXmlExtended $mount Xml-Element
     * @param DiskDevice        $dev   DiskDevice
     * @param Integer           $i     counter
     *
     * @return Void
     */
    private function _fillDevice($mount, $dev, $i)
    {
        $mount->addAttribute('MountPointID', $i);
        $mount->addAttribute('FSType', $dev->getFsType());
        $mount->addAttribute('Name', $dev->getName());
        $mount->addAttribute('Free', sprintf("%.0f", $dev->getFree()));    
        $mount->addAttribute('Used', sprintf("%.0f", $dev->getUsed()));
        $mount->addAttribute('Total', sprintf("%.0f", $dev->getTotal()));
        $mount->addAttribute('Percent', $dev->getPercentUsed());
        if (PSI_SHOW_MOUNT_OPTION === true) {
            if ($dev->getOptions() !== null) {
                $mount->addAttribute('MountOptions', preg_replace("/,/",", ",$dev->getOptions()));
            }
        }
        if ($dev->getPercentInodesUsed() !== null) {
            $mount->addAttribute('Inodes', $dev->getPercentInodesUsed());
        }
        if (PSI_SHOW_MOUNT_POINT === true) {
            $mount->addAttribute('MountPoint', $dev->getMountPoint());
        }
    }
    
    /**
     * generate the filesysteminformation
     *
     * @return void
     */
    private function _buildFilesystems()
    {
        $hideMounts = $hideFstypes = $hideDisks = array();
        $i = 1;
        if (PSI_HIDE_MOUNTS !== "") {
            $hideMounts = preg_split('/,/', PSI_HIDE_MOUNTS, -1, PREG_SPLIT_NO_EMPTY);
        }
        if (PSI_HIDE_FS_TYPES !== "") {
            $hideFstypes = preg_split('/,/', PSI_HIDE_FS_TYPES, -1, PREG_SPLIT_NO_EMPTY);
        }
        if (PSI_HIDE_DISKS !== "") {
            $hideDisks = preg_split('/,/', PSI_HIDE_DISKS, -1, PREG_SPLIT_NO_EMPTY);
        }
        $fs = $this->_xml->addChild('FileSystem');
        foreach ($this->_sys->getDiskDevices() as $disk) {
            if (!in_array($disk->getMountPoint(), $hideMounts, true) && !in_array($disk->getFsType(), $hideFstypes, true) && !in_array($disk->getName(), $hideDisks, true)) {
                $mount = $fs->addChild('Mount');
                $this->_fillDevice($mount, $disk, $i++);
            }
        }
    }
    
    /**
     * generate the motherboard information
     *
     * @return void
     */
    private function _buildMbinfo()
    {
        $mbinfo = $this->_xml->addChild('MBInfo');
        if (PSI_MBINFO || PSI_HDDTEMP) {
            $temp = $mbinfo->addChild('Temperature');
            if (PSI_MBINFO) {
                $mbinfoclass = PSI_SENSOR_PROGRAM;
                $mbinfo_data = new $mbinfoclass();
                $mbinfo_detail = $mbinfo_data->getMBInfo();
                foreach ($mbinfo_detail->getMbTemp() as $dev) {
                    $item = $temp->addChild('Item');
                    $item->addAttribute('Label', $dev->getName());
                    $item->addAttribute('Value', $dev->getValue());
                    $item->addAttribute('Max', $dev->getMax());
                }
            }
            if (PSI_HDDTEMP) {
                $hddtemp = new HDDTemp();
                $hddtemp_data = $hddtemp->getMBInfo();
                foreach ($hddtemp_data->getMbTemp() as $dev) {
                    $item = $temp->addChild('Item');
                    $item->addAttribute('Label', $dev->getName());
                    $item->addAttribute('Value', $dev->getValue());
                    $item->addAttribute('Max', $dev->getMax());
                }
            }
        }
        if (PSI_MBINFO) {
            $fan = $mbinfo->addChild('Fans');
            foreach ($mbinfo_detail->getMbFan() as $dev) {
                $item = $fan->addChild('Item');
                $item->addAttribute('Label', $dev->getName());
                $item->addAttribute('Value', $dev->getValue());
                $item->addAttribute('Min', $dev->getMin());
            }
        }
        if (PSI_MBINFO) {
            $volt = $mbinfo->addChild('Voltage');
            foreach ($mbinfo_detail->getMbVolt() as $dev) {
                $item = $volt->addChild('Item');
                $item->addAttribute('Label', $dev->getName());
                $item->addAttribute('Value', $dev->getValue());
                $item->addAttribute('Min', $dev->getMin());
                $item->addAttribute('Max', $dev->getMax());
            }
        }
    }
    
    /**
     * generate the ups information
     *
     * @return void
     */
    private function _buildUpsinfo()
    {
        $upsinfo = $this->_xml->addChild('UPSInfo');
        if(PSI_UPS_APCUPSD_CGI_ENABLE) {
            $upsinfo->addAttribute('ApcupsdCgiLinks', true);
        }
        if (PSI_UPSINFO) {
            $upsinfoclass = PSI_UPS_PROGRAM;
            $upsinfo_data = new $upsinfoclass();
            $upsinfo_detail = $upsinfo_data->getUPSInfo();
            foreach ($upsinfo_detail->getUpsDevices() as $ups) {
                $item = $upsinfo->addChild('UPS');
                $item->addAttribute('Name', $ups->getName());
                $item->addAttribute('Model', $ups->getModel());
                $item->addAttribute('Mode', $ups->getMode());
                $item->addAttribute('StartTime', $ups->getStartTime());
                $item->addAttribute('Status', $ups->getStatus());
                if ($ups->getTemperatur() !== null) {
                    $item->addAttribute('Temperature', $ups->getTemperatur());
                }
                if ($ups->getOutages() !== null) {
                    $item->addAttribute('OutagesCount', $ups->getOutages());
                }
                if ($ups->getLastOutage() !== null) {
                    $item->addAttribute('LastOutage', $ups->getLastOutage());
                }
                if ($ups->getLastOutageFinish() !== null) {
                    $item->addAttribute('LastOutageFinish', $ups->getLastOutageFinish());
                }
                if ($ups->getLineVoltage() !== null) {
                    $item->addAttribute('LineVoltage', $ups->getLineVoltage());
                }
                if ($ups->getLoad() !== null) {
                    $item->addAttribute('LoadPercent', $ups->getLoad());
                }
                if ($ups->getBatteryVoltage() !== null) {
                    $item->addAttribute('BatteryVoltage', $ups->getBatteryVoltage());
                }
                if ($ups->getBatterCharge() !== null) {
                    $item->addAttribute('BatteryChargePercent', $ups->getBatterCharge());
                }
                if ($ups->getTimeLeft() !== null) {
                    $item->addAttribute('TimeLeftMinutes', $ups->getTimeLeft());
                }
            }
        }
    }
    
    /**
     * generate the xml document
     *
     * @return void
     */
    private function _buildXml()
    {
        if (!$this->_plugin_request || $this->_complete_request) {
            if ($this->_sys === null) {
                $this->_sys = $this->_sysinfo->getSys();
            }
            $this->_buildVitals();
            $this->_buildNetwork();
            $this->_buildHardware();
            $this->_buildMemory();
            $this->_buildFilesystems();
            $this->_buildMbinfo();
            $this->_buildUpsinfo();
        }
        $this->_buildPlugins();
        $this->_xml->combinexml($this->_errors->errorsAddToXML($this->_sysinfo->getEncoding()));
    }
    
    /**
     * get the xml object
     *
     * @return string
     */
    public function getXml()
    {
        $this->_buildXml();
        return $this->_xml->getSimpleXmlElement();
    }
    
    /**
     * include xml-trees of the plugins to the main xml
     *
     * @return void
     */
    private function _buildPlugins()
    {
        $pluginroot = $this->_xml->addChild("Plugins");
        if (($this->_plugin_request || $this->_complete_request) && count($this->_plugins) > 0) {
            $plugins = array();
            if ($this->_complete_request) {
                $plugins = $this->_plugins;
            }
            if ($this->_plugin_request) {
                $plugins = array($this->_plugin);
            }
            foreach ($plugins as $plugin) {
                $object = new $plugin($this->_sysinfo->getEncoding());
                $object->execute();
                $pluginroot->combinexml($object->xml());
            }
        }
    }
    
    /**
     * build the xml structure where the content can be inserted
     *
     * @return void
     */
    private function _xmlbody()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $root = $dom->createElement("tns:phpsysinfo");
        $root->setAttribute('xmlns:tns', 'http://phpsysinfo.sourceforge.net/');
        $root->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $root->setAttribute('xsi:schemaLocation', 'http://phpsysinfo.sourceforge.net/phpsysinfo3.xsd');
        $dom->appendChild($root);
        $this->_xml = new SimpleXMLExtended(simplexml_import_dom($dom), $this->_sysinfo->getEncoding());
        
        $generation = $this->_xml->addChild('Generation');
        $generation->addAttribute('version', CommonFunctions::$PSI_VERSION_STRING);
        $generation->addAttribute('timestamp', time());
        $options = $this->_xml->addChild('Options');
        $options->addAttribute('tempFormat', defined('PSI_TEMP_FORMAT') ? PSI_TEMP_FORMAT : 'c');
        $options->addAttribute('byteFormat', defined('PSI_BYTE_FORMAT') ? PSI_BYTE_FORMAT : 'auto_binary');
        $options->addAttribute('refresh', defined('PSI_REFRESH') ? PSI_REFRESH : 0);
        $options->addAttribute('showPickListTemplate', defined('PSI_SHOW_PICKLIST_TEMPLATE') ? (PSI_SHOW_PICKLIST_TEMPLATE ? 'true' : 'false') : 'false');
        $options->addAttribute('showPickListLang', defined('PSI_SHOW_PICKLIST_LANG') ? (PSI_SHOW_PICKLIST_LANG ? 'true' : 'false') : 'false');
        $plug = $this->_xml->addChild('UsedPlugins');
        if ($this->_complete_request && count($this->_plugins) > 0) {
            foreach ($this->_plugins as $plugin) {
                $plug->addChild('Plugin')->addAttribute('name', $plugin);
            }
        } elseif ($this->_plugin_request && count($this->_plugins) > 0) {
            $plug->addChild('Plugin')->addAttribute('name', $this->_plugin);
        }
    }
}
?>
