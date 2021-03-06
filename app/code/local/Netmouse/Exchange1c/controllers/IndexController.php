<?php

/**
 * @category   Netmouse
 * @package    Netmouse_Exchange1c
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software Licence 3.0 (OSL-3.0)
 * @author     Netmouse <1c@netmouse.com.ua>
 */

class Netmouse_Exchange1c_IndexController extends Mage_Core_Controller_Front_Action

{

    public function indexAction()
    {
        $type = $this->getRequest()->getParam('type');
        $mode = $this->getRequest()->getParam('mode');

        switch ($type) {
            case "catalog":
                switch ($mode) {
                    case "checkauth":
                        //Mage::log("checkauth", null, 'exchange_1c.log', true);
                        $this->catalogCheckauthAction();
                        break;
                    case "init":
                        //Mage::log("init", null, 'exchange_1c.log', true);
                        $this->catalogInitAction();
                        break;
                    case "file":
                        //Mage::log("file", null, 'exchange_1c.log', true);
                        $this->catalogFileAction();
                        break;
                    case "import":
                        //Mage::log("import", null, 'exchange_1c.log', true);
                        $this->catalogImportAction();
                        break;
                    default:
                        echo "failure" . PHP_EOL . "Invalid exchange catalog mode parameter";
                        break;
                }

                break;
            case "sale":
                switch ($mode) {
                    case "checkauth":
                        $this->saleCheckauthAction();
                        break;
                    case "init":
                        $this->saleInitAction();
                        break;
                    case "query":
                        $this->saleQueryAction();
                        break;
                    case "success":
                        $this->saleSuccessAction();
                        break;
                    case "file":
                        $this->saleFileAction();
                        break;
                    default:
                        echo "failure" . PHP_EOL . "Invalid exchange sale mode parameter";
                        break;
                }

                break;
            default:
                echo "failure" . PHP_EOL . "Invalid exchange type parameter";
                break;
        }
    }

    protected function _getZipEnabled()
    {
        return "no";
    }

    protected function _getFileLimit()
    {
        return 1024000;
    }

    protected function _isAuthorized()
    {
        $name = Mage::getSingleton('core/session')->getSessionName();

        $value = $this->getRequest()->getCookie($name);
        if (null === $value) {
            $value = $this->getRequest()->getParam($name);
        }

        if (Mage::getSingleton('core/session')->getSessionId() == $value) {
            return true;
        }

        echo "failure" . PHP_EOL;
        echo "Session has expired";
        return false;
    }

    public function catalogCheckauthAction()
    {
        echo "success" . PHP_EOL;
        echo Mage::getSingleton('core/session')->getSessionName() . PHP_EOL;
        echo Mage::getSingleton('core/session')->getSessionId();
    }

    public function catalogInitAction()
    {
        if (!$this->_isAuthorized()) {
            return;
        }

        Mage::getModel('exchange1c/commerceML')->catalogInit();

        echo "zip=" . $this->_getZipEnabled() . PHP_EOL;
        echo "file_limit=" . $this->_getFileLimit() . PHP_EOL;
    }

    public function catalogFileAction()
    {
        if (!$this->_isAuthorized()) {
            return;
        }

        if (!Mage::getModel('exchange1c/commerceML')->catalogFile(basename($this->getRequest()->getParam('filename')))) {
            echo "failure" . PHP_EOL;
            echo "Error save catalog file";
            return;
        }

        echo "success";

    }

    public function catalogImportAction()
    {
        if (!$this->_isAuthorized()) {
            return;
        }

        $progress = Mage::getModel('exchange1c/commerceML')->addXml(basename($this->getRequest()->getParam('filename')));
        if (!$progress) {
            echo "failure" . PHP_EOL;
            echo "Error import catalog file";
            return;
        }

        if ($progress) {
            // TODO handle progress response
        }
        echo "success";
    }

    public function saleCheckauthAction()
    {
        echo "success" . PHP_EOL;
        echo Mage::getSingleton('core/session')->getSessionName() . PHP_EOL;
        echo Mage::getSingleton('core/session')->getSessionId();
    }

    public function saleInitAction()
    {
        if (!$this->_isAuthorized()) {
            return;
        }

        Mage::getModel('exchange1c/commerceML')->saleInit();

        echo "zip=" . $this->_getZipEnabled() . PHP_EOL;
        echo "file_limit=" . $this->_getFileLimit() . PHP_EOL;
    }

    public function saleQueryAction()
    {
        if (!$this->_isAuthorized()) {
            return;
        }

        $xml = Mage::getModel('exchange1c/commerceML')->saleQuery();
        if (false === $xml) {
            echo "failure" . PHP_EOL;
            echo "Error import sale file";
            return;
        }

        Mage::getModel('exchange1c/commerceML')->setSaleLastExportDate();
    }

    public function saleSuccessAction()
    {
        if (!$this->_isAuthorized()) {
            return;
        }

        Mage::getModel('exchange1c/commerceML')->setSaleLastExportDate();
    }

    public function saleFileAction()
    {
        if (!$this->_isAuthorized()) {
            return;
        }

        if (!Mage::getModel('exchange1c/commerceML')->saleFile($this->getRequest()->getParam('filename'))) {
            echo "failure" . PHP_EOL;
            echo "Error import sale file";
            return;
        }

        echo "success";
    }

}
