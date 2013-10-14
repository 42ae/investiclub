<?php

require_once('PHPUnit/Extensions/Database/DataSet/FlatXmlDataSet.php');

abstract class ControllerTestCase extends Zend_Test_PHPUnit_ControllerTestCase
{
    protected $application;

    /*
    public function setUp()
    {
        $this->bootstrap = array($this, 'appBootstrap');
        parent::setUp();
    }

    public function appBootstrap()
    {
        $this->application = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        $this->application->bootstrap();
    }
    */
    
    /*
    public function setUp()
    {
        // Use the 'start' method of a Bootstrap object instance:
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        parent::setUp();
    }
    */
    public function setUp()
    {
        
        $this->bootstrap = array($this, 'appBootstrap');
        //$this->setupDatabase();
        parent::setUp();
    }
 
    public function appBootstrap()
    {
        $this->application = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        $this->application->bootstrap();
        $this->frontController
             ->registerPlugin(new Ivc_Controller_Plugin_CheckAccess());
    }
    /*
    public function setupDatabase()
    {
        $db = new Zend_Db_Adapter_Pdo_Mysql(array(
    			'host'     => '192.168.1.42',
    			'username' => 'root',
    			'password' => 'toto',
    			'dbname'   => 'test_investiclub'
    			));
    	Zend_Db_Table::setDefaultAdapter($db);
        $connection = new Zend_Test_PHPUnit_Db_Connection($db, 'investiclub');
        $connection->createDataSet(array('treasury_capital_users'));
        $databaseTester = new Zend_Test_PHPUnit_Db_SimpleTester($connection);
 
        //$databaseFixture = $this->createMySQLXMLDataSet(APPLICATION_PATH . '/../tests/InitialFixture.xml');
        //$databaseTester->setupDatabase($databaseFixture);
    }
    */
}
    
