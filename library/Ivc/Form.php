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
 * @package		Ivc_Form
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * 
 * Form extends Zend_Form and set default decorators to a form.
 * This class also set the prefix paths needed in the current view.
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Form
 */
class Ivc_Form extends Zend_Form
{
    const VALIDATOR   = 'validate';
    const DATE_FORMAT = "yyyy-MM-dd";
 
    /**
     * The default decorators for Display Groups.
     *
     * @var array
     */ 
    protected $_displayGroupsDecorators = array(
        'FormElements',
		array('HtmlTag', array('tag' => 'tbody')),
		);

    /**
     * The default decorators for jQuery elements.
     *
     * @var array
     */
    protected $_formJQueryElements = array(
        array('UiWidgetElement', array('tag' => '')),
        array('Errors'),
        array('Description', array('tag' => 'span')),
        array('HtmlTag', array('tag' => 'td', 'class' => 'element')),
        array('Label', array('tag' => 'th')),
        array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    );

    
    /**
     * The default decorators for form elements.
     *
     * @var array
     */
    protected $_elementDecorators = array(
        'ViewHelper',
        'Errors',
        array('HtmlTag', array('tag' => 'td', 'class' => 'element')),
        array('Label', array('tag' => 'th')),
        array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    );

    /**
     * The default decorators for form buttons.
     *
     * @var array
     */
    protected $_buttonDecorators = array(
        array('ViewScript', array('viewScript' => '_submitButton.phtml', 'class' => 'buttons')),
        array('HtmlTag', array('tag' => 'td', 'colspan' => '2', 'class' => 'button-element')),
    );

    /**
     * The default decorators for the form.
     *
     * @var array
     */
    protected $_formDecorators = array(
        'FormElements',
        array('HtmlTag', array('tag' => 'table')),
        'Form'
    ); 
    
    public function init()
    {
        $this->setMethod('post')
             ->setEnctype(self::ENCTYPE_URLENCODED)
             ->setAttrib('class', 'form')
             ->setAttrib('accept-charset', 'UTF-8')
             ->clearDecorators()
             ->addPrefixPath('Ivc_Form_Element', 'Ivc/Form/Element/', self::ELEMENT)
             ->addElementPrefixPath('Ivc_Validate', 'Ivc/Validate', self::VALIDATOR)
             ->addElementPrefixPath('Ivc_Form_Decorator', 'Ivc/Form/Decorator', self::DECORATOR);

        $view = $this->getView();
        $view->addScriptPath(APPLICATION_PATH . "/../library/Ivc/Form/Decorator/ViewScript");       
    }
}