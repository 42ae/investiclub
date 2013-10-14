<?php

defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
defined('BATCH_MODE') || define('BATCH_MODE', true);

include ("Zend/Db/Exception.php");
include ("Zend/Db.php");
include ("Zend/Db/Adapter/Abstract.php");
include ("Zend/Db/Adapter/Pdo/Mysql.php");
include ("Zend/Db/Table.php");
include ("Zend/Rest/Client.php");
include ("Zend/Filter/StripTags.php");
include ("Zend/Date.php");
include ("Zend/Cache.php");
include ("../library/Ivc/Cache.php");
include ("../application/modules/default/models/Portfolio/QuotesLive.php");
include ("../application/modules/default/models/Portfolio/DbTable/QuotesLive.php");


if (file_exists("quotesLiveUpdater.lock")) {
	echo "Script is already running, exit\n";
	exit;
}
touch ("quotesLiveUpdater.lock");

$db = new Zend_Db_Adapter_Pdo_Mysql(array(
		'host'     => '127.0.0.1',
		'username' => 'root',
		'password' => 'toto',
		'dbname'   => 'investiclub'
));
Zend_Db_Table::setDefaultAdapter($db);

$select = $db->select()->from(array('ua' => 'users_activity'), array('s.symbol'))
					   ->join(array('m' => 'members'), 'ua.user_id = m.user_id', array())
					   ->join(array('t' => 'treasury_transactions'), 'm.club_id = t.club_id', array())
					   ->join(array('s' => 'stocks'), 't.stock_id = s.stock_id', array())
					   ->where('NOW() - ua.last_activity < ? AND s.is_default = 1',  600)
					   ->group('s.stock_id');
echo "$select\n";
$rowset = $db->fetchAll($select);
foreach ($rowset as $row)
{
	echo "Updating : " . $row['symbol'] . "\n";
	$quoteLive = new Model_Portfolio_QuotesLive(array("symbol" => $row['symbol']));
	$quoteLive->getQuote();
	$tmp = $quoteLive->toArray();
	echo "LastTrade: " . $tmp['last_trade'] . " LastUpdate:" . $tmp['last_update'] . "\n";
}
echo "Done !\n";
unlink("quotesLiveUpdater.lock");