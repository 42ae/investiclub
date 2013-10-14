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
 * Errors decorators used to generate errors in forms.
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Form
 * @subpackage	Decorator
 */
class Ivc_Form_Decorator_Errors extends Zend_Form_Decorator_Abstract
{   
    
    /**
     * Renders errors
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();
        $view    = $element->getView();
        if (null === $view) {
            return $content;
        }

        $errors = $element->getMessages();
        if (empty($errors)) {
            return $content;
        }

        $formErrors = $view->getHelper('formErrors');
        $formErrors->setElementStart('<label for="' . $this->getElement()->getName() . '" generated="true" class="error">')
                   ->setElementSeparator('<br />')
                   ->setElementEnd('</label>');
        
        $separator = $this->getSeparator();
        $placement = $this->getPlacement();
        $errors    = $view->formErrors($errors, $this->getOptions());

        switch ($placement) {
            case self::APPEND:
                return $content . $separator . $errors;
            case self::PREPEND:
                return $errors . $separator . $content;
        }
    }
}