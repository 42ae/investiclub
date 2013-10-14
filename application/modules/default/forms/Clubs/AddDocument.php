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
 * Club creation form
 * 
 * Renders a form that contains at least these fields:
 * Name, Creation date, Country, Currency, Club Role and Broker.
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		Form
 * @subpackage	Clubs
 */
class Form_Clubs_AddDocument extends Ivc_Form
{

    public function init()
    {
        parent::init();
        
        /*
         * Club name
         */
        $this->setName('form-create-document');
        $this->setAttrib('id', 'form-create-document');
    	$this->setAttrib('enctype', 'multipart/form-data');
    	$this->setAction('/documents/view/');


        
    	
    	$createFile = new Zend_Form_Element_Checkbox('create-file');
        $createFile->setLabel('Créer un nouveau document')
                 ->setRequired(false)
                 ->addDecorators($this->_elementDecorators);
        $this->addElement($createFile);
        
    		
        $file = new Zend_Form_Element_File('file');
    	$file->setAttrib('placeholder', 'Fichier...')
    	    ->removeDecorator('Label')
    	    ->setAttrib('style', 'margin-top: 18px')
    		->setRequired(false);
    	$this->addElement($file);
    	
    	
    		
        $title = new Zend_Form_Element_Text('title');
    	$title->setAttrib('placeholder', 'Titre du document...')
    		->setRequired(false)
    		->addDecorators($this->_elementDecorators);
    	$this->addElement($title);
    	
    	$description = new Zend_Form_Element_Text('description');
    	$description
    		->setRequired(false)
    		->setAttrib('placeholder', 'Description du document...')
    		->addValidator('NotEmpty')
    		->addDecorators($this->_elementDecorators);
        $this->addElement($description);
    		
        $datastore = new Model_Document_Datastore();
    	$tmpDest = $datastore->getDestinationDirs();
    	foreach ($tmpDest as $k => $folder) {
    	    $tmpDest[$k] = ucfirst($folder);
    	}
    	$destination = new Zend_Form_Element_Select('destination');
    	$destination->setMultiOptions($tmpDest)
    		->setRequired(true)
    		->addValidator('NotEmpty');
        $this->addElement($destination);
    }
}