<?php

class Ivc_Controller_Action_Helper_RedirectToOrigin extends Zend_Controller_Action_Helper_Abstract
{

    public function direct($message = null)
    {
        // Insertion du message dans le flash messenger
        if (!is_null($message)) {
            Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger')->addMessage($message);
        }
        
        // Redirection
        if (!isset(Zend_Registry::get('session.request')->requestUri)) {
            $gotoUrl = $this->getFrontController()->getBaseUrl();
        } else {
            $gotoUrl = Zend_Registry::get('session.request')->requestUri;
        }
        
        Zend_Controller_Action_HelperBroker::getStaticHelper('Redirector')
            ->setCode(303)->gotoUrl($gotoUrl, array('prependBase' => false));
    }

    public function setFlashMessengerNamespace($namespace)
    {
        Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger')->setNamespace($namespace);
        return $this;
    }
}