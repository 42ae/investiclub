<?php

class Ivc_File extends Ivc_Core
{
	private $rootPath = null;
	private $relPath = null;
	private $oldRelPath = null;
	private $name = null;
	private $fileId = null;
	private $size = null;
	private $type = null;
	private $creationDate = null;
	private $creatorId = null;
	
	private $_scope;
	public function __construct(array $options = null)
	{
		$this->_scope = Model_Document_Datastore::SCOPE_CLUB;
		$ivcRootPath = '/var/www/ivc/data/datastore/';
		
		$subDir = $this->getClubId() % 100;
		$this->_rootPath = $ivcRootPath . $this->_scope . "/" . $subDir . "/" . $this->getClubId() . "/";
		
		if (is_array($options))
			$this->setOptions($options);
		
		//$this->setAclRules();
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
    
    private function getMapper()
    {
    	$mapper = new Model_Document_DatastoreMapper($this->_scope, $this->getClubId());
    	return ($mapper);
    }
    
	/**
	 * @return the $path
	 */
	public function getPath() {
		return $this->_rootPath . $this->relPath;
	}
	
	/**
	 * @return the $path
	 */
	public function getRelPath() {
		return $this->relPath;
	}
	
	/**
	 * @return the $path
	 */
	public function getUrl() {
		return Ivc_Utils::base64url_encode($this->relPath);
	}

	/**
	 * @return the $name
	 */
	public function getName() {
		return basename($this->relPath);
	}

	/**
	 * @return the $fileId
	 */
	public function getFileId() {
		return $this->fileId;
	}

	/**
	 * @return the $size
	 */
	public function getSize() {
		return $this->size;
	}

	/**
	 * @return the $type
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return the $creationDate
	 */
	public function getCreationDate() {
		return $this->creationDate;
	}

	/**
	 * @return the $creatorId
	 */
	public function getCreatorId() {
		return $this->creatorId;
	}
	
	/**
	 * @param field_type $path
	 */
	public function setRelPath($relPath) {
		//if (Ivc_Utils::base64url_decode($relPath, true))
		//	$relPath = Ivc_Utils::base64url_decode($relPath);
		
		if ($this->oldRelPath === null)
			$this->oldRelPath = $relPath;
		$this->relPath = $relPath;
		return ($this);
	}
	
	public function setDestDir($destDir)
	{
		$this->setRelPath($destDir . $this->getName());
		return ($this);
	}

	/**
	 * @param field_type $name
	 */
	public function setName($name) {
		if ($name == basename($this->relPath))
			return ($this);
		
		$tmp = substr($this->relPath, 0, (-1 * strlen(basename($this->relPath))));
		$this->setRelPath($tmp . $name);
		return ($this);
	}

	/**
	 * @param field_type $fileId
	 */
	public function setFileId($fileId) {
		$this->fileId = $fileId;
		return ($this);
	}

	/**
	 * @param field_type $size
	 */
	public function setSize($size) {
		$this->size = $size;
		return ($this);
	}

	/**
	 * @param field_type $type
	 */
	public function setType($type) {
		$this->type = $type;
		return ($this);
	}

	/**
	 * @param field_type $creationDate
	 */
	public function setCreationDate($creationDate) {
		$this->creationDate = $creationDate;
		return ($this);
	}

	/**
	 * @param field_type $creatorId
	 */
	public function setCreatorId($creatorId) {
		$this->creatorId = $creatorId;
		return ($this);
	}

	public function update()
	{
		if ($this->oldRelPath != $this->relPath)
		{
			echo "rename file";
			if (!rename($this->_rootPath . $this->oldRelPath,  $this->getPath()))
			{
				echo "Error: could not move file " . $this->_rootPath . $this->oldRelPath . " to " . $this->getPath() . "<br />";
				return;
			}
		}
		$data = array("path" => $this->getRelPath(),
                	  "datastore_files_meta_id" => $this->getFileId(),
               		  "size" => $this->getSize(),
           			  "type" => $this->getType(),
           			  "create_date" => $this->getCreationDate(),
           			  "create_member_id" => $this->getCreatorId());
		$this->getMapper()->updateFileMeta(Model_Document_Datastore::SCOPE_CLUB, $data);
	}
    
	
	function getSizeHuman($decimals = 2)
	{
		$sz = 'BKMGTP';
		$bytes = $this->getSize();
		$factor = floor((strlen($bytes) - 1) / 3);
		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
	}
	
	public function getInfos()
	{
		$model = new Model_Members_Members();
		$memberList = $model->listMembers();
		
		return (array('name' => $this->getName(),
    					'size' => $this->getSize(),
						'sizeH' => $this->getSizeHuman(),
    					'type' => $this->getType(),
    					'relPath' => $this->getRelPath(),
						'url' => $this->getUrl(),
    					'creationDate' => $this->getCreationDate(),
    					'creatorFullName' => $memberList->getUserByMemberId($this->getCreatorId())->getFullName()));
	}
	
}

class Ivc_Fs
{
	public $name;
	public $path;
	public $content;
	
	public function __construct($path)
	{
		$this->content = array();
		$this->name = basename($path);
		$this->path = $path;
	}
	
	public function setProperties($tab)
	{
		$this->name = $tab['name'];
	}
	
	public function getName()
	{
		return ($this->name);
	}
	
	public function getContent()
	{
		return ($this->content);
	}
	
	public function addFile($data)
	{
		$file = new Ivc_File();
		$file->setoptions($data);
		$this->content[] = $file;
	}
	public function addDirectory($ref)
	{
		$this->content[] = $ref;
	}
}

class Model_Document_Datastore extends Ivc_Core
{
	const SCOPE_USER = "users";
	const SCOPE_CLUB = "clubs";
	
	private $_scope = null;
	private $_id = null;
	private $_curDate = null;
	private $_mapper = null;
	private $_rootPath = null;
	private $_subDir = null;
	
	private $_getFileByTypeTmp = null;
	
	public function __construct(array $options = null)
	{
		$this->_scope = self::SCOPE_CLUB;
		$this->_curDate = date("Y-m-d");
		$ivcRootPath = '/var/www/ivc/data/datastore/';

		/*
		if ($this->_scope === self::SCOPE_CLUB)
			if (Ivc::getCurrentUser()->hasClub())
				$this->_id = Ivc::getCurrentMember()->getClub()->club_id;
		*/
		//$this->_id = 1;
		if (is_array($options))
			$this->setOptions($options);

		$this->_subDir = $this->getClubId() % 100;
		$this->_rootPath = $ivcRootPath . $this->_scope . "/" . $this->_subDir . "/" . $this->getClubId() . "/";
			
        $this->setAclRules();
	}
	
	private function setAclRules()
    {
        $acl = Zend_Registry::get('Ivc_Acl');
        if ($acl->has($this->getResourceId())) // if already set return
            return;
        $acl->add(new Zend_Acl_Resource($this->getResourceId()));

        // Set Guest and User rules
        $acl->deny(Ivc_Acl::GUEST, $this, null);
        $acl->deny(Ivc_Acl::USER, $this, null);
        // Set club default rules
        if ($this->_scope === self::SCOPE_CLUB && $this->getClubId())
        {
            $acl->allow(Ivc_Acl::CLUB_MEMBER . $this->getClubId(), $this, 'list')
            	->allow(Ivc_Acl::CLUB_MEMBER . $this->getClubId(), $this, 'download')
            	->allow(Ivc_Acl::CLUB_MEMBER . $this->getClubId(), $this, 'writeFile')
            	->allow(Ivc_Acl::CLUB_MEMBER . $this->getClubId(), $this, 'move')
            	->allow(Ivc_Acl::CLUB_MEMBER . $this->getClubId(), $this, 'remove')
            	->allow(Ivc_Acl::CLUB_MEMBER . $this->getClubId(), $this, 'upload')
            	->allow(Ivc_Acl::CLUB_MEMBER . $this->getClubId(), $this, 'createEditable')
            	->allow(Ivc_Acl::CLUB_MEMBER . $this->getClubId(), $this, 'generate');
            	//->allow(Ivc_Acl::CLUB_MEMBER . $this->_id, $this, null);
        }
        // Set dynamic rules, works for external users rights
        Ivc_Acl_Factory::setDynAcl($acl, $this);
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
    
    public function getResourceId()
    {
        return ($this->_scope . ':' . $this->getClubId() . ':datastore');
    }
    
	private function getMapper()
    {
        if (!$this->_mapper)
            $this->_mapper = new Model_Document_DatastoreMapper($this->_scope, $this->getClubId());
        return ($this->_mapper);
    }
    
	public function getRootPath(){
		return ($this->_rootPath);
	}
	
	private function saveFileMeta($relPath, $user_id = 0, $type = 'default')
	{
		$path = $this->_rootPath . $relPath;
		$size = filesize($path);
		// Add to db
		$data = array(
						"club_id" => $this->getClubId(),
						"create_member_id" => $user_id,
						"type" => $type,
						"size" => $size,
						"path" => $relPath);
		$this->getMapper()->saveFileMeta($this->_scope, $data);
	}
	
	public function saveFile($data, $user_id = 0)
	{
		if (!$this->getAcl()->ivcAllowed($this, 'upload') || !$this->getAcl()->ivcAllowed($this, 'generate') )
            throw new Ivc_Acl_Exception;
		
		$tmpDest = $this->getDestinationDirs();
		if (isset($data['file']) && isset($data['destination']))
		{
			// Check file ... that is security !
			// Check datastore usage before copy
			$tmpPath = '/tmp/' . $data['file'];
			$relativePath = $tmpDest[$data['destination']] . '/' . $data['file'];
			$path = $this->getRootPath() . $relativePath;
			if (copy($tmpPath, $path))
			{						 // replace by move
				$this->saveFileMeta($relativePath, $user_id);
				$this->getMessageInstance()->push(Ivc_Message::SUCCESS, "Fichier sauvegardé");
				return (array('result' => 'saved'));
			}
			else
				throw new Ivc_Exception("Error: could not copy file $tmpPath to $relativePath ($path)", Zend_Log::CRIT);
		}
		elseif (isset($data['title']) && isset($data['destination']))
		{
			$relativePath = $tmpDest[$data['destination']] . '/' . $data['title'];
			$path = $this->getRootPath() . $relativePath;

			if (touch($path))
			{
				$this->saveFileMeta($relativePath, $user_id, 'texteditor');
				$url = Ivc_Utils::base64url_encode($relativePath);
				$this->getMessageInstance()->push(Ivc_Message::SUCCESS, "$url");
				return (array('result' => $url));
			}
			else
				throw new Ivc_Exception("Error: could not create file $relativePath ($path)", Zend_Log::CRIT);
		}
		else
			throw new Ivc_Exception("Error: param error", Zend_Log::CRIT);
	}
	
	public function createEditableFile($relativePath, $user_id = 0)
	{
		if (!$this->getAcl()->ivcAllowed($this, 'createEditable') )
			throw new Ivc_Acl_Exception;
		

	}
	
	public function findFileById($id)
	{
		$meta = $this->getMapper()->findFileMetaById(Model_Document_Datastore::SCOPE_CLUB, $id);
		if ($meta === null)
			throw new Ivc_Exception("Error: could not find file", Zend_Log::CRIT);
		$file = new Ivc_File(array("path" => $this->getRootPath() . $meta['path'],
                				   "relPath" => $meta['path'],
                			   	   "name" => basename($meta['path']),
                			       "fileId" => $meta['datastore_files_meta_id'],
               				       "size" => $meta['size'],
           					       "type" => $meta['type'],
           					       "creationDate" => $meta['create_date'],
           					       "creatorId" => $meta['create_member_id']));
		return ($file);
	}
	
	public function findFileByRelPath($relPath)
	{
		//if (Ivc_Utils::base64url_decode($relPath, true))
		//	$relPath = Ivc_Utils::base64url_decode($relPath);
		$meta = $this->getMapper()->findFileMeta(Model_Document_Datastore::SCOPE_CLUB, $relPath);
		if ($meta === null)
			throw new Ivc_Exception("Error: could not find file", Zend_Log::CRIT);
		$file = new Ivc_File(array("path" => $this->getRootPath() . $meta['path'],
				"relPath" => $meta['path'],
				"name" => basename($meta['path']),
				"fileId" => $meta['datastore_files_meta_id'],
				"size" => $meta['size'],
				"type" => $meta['type'],
				"creationDate" => $meta['create_date'],
				"creatorId" => $meta['create_member_id']));
		return ($file);
	}
	
	public function sendFile($relativePath)
	{
		if (!$this->getAcl()->ivcAllowed($this, 'download'))
            throw new Ivc_Acl_Exception;
            
		//if (Ivc_Utils::base64url_decode($relativePath))
    		$relativePath = Ivc_Utils::base64url_decode($relativePath);
		
		$path = $this->_rootPath . $relativePath;
		
		// Send file
		if (file_exists($path)) {
    		header('Content-Description: File Transfer');
    		header('Content-Type: application/octet-stream');
    		header('Content-Disposition: attachment; filename='.basename($path));
    		header('Content-Transfer-Encoding: binary');
    		header('Expires: 0');
    		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    		header('Pragma: public');
    		header('Content-Length: ' . filesize($path));
    		ob_clean();
    		flush();
    		readfile($path);
    		exit;
		}
	}
	
	public function writeFile($relativePath, $content)
	{
		if (!$this->getAcl()->ivcAllowed($this, 'writeFile'))
			throw new Ivc_Acl_Exception;
		//if (Ivc_Utils::base64url_decode($relativePath, true))
		$relativePath = Ivc_Utils::base64url_decode($relativePath);
		$path = $this->_rootPath . $relativePath;
		file_put_contents($path, $content);
		$file = $this->findFileByRelPath($relativePath);
		$file->setSize(filesize($path));
		$file->update();
		
		return (true);
	}
	
	private function getFileSize($relativePath){
		$path = $this->_rootPath . $relativePath;
		return (filesize($path));
	}
	private function getFileType($path){
		return("default");
	}
	private function getFileCreationDate($path){
		
	}
	private function getFileModificationDate($path){
		
	}
	
	public function moveFile($src, $dest){
		if (!$this->getAcl()->ivcAllowed($this, 'remove'))
            throw new Ivc_Acl_Exception;
            
		if (Ivc_Utils::base64url_decode($src, true))
    		$src = Ivc_Utils::base64url_decode($src);
    	if (Ivc_Utils::base64url_decode($dest, true))
    		$dest = Ivc_Utils::base64url_decode($dest);
		// Check Acls
		$fileMeta = $this->getMapper()->findFileMeta(Model_Document_Datastore::SCOPE_CLUB, $src);
		
		if (!rename($this->_rootPath . $src, $this->_rootPath . $dest))
			echo "Error: could not move file $src to $dest<br />";
		else
		{
			$fileMeta['path'] = $dest;
			if (!$this->getMapper()->updateFileMeta(Model_Document_Datastore::SCOPE_CLUB, $fileMeta))
				echo "Error: could not update metadata<br />";
		}
	}
	
	private function removeFileMeta($relPath){
		$this->getMapper()->removeFileMeta(Model_Document_Datastore::SCOPE_CLUB, $relPath);
	}
	
	public function removeFile($relPath){
		if (!$this->getAcl()->ivcAllowed($this, 'remove'))
            throw new Ivc_Acl_Exception;
            
		//if (Ivc_Utils::base64url_decode($relPath, true) != false)
    		$relPath = Ivc_Utils::base64url_decode($relPath);
		
		$path = $this->_rootPath . $relPath;
		// Check Acls
		$fileMeta = $this->getMapper()->findFileMeta(Model_Document_Datastore::SCOPE_CLUB, $relPath);
		
		if (!unlink($path))
			echo "Error: could not remove file $relPath<br />";
		else
			$this->removeFileMeta($relPath);
		
		$this->getMessageInstance()->push(Ivc_Message::SUCCESS, "fichier supprimé");
	}
	
	public function createDataStore()
	{
		// Checks !
		mkdir($this->_rootPath, 0755, true);
		mkdir($this->_rootPath . "bilan/");
		mkdir($this->_rootPath . "data/");
		mkdir($this->_rootPath . "data/test");
		mkdir($this->_rootPath . "profile/");
	}
	
	public function getDataStoreTree($path = null)
	{
		if (!$this->getAcl()->ivcAllowed($this, 'list'))
            throw new Ivc_Acl_Exception;
            
		if ($path === null)
			$path = $this->_rootPath;
		$ignore = array('.', '..' );
		
		if (is_dir($path) == false) {
			$this->createDataStore();
			
		}
		
		
    	$dh = opendir( $path );
    	$fs = new Ivc_Fs($path);
    	while( false !== ( $file = readdir( $dh ) ) ){
        	if( !in_array( $file, $ignore ) )
        	{
            	if( is_dir( "$path/$file" ) )
                	$fs->addDirectory($this->getDataStoreTree("$path$file/"));
            	else
            	{
            		$relativeFilePath = substr($path . $file, strlen($this->_rootPath));
            		$meta = $this->getMapper()->findFileMeta($this->_scope, $relativeFilePath);
            		if ($meta)
                		$fs->addFile(array("path" => $path,
                						   "relPath" => substr($path . $file, strlen($this->_rootPath)),
                					   	   "name" => $file,
                					       "fileId" => $meta['datastore_files_meta_id'],
                					       "size" => $meta['size'],
                					       "type" => $meta['type'],
                					       "creationDate" => $meta['create_date'],
                					       "creatorId" => $meta['create_member_id']));
                	else
                	{
                		//echo "LOG ERROR: no meta data for this file<br />";
                		$this->saveFileMeta($relativeFilePath, $this->getMember()->member_id);
                	}
            	}
        	}
    	}
    	closedir( $dh );
    	return $fs;   	
	}
	
	public function getFileByType(Ivc_Fs $relDir, $type)
	{
		$first = false;
		if ($this->_getFileByTypeTmp === null)
		{
			$this->_getFileByTypeTmp = array();
			$first = true;
		}
		
		$content = $relDir->content;
		foreach ($content as $k => $entry)
		{
			if ($entry instanceof Ivc_Fs)
				$this->getFileByType($entry, $type);
			else if ($entry->getType() == $type)
				$this->_getFileByTypeTmp[] = $entry;		
		}
		
		if ($first === true)
		{
			$data = $this->_getFileByTypeTmp;
			$this->_getFileByTypeTmp = null;
			return ($data);
		}
	}
	
	public function getEditableFilesList()
	{
		$tree = $this->getDataStoreTree();
		return ($this->getFileByType($tree, 'texteditor'));
	}
	
	public function getDestinationDirs()
	{
		return (array('bilan', 'data', 'data/test', 'profile')); // Must replace this by a real list
	}
	
	public function printDataStore($path = null, $level = 0)
	{
		if ($path === null)
			$path = $this->_rootPath;
		$ignore = array('.', '..' );
    	$dh = @opendir( $path );
    
    	while( false !== ( $file = readdir( $dh ) ) ){
        	if( !in_array( $file, $ignore ) )
        	{
        		$spaces = str_repeat( '&nbsp;&nbsp;', ( $level * 4 ) );
            	if( is_dir( "$path/$file" ) )
            	{
	            	echo "<strong>$spaces $file</strong> (" . substr($path . $file, strlen($this->_rootPath)). ")<br />";
                	$this->printDataStore( "$path$file/", ($level+1) );
          		}
            	else
            	{
            		$relPath = substr($path . $file, strlen($this->_rootPath));
                	echo "$spaces $file (" . $relPath . ")<br />";
            	}
        	}
    	}
    	closedir( $dh ); 
	}
	public function printDirectory($path)
	{
		$handle = opendir($path);
		if ($handle)
		{
    		echo "Directory handle: $handle\n";
    		echo "Files:\n";

    		// This is the correct way to loop over the directory.
    		while (false !== ($file = readdir($handle))) {
        		echo "$file<br />\n";
    		}
		}
	}
	
	static public function saveStaticFile($src){ // Must move to assets
		$rootPath = '/var/www/test/datastore/ivc/';
		if (!file_exists($src))
		{
			echo "Error: src file doesn't exist<br />";
			return;
		}
		$path_parts = pathinfo($src); 
		$hash = hash_file('md5', $src);
		$hash .= '.' . $path_parts['extension'];
		$dirHash = substr($hash, 0, 3) . "/";
		if (!file_exists($rootPath . $dirHash))
			mkdir($rootPath . $dirHash);
		echo "[$dirHash][$hash]<br />";
		if (!rename($src, $rootPath . $dirHash . $hash))
			echo "Error: could not move file $src to $dirHash$hash<br />";
		return ($hash);
	}
	
	static public function removeStaticFile($hash){ // Must move to assets
		$rootPath = '/var/www/test/datastore/ivc/';
		$dirHash = substr($hash, 0, 3) . "/";
		return (unlink($rootPath . $dirHash . $hash));
	}
	
	static public function getStaticFileUrl($hash){ // Must move to assets
		$rootPath = '/var/www/test/datastore/ivc/';
		$rootUrl = '/test/datastore/ivc/';
		$dirHash = substr($hash, 0, 3) . "/";
		if (!file_exists($rootPath . $dirHash . $hash))
			echo "Error: file doesn't exist<br />";
		return ($rootUrl . $dirHash . $hash);
	}
}