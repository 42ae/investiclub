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
 * @package		Ivc_Model
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * Brokers model
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Model
 * @subpackage	Clubs
 */
class Model_Clubs_Brokers
{
    protected $_id;
    protected $_name;
    protected $_url;
    protected $_country;
    protected $_default = false;

    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function __set($name, $value)
    {
        $method = 'set' . $name;
        if (('mapper' == $name) || ! method_exists($this, $method)) {
            throw new Exception('Invalid broker property');
        }
        $this->$method($value);
    }

    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || ! method_exists($this, $method)) {
            throw new Exception('Invalid broker property');
        }
        return $this->$method();
    }

    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setId($_id)
    {
        $this->_id = $_id;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setName($_name)
    {
        $this->_name = $_name;
    }

    public function getUrl()
    {
        return $this->_url;
    }

    public function setUrl($_url)
    {
        $this->_url = $_url;
    }

    public function getCountry()
    {
        return $this->_country;
    }

    public function setCountry($_country)
    {
        $this->_country = $_country;
    }

    public function getDefault()
    {
        return $this->_default;
    }

    public function setDefault($_default)
    {
        $this->_default = $_default;
    }
}