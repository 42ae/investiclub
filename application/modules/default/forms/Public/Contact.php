<?php

class Form_Public_Contact extends Zend_Form
{
    
    public function init()
    {
        parent::init();
            
        $this->setAttrib('id', 'contact-form');
        
        $title = new Zend_Form_Element_Select('title');
        $title->setLabel('Title')
              ->setMultiOptions(array('Mr' => 'Mr', 'Mrs' => 'Mrs'))
              ->setRequired(true)
              ->addValidator('NotEmpty', true);
              
        $firstName = new Zend_Form_Element_Text('firstName');
        $firstName->setLabel('First name')
                  ->setRequired(true)
                  ->addValidator('NotEmpty');
                  
        $lastName = new Zend_Form_Element_Text('lastName');
        $lastName->setLabel('Last name')
                 ->setRequired(true)
                 ->addValidator('NotEmpty');
                 
        $email = new Zend_Form_Element_Text('email');
        $email->setLabel('Email address')
              ->addFilter('StringToLower')
              ->setRequired(true)
              ->addValidator('NotEmpty', true)
              ->addValidator('EmailAddress');

        $message = new Zend_Form_Element_Textarea('message');
        $message->setLabel('Message')
                ->addValidator('NotEmpty', true);
              
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Contact us');
        
        $this->addElements(array($title, $firstName, $lastName, $email, $message, $submit));
    }
}