<?php

class Ivc_Controller_Action_Helper_Navigation extends Zend_Controller_Action_Helper_Abstract
{
    private $_view = null;

    public function direct()
    {
        $this->_view = $view = Zend_Layout::getMvcInstance()->getView();
        $this->_view->placeholder('sidebar')
             ->setPrefix('<div class="sidebar-wrapper">' . PHP_EOL . '<div class="sidebar-block">' . PHP_EOL)
             ->setSeparator('</div>' . PHP_EOL . '<div class="sidebar-block">' . PHP_EOL)
             ->setPostfix('</div>' . PHP_EOL . '</div>' . PHP_EOL);
        return $this;
    }

    public function renderSubMenu()
    {
        $this->_view->render('partials/_submenu.phtml');
    }

    public function renderBreadcrumbs()
    {
        $this->_view->render('partials/_breadcrumbs.phtml');
    }
}