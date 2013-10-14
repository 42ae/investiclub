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
 * @package		Ivc_Bootstrap
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * HTTP Headers resource plugin
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Application
 * @subpackage	Resource
 */
class Ivc_Application_Resource_Header extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var Zend_Controller_Response_Http
     */
    protected $_response;

    /**
     * Defined by Zend_Application_Resource_Resource
     *
     * @return Zend_Controller_Response_Http
     */
    public function init()
    {
        $response = $this->getHeader();
        $front = Zend_Controller_Front::getInstance();
        // Assign the response to the front controller
        $front->setResponse($response);
        return $response;
    }

    /**
     * Retrieve controller response http object
     *
     * @return Zend_Controller_Response_Http
     */
    public function getHeader()
    {
        if (null === $this->_response) {
            // Create a new HTTP response object
            $this->_response = new Zend_Controller_Response_Http();
            // Set the response headers : the value below are only set for example.
            $this->_response->setHeader('language', 'en')
                            ->setHeader('content-language', 'en')
                            ->setHeader('Content-Type', 'text/html; charset=utf-8')
                            ->setHeader('Accept-Encoding', 'gzip, deflate')
                            ->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT')
                            ->setHeader('Expires', 'max-age=200', true)
                            ->setHeader('Cache-Control', 'public', true)
                            //->setHeader('Cache-Control', 'no-store, private, no-cache, must-revalidate', false) // no cache
                            //->setHeader('Cache-Control', 'pre-check=0, post-check=0, max-age=0, max-stale = 0', false) // no cache         
                            //->setHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT', false) // date in the past      
                            ->setHeader('Pragma', '', true);
        }
        return $this->_response;
    }
}