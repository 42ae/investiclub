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
 * @package		Controller
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * Documents controller
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		Controller
 */
class DocumentsController extends Zend_Controller_Action
{

	public function preDispatch()
	{
		$this->_helper->navigation()->renderBreadcrumbs();
		$this->_helper->navigation()->renderSubMenu();
	}
	
    public function init()
    {
    }

    
    public function indexAction() {
        $this->_helper->redirector('view', 'documents');
    }
    
    /**
     * Index action
     * 
     * Redirect to the view action
     */
    public function viewAction()
    {
    	$datastore = new Model_Document_Datastore();
    	$form = new Form_Clubs_AddDocument();
        
    	$this->view->messages = $this->_helper->flashMessenger->getMessages();
    	
    	$tmpDest = $datastore->getDestinationDirs();
    	if ($this->_request->isPost()) {
    		$formData = $this->_request->getPost();
    		if ($form->isValid($formData)) {
    			$res = $datastore->saveFile($form->getValues(), Ivc::getCurrentMember()->member_id);
                $messages = $datastore->getMessages();
                if ($res['result'] !== 'saved') {
                    $this->_helper->redirector('editor',
                                       'documents',
                                       null, array('file' => $res['result']));
                } else {
                    $this->_helper->flashMessenger->addMessage($messages);
    		        $this->_helper->redirector('view', 'documents');
                }
    		} else {
    			$this->view->messages = $form->getMessages();
    		}
    	}
    	
    	$model = new Model_Members_Members();
    	
    	$this->view->members = $model->listMembers();
    	$this->view->uploadForm = $form;
    	$data = $datastore->getDataStoreTree(); // IVC ACL Exception
    	
    	
    	$this->view->datastore = $data;
    	$this->view->destDirs = $datastore->getDestinationDirs();
    }
    
    public function sendfileAction()
    {
    	$fileRelPath = $this->getRequest()->getParam('file');
    	$datastore = new Model_Document_Datastore();
    	$datastore->sendFile($fileRelPath); // IVC ACL Exception
    }
    
    public function editAction() // Ajax
    {
    	/* Post with
    	FileId
    	name
    	destDir
    	comment
    	... what else ?
    	*/
   		$id = 50;
    	$destDir = "data/test/ex.txt";
    	
    	$datastore = new Model_Document_Datastore();
    	$file = $datastore->findFileById($id);
    	if ($file != null)
    	{
    		$file->setDestDir($destDir); // IVC Exception ; this will move the file once update() is executed
    		//...
    		$file->update(); // IVC Exception
    		echo "okGood";
    	}
    	else
    		echo "error file not found";
    	
    }
    
    public function removefileAction()
    {
    	$fileRelPath = $this->getRequest()->getParam('file');
    	$datastore = new Model_Document_Datastore(); 
    	$datastore->removeFile($fileRelPath); // IVC ACL Exception
    	$messages = $datastore->getMessages();
    	$this->_helper->flashMessenger->addMessage($messages);
    	//echo "file removed";
    	$this->_helper->redirector('view', 'documents');
    }
    
    public function fileinfosAction() // Ajax
    {
    	$datastore = new Model_Document_Datastore();
    	$fileRelPath = $this->getRequest()->getParam('file');
    	$fileRelPath = Ivc_Utils::base64url_decode($fileRelPath);
    	$file = $datastore->findFileByRelPath($fileRelPath);
    	echo json_encode($file->getInfos());
    	die;
    }
    
    public function editorAction()
    {
    	$datastore = new Model_Document_Datastore();
    	$fileRelPath = $this->getRequest()->getParam('file');
    	$this->view->openedFile = $fileRelPath;
    	$this->view->files = $datastore->getEditableFilesList(); // IVC ACL Exception
    	$this->view->hideSidebar = true;
    }
    
    public function getdestinationdirAction() // Ajax
    {
    	$datastore = new Model_Document_Datastore();
    	echo json_encode($datastore->getDestinationDirs()); // IVC ACL Exception
    	die;
    }
    
    public function createeditorfileAction() // Ajax
    {
    	$datastore = new Model_Document_Datastore();
    	
    	$form = new Zend_Form();
    	$form->setName('upload');
    	 
    	$description = new Zend_Form_Element_Text('description');
    	$description->setLabel('Description')
    	->setRequired(true)
    	->addValidator('NotEmpty');
    	 
    	$tmpDest = $datastore->getDestinationDirs();
    	$destination = new Zend_Form_Element_Select('destination');
    	$destination->setLabel('destination')
    	->setMultiOptions($tmpDest)
    	->setRequired(true)
    	->addValidator('NotEmpty');
    	 
    	$submit = new Zend_Form_Element_Submit('submit');
    	$submit->setLabel('Upload');
    	 
    	$form->addElements(array($description, $destination, $submit));
    	
    	if ($this->_request->isPost()) {
    		$formData = $this->_request->getPost();
    		if ($form->isValid($formData)) {
    			$uploadedData = $form->getValues();
    			//if ($datastore->createEditableFile($tmpDest[$uploadedData['destination']] . '/' . $uploadedData['file'], Ivc::getCurrentMember()->member_id)) // IVC ACL Exception
    			if ($datastore->createEditableFile($tmpDest[$uploadedData['destination']] . '/' . $uploadedData['file'], Ivc::getCurrentMember()->member_id))
    				echo Ivc_Utils::base64url_encode($tmpDest[$uploadedData['destination']] . '/' . $uploadedData['file']); // okGood
    			else
    				echo "error can't create file";
    		}
    		else 
    			echo "error invalid form";
    		die;
    	}
    }
    
    public function saveeditorfileAction() // Ajax
    {
    	$datastore = new Model_Document_Datastore();
    	if ($this->getRequest()->getParam('file'))
    		$fileName = basename($this->getRequest()->getParam('file'));
    	else
    	{
    		echo "filename error";
    		die;
    	}
    	$data = file_get_contents("php://input");
    	$datastore = new Model_Document_Datastore();
    	if ($datastore->writeFile($fileName, $data)) // IVC ACL Exception
	    	echo "okGood";
    	else
    		echo "error can't write file";
		die;
    	
    }
    
}

