<?php

class Model_Document_DatastoreMapper
{
	private $dba;
    private $clubId;
    
    public function __construct ($scope, $clubId)
	{
	    $this->clubId = $clubId;
	    $this->dba = Zend_Db_Table::getDefaultAdapter();
	}
	
	public function saveFileMeta($scope, $data)
	{
		if ($scope === Model_Document_Datastore::SCOPE_CLUB)
		{
			$data['club_id'] = $this->clubId;
			$dbTable = new Zend_Db_Table('datastore_files_meta');
		}
        return ($dbTable->insert($data));
	}
	
	public function findFileMeta($scope, $path)
	{
		$data = null;
		if ($scope === Model_Document_Datastore::SCOPE_CLUB)
		{
			$dbTable = new Zend_Db_Table('datastore_files_meta');
			$select = $dbTable->select()->where('club_id = ?', $this->clubId)
										->where('path = ?', $path);
			$data = $dbTable->fetchAll($select)->current();
			if ($data)
				$data = $data->toArray();
		}
       	return ($data);
	}
	
	public function findFileMetaById($scope, $id)
	{
		$data = null;
		if ($scope === Model_Document_Datastore::SCOPE_CLUB)
		{
			$dbTable = new Zend_Db_Table('datastore_files_meta');
			$select = $dbTable->select()->where('club_id = ?', $this->clubId)
			->where('datastore_files_meta_id = ?', $id);
			$data = $dbTable->fetchAll($select)->current();
			if ($data)
				$data = $data->toArray();
		}
		return ($data);
	}
	
	public function updateFileMeta($scope, $data)
	{
		if ($scope === Model_Document_Datastore::SCOPE_CLUB)
		{
			$dbTable = new Zend_Db_Table('datastore_files_meta');
			$data['club_id'] = $this->clubId;
		}
        return ($dbTable->update($data, array('datastore_files_meta_id = ?' => $data['datastore_files_meta_id'])));
	}
	
	public function removeFileMeta($scope, $path)
	{
		$data = null;
		if ($scope === Model_Document_Datastore::SCOPE_CLUB)
		{
			$dbTable = new Zend_Db_Table('datastore_files_meta');
			$select = $dbTable->select()->where('club_id = ?', $this->clubId)
										->where('path = ?', $path);
			$data = $dbTable->fetchAll($select)->current();
			if ($data)
				$data->delete();
		}
	}
	
	public function fetchFiles($type)
	{
		$data = null;
		$dbTable = new Zend_Db_Table('datastore_files_meta');
		$select = $dbTable->select()->where('club_id = ?', $this->clubId)
									->where('type = ?', $type);
		$rowset = $dbTable->fetchAll($select);
		foreach ($rowset as $row)
			$data[$row['path']] = $row->toArray();
		return ($data);
	}
}
