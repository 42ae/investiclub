<?php
/**
 * InvestiClub
 *
 * LICENSE
 *
 * This file may not be duplicated, disclosed or reproduced in whole or in part
 * for any purpose without the express written authorization of InvestiClub.
 *
 * @category	InvestiClub
 * @package		Form
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * Edit profile form
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		Form
 * @subpackage	Users
 */
class Form_Users_CreateMessage extends Ivc_Form
{
    public function init()
    {
        parent::init();
        
        $this->setAttrib('id', 'form-create-message');
        $this->setAction('/users/messages');
        
        /*
         * Send to all members
         */
        $sendToAll = new Zend_Form_Element_Checkbox('send-to-all');
        $sendToAll->setLabel('Envoyer à tous les membres du club')
                 ->setRequired(false)
                 ->addDecorators($this->_elementDecorators);
        $this->addElement($sendToAll);

        /*
         * Recipients list
         */
        $recipients = new Zend_Form_Element_Text('recipients');
        $recipients->setAttrib('placeholder', 'Ajouter un destinataire...')
                 ->setRequired(true)
                 ->addErrorMessage("Ce champs est requis.")
                 ->addDecorators($this->_elementDecorators);
        $this->addElement($recipients);
        


        /*
         * Message subject
         */
        $subject = new Zend_Form_Element_Text('subject');
        $subject->setAttrib('required name', 'subject')
                ->setAttrib('placeholder', 'Sujet...')
                 ->setRequired(false)
                 ->addErrorMessage("Ce champs est requis.")
                 ->addDecorators($this->_elementDecorators);
        $this->addElement($subject);

        /*
         * Message content
         */
        $message = new Zend_Form_Element_Textarea('message');
        $message->setAttrib('required name', 'message')
                 ->setAttrib('style', 'display: block;margin-left: auto;margin-right: auto;')
                 ->setRequired(true)
                 ->addErrorMessage("Ce champs est requis.")
                 ->addDecorators($this->_elementDecorators);
        $this->addElement($message);
        
    }
}