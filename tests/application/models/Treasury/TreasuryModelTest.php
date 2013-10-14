<?php

class TreasuryModelTest extends ControllerTestCase
{
    /**
     * 
     * Treasury balance sheet Unit Tests
     */
    
	private function getTreasury()
	{
		system("/var/www/ivc/scripts/db/mysql/reload-db.sh");
		$config = array('date' => '2011-04-28',
						'clubCreationDate' => '2010-12-24',
						'clubId' => 10);
		$treasury = new Model_Treasury_Treasury($config);
		return ($treasury);
	}
	
    public function testTreasury2011_01_31Values()
    {
        $treasury = $this->getTreasury();
        $info = $treasury->getData("2011-01-31");
        $this->assertSame($info['solde'], 700.0);
        $this->assertSame($info['pf'], 1150.0);
        $this->assertSame($info['capital'], 1850.0);
        $this->assertSame($info['profit'], 0.0);
    }
    
    public function testTreasury2011_02_28Values()
    {
        $treasury = $this->getTreasury();
        $info = $treasury->getData("2011-02-28");
        $this->assertSame($info['solde'], 240.0);
        $this->assertSame($info['pf'], 3460.0);
        $this->assertSame($info['capital'], 3700.0);
        $this->assertSame($info['profit'], 0.0);
    }
    
    public function testTreasury2011_03_31Values()
    {
        $treasury = $this->getTreasury();
        $info = $treasury->getData("2011-03-20"); // bilan with midrange date
        $this->assertSame($info['solde'], 180.0);
        $this->assertSame($info['pf'], 7290.0);
        $this->assertSame($info['capital'], 7470.0);
        $this->assertSame($info['profit'], 1970.0);
    }
    public function testTreasury2011_04_30Values()
    {
        $treasury = $this->getTreasury();
        $info = $treasury->getData("2011-04-01"); // bilan with start date
        $this->assertSame($info['solde'], 1866.088);
        $this->assertSame($info['pf'], 6784.0);
        $this->assertSame($info['capital'], 8650.088);
        $this->assertSame($info['profit'], 0.0);
    }
 
    private function checkError($error, $flag, $msg)
    {
        $list = $error->getList();
        $this->assertArrayHasKey($flag, $list);
        $this->assertSame($msg, $list[$flag][0]);
        $error->flush();
    }
    
    /**
     * 
     * Treasury Credit Unit Tests
     */
    /*
    public function testTreasuryAddCreditNoParam()
    {
        $treasury = new Model_Treasury_Treasury();
        $data = array();
        $this->checkError($treasury->addCredit($data)->getError(), 'ERROR', Model_Treasury_Treasury::BAD_PARAM);
    }
    */
    
    public function testTreasuryAddCreditClosed()
    {
        $treasury = $this->getTreasury();
        $data = array('user_id' => 110, 'value' => 200, 'comment' => "Credit", 'date' => '2011-03-04');
        $this->checkError($treasury->addCredit($data)->getError(), 'ERROR', Model_Treasury_Treasury::TREASURY_CLOSED);
    }
   
    public function testTreasuryAddCreditNotExist()
    {
        $treasury = $this->getTreasury();
        $data = array('user_id' => 110, 'value' => 200, 'comment' => "Credit", 'date' => '2011-06-04');
        $this->checkError($treasury->addCredit($data)->getError(), 'ERROR', Model_Treasury_Treasury::TREASURY_DONT_EXIST);
    }
    
    public function testTreasuryAddCreditOk()
    {
        $treasury = $this->getTreasury();
        $data = array('user_id' => 110, 'value' => 200, 'comment' => "Credit", 'date' => '2011-04-04');
        $this->checkError($treasury->addCredit($data, true)->getError(), 'SUCCESS', 'credit registred');
    }
    
    public function testTreasuryAddCreditDynamicValue()
    {
        $treasury = $this->getTreasury();
        $data = array('user_id' => 110, 'comment' => "Creditt", 'date' => '2011-04-04');
        $this->checkError($treasury->addCredit($data, true)->getError(), 'SUCCESS', 'credit registred');
    }
    
 
    public function testTreasuryAddCreditWarningForce()
    {
        $treasury = $this->getTreasury();
        $data = array('user_id' => 110, 'value' => 2000, 'comment' => "Credit", 'date' => '2011-04-04', 'force' => true);
        $this->checkError($treasury->addCredit($data, true)->getError(), 'SUCCESS', 'credit registred');
    }

    /*
    public function testTreasuryAddCreditWarningUserMax()
    {
        $treasury = new Model_Treasury_Treasury();
        $data = array('user_id' => 110, 'value' => 6000, 'comment' => "Credit", 'date' => '2011-04-04');
        $error = $treasury->addCredit($data, true)->getError()->list[0];
        $this->assertArrayHasKey('WARNING', $error);
        $this->assertSame('xxx', $error['WARNING']);
        $treasury->getError()->flush();
    }
    */
    
    /*
    public function testTreasuryAddCreditUserNull()
    {
        $treasury = new Model_Treasury_Treasury();
        $data = array('user_id' => 110, 'value' => 200, 'comment' => "Credit", 'date' => '2011-04-04');
        $error = $treasury->addCredit($data, true)->getError()->list[0];
        $this->assertArrayHasKey('SUCCESS', $error);
        $this->assertSame('credit registred', $error['SUCCESS']);
        $treasury->getError()->flush();
    }
    */

	/**
     * 
     * Treasury Debit Unit Tests
     */
    /*
    public function testTreasuryAddDebitNoParam()
    {
        $treasury = new Model_Treasury_Treasury();
        $data = array();
        $this->checkError($treasury->addDebit($data)->getError(), 'ERROR', Model_Treasury_Treasury::BAD_PARAM);
    }
    */
    
    public function testTreasuryAddDebitClosed()
    {
        $treasury = $this->getTreasury();
        $data = array('user_id' => 110, 'value' => 200, 'comment' => "Debit", 'date' => '2011-03-04');
        $this->checkError($treasury->addDebit($data)->getError(), 'ERROR', Model_Treasury_Treasury::TREASURY_CLOSED);
    }
    
    public function testTreasuryAddDebitNotExist()
    {
        $treasury = $this->getTreasury();
        $data = array('user_id' => 110, 'value' => 200, 'comment' => "Debit", 'date' => '2011-06-04');
        $this->checkError($treasury->addDebit($data)->getError(), 'ERROR', Model_Treasury_Treasury::TREASURY_DONT_EXIST);
    }
    
    public function testTreasuryAddDebitOk()
    {
        $treasury = $this->getTreasury();
        $data = array('user_id' => 110, 'value' => 200, 'comment' => "Debit", 'date' => '2011-04-04');
        $this->checkError($treasury->addDebit($data, true)->getError(), 'SUCCESS', 'debit registred');
    }
    
    public function testTreasuryAddDebitDynamicValue()
    {
        $treasury = $this->getTreasury();
        $data = array('user_id' => 110, 'comment' => "Debit", 'date' => '2011-04-04');
        $this->checkError($treasury->addDebit($data, true)->getError(), 'SUCCESS', 'debit registred');
    }
    
    public function testTreasuryAddDebitWarningCash()
    {
        $treasury = $this->getTreasury();
        $data = array('user_id' => 110, 'value' => 2000, 'comment' => "Debit", 'date' => '2011-04-04');
        $this->checkError($treasury->addDebit($data)->getError(), 'WARNING', 'Not enough cash');
    }
    
    public function testTreasuryAddDebitForce()
    {
        $treasury = $this->getTreasury();
        $data = array('user_id' => 110, 'value' => 2000, 'comment' => "Debit", 'date' => '2011-04-04', 'force' => true);
        $this->checkError($treasury->addDebit($data, true)->getError(), 'SUCCESS', 'debit registred');
    }
   
    /*
    public function testTreasuryAddDebitWarningUserCash()
    {
        $treasury = new Model_Treasury_Treasury();
        $data = array('user_id' => 110, 'value' => 2000, 'comment' => "Debit", 'date' => '2011-04-04');
        $error = $treasury->addDebit($data, true)->getError()->list[0];
        $this->assertArrayHasKey('SUCCESS', $error);
        $this->assertSame('credit registred', $error['SUCCESS']);
        $treasury->getError()->flush();
    }
    */
    
    /*
    public function testTreasuryAddDebitUserNull()
    {
        $treasury = new Model_Treasury_Treasury();
        $data = array('user_id' => 110, 'value' => 200, 'comment' => "Credit", 'date' => '2011-04-04');
        $error = $treasury->addCredit($data, true)->getError()->list[0];
        $this->assertArrayHasKey('SUCCESS', $error);
        $this->assertSame('credit registred', $error['SUCCESS']);
        $treasury->getError()->flush();
    }
    */
    
    /*
  public function testTreasuryAddDebit()
  {

      $treasury = new Model_Treasury_Treasury();
      $treasury->setClubCreationDate("2010-12-24");
      //$treasury->setDebugMode(true);
      $info = $treasury->getTreasury();
      $this->assertSame($info['solde'], 1866.088); // Check treasury value
      
      //$data = array('user_id' => 42, 'value' => 200, 'comment' => "Debit", 'date' => '2011-05-04');
      //$rtn = $treasury->addDebit($data, false, true);
      //$this->assertArrayHasKey('SUCCESS', $rtn->list[0]);
      
      //$data = array('user_id' => 42, 'value' => 250, 'comment' => "Debit", 'date' => '2011-05-04');
      //$rtn = $treasury->addDebit($data, false, true);
      //$this->assertArrayHasKey('WARNING', $rtn->list[0]);
      

  }
  */
}

