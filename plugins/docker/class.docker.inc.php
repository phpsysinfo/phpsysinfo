<?php
/**
 * Docker plugin, which displays docker informations
 *
 * @category  PHP
 * @package   PSI_Plugin_Docker
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2014 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 1.0
 * @link      http://phpsysinfo.sourceforge.net
 */

class Docker extends PSI_Plugin
{
    private $_lines;

    public function __construct($enc)
    {
        parent::__construct(__CLASS__, $enc);

        $this->_lines = array();
    }

    /**
     * get docker information
     *
     * @return array docker in array with label
     */

    private function getDocker()
    {
        $result = array();
        $i = 0;

        foreach ($this->_lines as $line) {
            if ($i > 0) {
                $buffer = preg_split("/\s\s+/", $line);
                $result[$i]['Name'] = $buffer[0];
                $result[$i]['CPUUsage'] = str_replace(',', '.',trim($buffer[1],'%'));
                preg_match('/([\d\.]+)(B|KiB|MiB|GiB|TiB|PiB)\s+\/\s+([\d\.]+)(B|KiB|MiB|GiB|TiB|PiB)/', str_replace(',', '.',trim($buffer[2])), $tmpbuf);
                switch ($tmpbuf[2]) {
                    case 'B':
                        $result[$i]['MemoryUsed'] = $tmpbuf[1];
                        break;
                    case 'KiB':
                        $result[$i]['MemoryUsed'] = 1024*$tmpbuf[1];
                        break;
                    case 'MiB':
                        $result[$i]['MemoryUsed'] = 1024*1024*$tmpbuf[1];
                        break;
                    case 'GiB':
                        $result[$i]['MemoryUsed'] = 1024*1024*1024*$tmpbuf[1];
                        break;
                    case 'TiB':
                        $result[$i]['MemoryUsed'] = 1024*1024*1024*1024*$tmpbuf[1];
                        break;
                    case 'PiB':
                        $result[$i]['MemoryUsed'] = 1024*1024*1024*1024*1025*$tmpbuf[1];
                        break;
                }
                switch ($tmpbuf[4]) {
                    case 'B':
                        $result[$i]['MemoryLimit'] = $tmpbuf[3];
                        break;
                    case 'KiB':
                        $result[$i]['MemoryLimit'] = 1024*$tmpbuf[3];
                        break;
                    case 'MiB':
                        $result[$i]['MemoryLimit'] = 1024*1024*$tmpbuf[3];
                        break;
                    case 'GiB':
                        $result[$i]['MemoryLimit'] = 1024*1024*1024*$tmpbuf[3];
                        break;
                    case 'TiB':
                        $result[$i]['MemoryLimit'] = 1024*1024*1024*1024*$tmpbuf[3];
                        break;
                    case 'PiB':
                        $result[$i]['MemoryLimit'] = 1024*1024*1024*1024*1025*$tmpbuf[3];
                        break;
                }
                $result[$i]['MemoryUsage'] = str_replace(',', '.',trim($buffer[3],'%'));
                $result[$i]['NetIO'] = trim($buffer[4]);
                $result[$i]['BlockIO'] = trim($buffer[5]);
                $result[$i]['PIDs'] = trim($buffer[6]);
            }
            $i++;
        }

        return $result;
    }

    public function execute()
    {
        $this->_lines = array();
        switch (strtolower(PSI_PLUGIN_DOCKER_ACCESS)) {
            case 'command':
                $lines = "";
                if (CommonFunctions::executeProgram('docker', 'stats --no-stream --format \'table {{.Name}}\t{{.CPUPerc}}\t{{.MemUsage}}\t{{.MemPerc}}\t{{.NetIO}}\t{{.BlockIO}}\t{{.PIDs}}\'', $lines) && !empty($lines))
                    $this->_lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
                break;
            case 'data':
                if (CommonFunctions::rfts(PSI_APP_ROOT."/data/docker.txt", $lines) && !empty($lines))
                    $this->_lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
                break;
            default:
                $this->global_error->addConfigError("execute()", "[docker] ACCESS");
                break;
        }
    }

    public function xml()
    {
        if (empty($this->_lines))
            return $this->xml->getSimpleXmlElement();

        $arrBuff = $this->getDocker();
        if (sizeof($arrBuff) > 0) {
            $docker = $this->xml->addChild("Docker");
            foreach ($arrBuff as $arrValue) {
                $item = $docker->addChild('Item');
                $item->addAttribute('Name', $arrValue['Name']);
                $item->addAttribute('CPUUsage', $arrValue['CPUUsage']);
                $item->addAttribute('MemoryUsage', $arrValue['MemoryUsage']);
                $item->addAttribute('MemoryUsed', Round($arrValue['MemoryUsed']));
                $item->addAttribute('MemoryLimit', Round($arrValue['MemoryLimit']));
                $item->addAttribute('NetIO', $arrValue['NetIO']);
                $item->addAttribute('BlockIO', $arrValue['BlockIO']);
                $item->addAttribute('PIDs', $arrValue['PIDs']);
            }
        }

        return $this->xml->getSimpleXmlElement();
    }
}
