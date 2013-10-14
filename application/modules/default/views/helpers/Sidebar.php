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
 * @package		View
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * Sidebar View Helper
 * 
 * Generates a sidebar
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		View
 * @subpackage	Helper
 */
class Zend_View_Helper_Sidebar extends Zend_View_Helper_Abstract
{

    public function sidebar()
    {
        $this->view->render('partials/_sidebar.phtml');
        return $this->view->placeholder('sidebar');
    }
}