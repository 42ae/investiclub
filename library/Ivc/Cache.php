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
 * @package		Ivc_Cache
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * Enter description here ...
 * 
 * @author		Jonathan Hickson
 * @category	Ivc
 * @package		Ivc_Cache
 */

class Ivc_Cache
{
	const SCOPE_IVC = 'ivc';
	const SCOPE_USER = 'user';
	const SCOPE_CLUB = 'club';
	
	const TIME_DEFAULT = 300;
	const TIME_10S = 10;
	const TIME_1H = 3600;
	const TIME_1D = 86400;
	
	private $_clubId = "NoClub";
	private $_user = null;
	private $_cache;
	private $_stats;
	private $_statsTable;
	private $_statsList;
	private $_cacheMode;
	private $_sessionId;
	private $_batch = false;
	static private $_instance = NULL;
	
	private function __construct()
	{
		$frontendOptions = array(
       		'lifetime' => self::TIME_DEFAULT, // cache lifetime of 10 sec
       		'automatic_serialization' => true
    	);
     
    	$backendOptions = array(
        	'cache_dir' => APPLICATION_PATH . '/../data/cache/' // Directory where to put the cache files. for perf use: '/dev/shm/' // Directory where to put the cache files (here is memory fs)
    	);
     
    	/*
    	$backendOptions = array(
    		'servers' =>array(array('host' => '127.0.0.1', 'port' => 11211)),
    		'compression' => true,
		);
		*/
		
    	// getting a Zend_Cache_Core object
    	
    	// TODO: (alex) check if 'cache_dir' exists. ---- chmod /cache/ ?
    	//
    	try {
    	$this->_cache = Zend_Cache::factory('Core', /*'Memcached'*/'File',
                                            $frontendOptions,
                                            $backendOptions);
    	}
    	catch (Zend_Exception $e) {
            echo "Message: " . $e->getMessage() . "\n";die;
       	}
       	//echo $oBackend->getFillingPercentage();
       	
    	$this->_stats = 'off';
        $this->_cacheMode = true;
        $this->_statsTable = null;
        $this->_statsList = array();
        if (defined('BATCH_MODE')) {
        	$this->_user = 0;
        	$this->_clubId = 0;
        }
        else {
        	$this->_user = Ivc::getCurrentUser();
        	if (Ivc_Auth::isLogged() AND $this->_user->hasClub()) {
        		$gateway = new Ivc_Model_Users_Gateway();
        		$this->_clubId = $gateway->fetchClubByUserId($this->_user->user_id)->club_id;
        	}
        }
        
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	static public function getInstance()
	{
		if (self::$_instance == null)
			self::$_instance = new Ivc_Cache;
		return (self::$_instance);
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $userId
	 * @param unknown_type $clubId
	 */
	public function initIvcCache($userId, $clubId)
	{
		//$this->_user->user_id = $userId;
		$this->_clubId = $clubId;
		return ($this);
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function setStats($mode) // off on full
	{
	    if ($mode !== 'off')
	    {
		    $this->_stats = $mode;
		    if ($mode === 'full' && !$this->_statsTable)
		    {
		        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
		        $this->_statsTable = new Zend_Db_Table('stats_cache');
		        $dbAdapter->insert('stats_cache_session', array());
		        $this->_sessionId = $dbAdapter->lastInsertId('stats_cache_session', 'stat_id');
		    }
	    }
	    else
	        $this->_stats = 'off';  
	    return ($this);
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $bool
	 */
	public function setCacheMode($bool)
	{
		$this->_cacheMode = $bool;
		return ($this);
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function getCache()
	{
		return ($this->_cache);
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 * @param unknown_type $scope
	 * @param unknown_type $id
	 * @param unknown_type $specificLifetime
	 */
	public function save($data, $scope, $id, $specificLifetime = false)
	{
		$id = str_replace("-", "_", $id);
		if ($scope === self::SCOPE_USER)
		{
			$fullId = $scope . $this->_user->user_id . $id;
			$tags = array($scope . $this->_user->user_id);
		}
		elseif ($scope === self::SCOPE_CLUB)
		{
			$fullId = $scope . $this->_clubId . $id;
			$tags = array($scope . $this->_clubId);
		}
		elseif ($scope === self::SCOPE_IVC)
		{
			$fullId = $scope . $id;
			$tags = array($scope);
		}
		
		if ($this->_stats !== 'off')
		{
			if ($this->_cache->load($fullId))
				$action = "replace";
			else
				$action = "insert";
			$dataStr = serialize($data);
			$size = strlen($dataStr);
			$stats = array(
			'full_key' => $fullId,
			'scope' => $scope,
			'key' => $id,
			'size' => $size,
			'action' => $action,
			'validity' => $specificLifetime,
			'session_id' => $this->_sessionId
			);
			$this->_statsList[] = $stats;
			if ($this->_stats === 'full')
			    $this->_statsTable->insert($stats);
		}
		$this->_cache->save($data, $fullId, $tags, $specificLifetime);
		return ($this);
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $scope
	 * @param unknown_type $id
	 */
	public function load($scope, $id)
	{
		$id = str_replace("-", "_", $id);
		if ($scope === self::SCOPE_USER)
			$fullId = $scope . $this->_user->user_id . $id;
		elseif ($scope === self::SCOPE_CLUB)
			$fullId = $scope . $this->_clubId . $id;
		elseif ($scope === self::SCOPE_IVC)
			$fullId = $scope . $id;
		$data = $this->_cache->load($fullId);
		
		if ($this->_stats !== 'off')
		{
			if (!$data)
			{
				$action = "cache_miss";
				$size = -1;
			}	
			else
			{
				$action = "cache_hit";
				$dataStr = serialize($data);
				$size = strlen($dataStr);
			}
			$stats = array(
				'full_key' => $fullId,
				'scope' => $scope,
				'key' => $id,
				'size' => $size,
				'action' => $action,
				'validity' => -1,
				'session_id' => $this->_sessionId
				);
			$this->_statsList[] = $stats;
			if ($this->_stats === 'full')
			    $this->_statsTable->insert($stats);	
		}
		if ($this->_cacheMode == false)
			return (false);
		return ($data);
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $scope
	 * @param unknown_type $id
	 */
	public function remove($scope, $id)
	{
		$id = str_replace("-", "_", $id);
		if ($scope === self::SCOPE_USER)
			$fullId = $scope . $this->_user->user_id . $id;
		elseif ($scope === self::SCOPE_CLUB)
			$fullId = $scope . $this->_clubId . $id;
		elseif ($scope === self::SCOPE_IVC)
			$fullId = $scope . $id;
		$rtn = $this->_cache->remove($fullId);
		
		if ($this->_stats !== 'off')
		{
			if ($rtn)
				$action = "remove_ok";
			else
				$action = "remove_ko";
			$stats = array(
				'full_key' => $fullId,
				'scope' => $scope,
				'key' => $id,
				'size' => -1,
				'action' => $action,
				'validity' => -1,
				'session_id' => $this->_sessionId
				);
			$this->_statsList[] = $stats;
			if ($this->_stats === 'full')
			    $this->_statsTable->insert($stats);	
		}
		return ($rtn);
	}
	
	public function cleanClub()
	{
		$rtn = $this->_cache->clean(Zend_Cache::CLEANING_MODE_ALL, array(self::SCOPE_CLUB . $this->_clubId));
		if ($this->_stats !== 'off')
		{
			if ($rtn)
				$action = "clean_ok";
			else
				$action = "clean_ko";
			$stats = array(
					'full_key' => 'none',
					'scope' => self::SCOPE_CLUB,
					'key' => 'none',
					'size' => -1,
					'action' => $action,
					'validity' => -1,
					'session_id' => $this->_sessionId
			);
			$this->_statsList[] = $stats;
			if ($this->_stats === 'full')
				$this->_statsTable->insert($stats);
		}
		return ($rtn);
	}
	
	public function cleanUser()
	{
		$rtn = $this->_cache->clean(Zend_Cache::CLEANING_MODE_ALL, array(self::SCOPE_USER . $this->_user->user_id));
		if ($this->_stats !== 'off')
		{
			if ($rtn)
				$action = "clean_ok";
			else
				$action = "clean_ko";
			$stats = array(
					'full_key' => 'none',
					'scope' => self::SCOPE_USER,
					'key' => 'none',
					'size' => -1,
					'action' => $action,
					'validity' => -1,
					'session_id' => $this->_sessionId
			);
			$this->_statsList[] = $stats;
			if ($this->_stats === 'full')
				$this->_statsTable->insert($stats);
		}
		return ($rtn);
	}
		
	/**
	 * 
	 * Enter description here ...
	 */
	public function getStats()
	{
	    return ($this->_statsList);
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function fetchStats()
	{
		$statsData = array();
		
		// Get all cache action
		$select = $this->_statsTable->select()
									->where('session_id = ?', $this->_sessionId);
		$rowSet = $this->_statsTable->fetchall($select);
		$statsData['AllActionCount'] = 0;
		foreach ($rowSet as $row)
		{
			$statsData['AllAction'][] = $row->toArray();
			$statsData['AllActionCount'] += 1;
		}
		
		// Calculate current cache use for every scope;
		//SELECT * FROM stats_ivc_cache WHERE session_id = 12 AND (action = "insert" OR action = "cache_hit") GROUP BY full_key;
		$select = $this->_statsTable->select()
									->where('session_id = ?', $this->_sessionId)
									->where('action = "insert" OR action = "cache_hit" OR action = "replace"')
									->group('full_key');
		$rowSet = $this->_statsTable->fetchall($select);
		$statsData['totalUse'] = 0;
		$statsData['ivcUse'] = 0;
		$statsData['clubUse'] = 0;
		$statsData['userUse'] = 0;
		foreach ($rowSet as $row)
		{
			$statsData['keyUseList'][$row['full_key']] = $row->toArray();
			$statsData['totalUse'] += $row['size'];
			$statsData[$row['scope'] . 'Use'] += $row['size'];
		}
		
		// Calculate average use of user session ... need user session for that
		
		return ($statsData);
	}
	
}