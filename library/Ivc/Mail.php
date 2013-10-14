<?php
/**
 * InvestiClub
 *
 * LICENSE
 *
 * This file may not be duplicated, disclosed or reproduced in whole or in part
 * for any purpose without the express written authorization of InvestiClub.
 *
 * @category	Ivc
 * @package		Ivc_Mail
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * Mail manager
 * 
 * Handle e-mail sending and e-mail template 
 *  
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Mail
 */

class Ivc_Mail
{
    
    const SIGNUP_ACTIVATION          = "signup-activation";
    const JOIN_CLUB_REQUEST          = "join-club-request";
    const JOIN_CLUB_REQUEST_NEW_USER = "join-club-request-new-user";
    const JOIN_CLUB_CONFIRMATION     = "join-club-confirmation";
    const REQUEST_JOIN_CLUB          = "request-join-club";
    const CONTACT                    = "contact";
    
    /**
     * Zend_View object
     *
     * @var Zend_View
     */
    private $_viewSubject;
    
    /**
     * Zend_View object
     *
     * @var Zend_View
     */
    private $_viewContent;

    /**
     * Variable registry for template values
     */
    protected $templateVariables = array();

    /**
     * Template name
     */
    protected $templateName;

    /**
     * Zend_Mail instance
     */
    protected $_mail;
     
    /**
     * Email recipient
     */
    protected $recipient;
    
    /**
     * __construct
     *
     * Set default options
     *
     */
    public function __construct()
    {
        $this->_mail = new Zend_Mail();
        $this->_viewSubject = new Zend_View();
        $this->_viewContent = new Zend_View();
    }

    /**
     * Set variables for use in the templates
     *
     * Magic function stores the value put in any variable in this class for
     * use later when creating the template
     *
     * @param string $name  The name of the variable to be stored
     * @param mixed  $value The value of the variable
     */
    public function __set($name, $value)
    {
        $this->templateVariables[$name] = $value;
    }

    /**
     * Set the template file to use
     *
     * @param string $filename Template filename
     */
    public function setTemplate($filename)
    {
        $this->templateName = $filename;
    }
    
    /**
     * Set the recipient address for the email message
     * 
     * @param string $email Email address
     */
    public function setRecipient($email)
    {
        $this->recipient = $email;
    }

    public function setDefaultFrom($email, $name)
    {
        $this->_mail->setDefaultFrom($email, $name);
    }

    /**
     * Send the constructed email
     *
     * @todo Add from name
     */
    public function send()
    {
        $config = Zend_Registry::get('config');
        $emailPath = $config->email->templatePath;
        $templateVars = $config->email->template->toArray();

        foreach ($templateVars as $key => $value)
        {
            if (!array_key_exists($key, $this->templateVariables)) {
                $this->{$key} = $value;
            }
        }
        

        $viewSubject = $this->_viewSubject->setScriptPath($emailPath);
        foreach ($this->templateVariables as $key => $value) {
            $viewSubject->{$key} = $value;
        }
        try {
            $subject = $viewSubject->render($this->templateName . '.subj.tpl');
        } catch (Zend_View_Exception $e) {
            //@todo: log error template
            $subject = false;
        }
        

        $viewContent = $this->_viewContent->setScriptPath($emailPath);
        foreach ($this->templateVariables as $key => $value) {
            $viewContent->{$key} = $value;
        }
        try {
            $html = $viewContent->render($this->templateName . '.tpl');
        } catch (Zend_View_Exception $e) {
            //@todo: log error template
            $html = false;
        }

        $this->_mail->addTo($this->recipient);
        $this->_mail->setSubject($subject);
        $this->_mail->setBodyHtml($html);
        
        //Send email
        // $transport = $config->email->transport;
//        if ($transport == 'development')
//        {
//            $tr = new Zend_Mail_Transport_Smtp();
//            $mail->send($tr);
//            return;
//        }
              //return;
        $this->_mail->send();
    }
}