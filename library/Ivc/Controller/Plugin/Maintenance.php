<?php
class Ivc_Controller_Plugin_Maintenance extends Zend_Controller_Plugin_Abstract
{
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        
        if ($this->getRequest()->getModuleName() == 'ajax'
        AND $this->getRequest()->getControllerName() == 'index'
        AND $this->getRequest()->getActionName() == 'keep-me-updated')
            return;
        $request->setActionName('index');
        $request->setModuleName('default');
        $request->setControllerName('index');
    } 
}