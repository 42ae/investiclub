<?php

class XotestsController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        echo "Xo Test Controller<br />";
        echo '<a href="'; echo $this->view->url(array('controller'=>'xotests',
                    							'action' => 'treasury'),
            									'default', true);  echo '">treasury</a><br />';
        echo '<a href="'; echo $this->view->url(array('controller'=>'xotests',
                    							'action' => 'treasurydump'),
            									'default', true);  echo '">treasury dump</a><br />';
        echo '<a href="'; echo $this->view->url(array('controller'=>'xotests',
                    							'action' => 'printshares'),
            									'default', true);  echo '">portfolio</a><br />';
        echo '<a href="'; echo $this->view->url(array('controller'=>'xotests',
                    							'action' => 'portfoliodump'),
            									'default', true);  echo '">portfolio dump</a><br />';
        echo '<a href="'; echo $this->view->url(array('controller'=>'xotests',
                    							'action' => 'showlog'),
            									'default', true);  echo '">show log</a><br />';
        echo '<a href="'; echo $this->view->url(array('controller'=>'xotests',
                    							'action' => 'treasuryall'),
            									'default', true);  echo '">treasury All</a><br />';
        echo '<a href="'; echo $this->view->url(array('controller'=>'xotests',
                    							'action' => 'contributions'),
            									'default', true);  echo '">Contributions</a><br />';
        echo '<a href="'; echo $this->view->url(array('controller'=>'xotests',
                    							'action' => 'printdatastore'),
            									'default', true);  echo '">Datastore</a><br />';
        echo '<a href="'; echo $this->view->url(array('controller'=>'xotests',
                    							'action' => 'savefile'),
            									'default', true);  echo '">Save File (copy from tmp)</a><br />';
        
        echo "<hr />Not DB friendly !<br />";
        echo '<a href="'; echo $this->view->url(array('controller'=>'xotests',
                    							'action' => 'resetportfoliotreasury'),
            									'default', true);  echo '">Reset Portfolio and treasury</a><br />';

    }
    
    
    public function printsharesAction()
    {
        $portfolio = new Model_Portfolio_Portfolio();
        //$portfolio2 = new Model_Portfolio_Portfolio(11);
        //Zend_Debug::Dump($portfolio->getPortfolioShares());
        $shares = $portfolio->getPortfolioStocks();
        Zend_Debug::dump($portfolio->getPortfolioActiveStocksSymbols(), "active stocks symbols");
        
        foreach ($shares as $share)
        {
            $share->calculateAll();
            $share->printInfos();
            echo "<hr />";
        }
        
        //Zend_Debug::dump($portfolio->getPortfolio());
        
        $this->renderScript("xotests/index.phtml");
    }
    
 
    

    
    public function treasuryAction()
    {
        $bilan_date = $this->getRequest()->getParam('date');
        //if (!$bilan_date)
        //    $bilan_date = '2011-04-01'; // just for test
        $treasury = new Model_Treasury_Treasury();
        $treasury->setDebugMode(false);
        //$treasury->setDebugMode(true);
        // Display bilan's list
        foreach ($treasury->getTreasuryList() as $key => $val)
        {
            echo  '<a href="' .  $this->view->url(
            array('controller'=>'xotests',
                  'action' => 'treasury',
                    'date' => $key),
            'default',
            true) . '">' . "bilan: $key" . '</a><br />';
        };
        echo  '<a href="' .  $this->view->url(
            array('controller'=>'xotests',
                  'action' => 'treasury'),
            'default',
            true) . '">' . "bilan: Current" . '</a><br /><br />';
        
        echo "Bilan you have to valid :<br />";
        foreach ($treasury->getTreasuryPendingValidationList() as $key => $val)
        	echo "$key<br />";
        
        // Display selected bilan from date (works within intervals)
        $data = $treasury->getData($bilan_date); // Get bilan
        echo "Treasury Dates<br />";
        Zend_Debug::Dump($treasury->getTreasuryDates());
        $treasury->setActions($data); // Set actions for this bilan
        Zend_Debug::Dump($data, "dump");
        echo "<H1>Bilan $bilan_date (" . $data['status'] . ") | cotisation: " . (($data['flag_cotisation']) ? "OK" : "KO") . " reevaluation: " . ($data['flag_revaluation'] ? "OK" : "KO") . "</H1>";
        echo "<table width=\"100%\"><tr>";
        echo "<td>date</td>";
        echo "<td>operation</td>";
        echo "<td>debit</td>";
        echo "<td>credit</td>";
        echo "<td>solde</td>";
        echo "<td>Actif</td>";
        echo "<td>Passif</td>";
        echo "<td>observations</td>";
        echo "<td>liens</td>";
        echo "</tr>";
        foreach ($data['events'] as $key => $val)
        {
            echo "<tr>";
            echo "<td>$key</td>";
            echo "<td>" . $val['ope'];
                         if ($val['ope'] === "buy" || $val['ope'] === "sell") echo " " . $val['shares'] . " " . $val['symbol'];
                         if ($val['ope'] === "credit" || $val['ope'] === "debit" || $val['ope'] === "demission") echo " User " . $val['club_user_id'];
                         if ($val['ope'] === "dividend" || $val['ope'] === "sell") { echo "<br />"; foreach ($val['users'] as $id => $action) echo "User $id : $action<br />";}
            echo "</td>";
            echo "<td>"; if ($val['ope'] === "debit") echo $val['value'];
                         if ($val['ope'] === "demission") echo $val['value'] . "<br />(F:" . $val['fees'] . ")";
                         if ($val['ope'] === "buy") echo ($val['price'] * $val['shares']);
            echo "</td>";
            echo "<td>"; if ($val['ope'] === "credit") echo $val['value'];
                         if ($val['ope'] === "sell") echo $val['value'] . " (" . round($val['profit'], 2) . ")";
                         if ($val['ope'] === "dividend") echo $val['price'] * $val['shares'];
            echo "</td>";
            echo "<td>"; if ($val['ope'] !== "reevaluation") echo round($val['solde'],2);
            echo "</td>";
            echo "<td>"; if ($val['ope'] === "reevaluation")
                            echo "Before<br />" . "PF: "
                                . $val['old_pf'] . "<br />Solde: " . $val['solde']
                                . "<br />= " . ($val['old_pf'] + $val['solde'])
                                ."<br />After<br />" . "PF: "
                                . $val['new_pf'] . "<br />Solde: " . $val['solde']
                                . "<br />= " . ($val['new_pf'] + $val['solde']);
            echo "</td>";
            echo "<td>"; if ($val['ope'] === "reevaluation")
                            echo "Before<br />" . "Capital: "
                                . $val['old_capital']
                                . "<br />= " . $val['old_capital'] . "<br />"
                                ."<br />After<br />" . "Capital: "
                                . $val['old_capital'] . "<br /> reev: " . $val['value']
                                . "<br />= " . $val['new_capital'];
            echo "</td>";
            echo "<td>"; if (isset($val['comment'])) echo $val['comment'];
            echo "</td>";
            echo "<td>"; if (isset($val['action'])) foreach($val['action'] as $ac) echo "$ac ";
            echo "</td>";
            echo "</tr>"; 
        }
        echo "<td></td><td></td>";
        echo "<td>Fin du mois</td>";
        echo "<td>Bilan fin du mois</td>";
        echo "<td></td>";
        echo "<td>Actif<br />" . "PF: "
        . $data['pf'] . "<br />Solde: " . $data['solde']
        . "<br />= " . ($data['pf'] + $data['solde']) . "</td>";
        echo "<td>Passif<br />Capital: " . $data['capital'] . "<br />(Profits : " . $data['profit'] . ")<br />= " . $data['capital'] . "</td>";
        echo "<td></td><td></td></tr></table>";
        
        echo "<H1>Stats</H1>";
        echo "Perf: " . round($data['perf'],2) . "%<br />";
        echo "UVS: " . round($data['unit'],2) . " Total: " . round($data['unit_nb'],2). "<br />";
        
        echo "<H1>User Stats</H1>";
        echo "<table width=\"100%\"><tr><td>Id</td><td>Paid</td><td>Value</td><td>Share %</td><td>Unit Nb</td><td>perf %</td></tr>";
        foreach ($data['memberStats'] as $id => $member)
        {
            echo "<tr><td>"
            . $id . "</td><td>"
            . $member['paid'] . "</td><td>"
            . $member['value'] . "</td><td>"
            . $member['shareP'] . "</td><td>"
            . $member['unitNb'] . "</td><td>"
            . $member['perfP'] . "</td></tr>";
        }
        echo "</table>";
        
        $this->renderScript("xotests/index.phtml");
    }

    public function treasuryallAction()
    {   
        $treasury = new Model_Treasury_Treasury();
        $treasury->setDebugMode(true);
        $treasury->calculateAll();
        //$treasury->setActions($data);
        $this->renderScript("xotests/index.phtml");
    }
    
    public function treasurydumpAction()
    {
        $treasury = new Model_Treasury_Treasury();
        $data = $treasury->getData();
        $treasury->setActions($data);
        Zend_Debug::Dump($data);
        $this->renderScript("xotests/index.phtml");
    }
    public function portfoliodumpAction()
    {
        $portfolio = new Model_Portfolio_Portfolio();
        
        Zend_Debug::dump($portfolio->getTransacById(26));
        echo "* * * *";
        Zend_Debug::Dump($portfolio->getData());
        $this->renderScript("xotests/index.phtml");
    }
    
    public function showlogAction()
    {
        $logs = new Zend_Db_Table('logs');
        $select = $logs->select()->order('log_id DESC')
                                 ->limit(100);
        $rowset = $logs->fetchAll($select);
        echo "<table>";
        echo "<tr><td width=\"40px\">log_id" .
            "</td><td width=\"80px\">date" .
        	"</td><td width=\"100px\">ip" .
            "</td><td width=\"50px\">user_id" .
            "</td><td width=\"80px\">priority_name" .
            "</td><td width=\"120px\">message" .
            "</td><td>stack_trace</td></tr>";
        foreach ($rowset as $entry)
        {
            $date = new DateTime($entry['timestamp']);
            $date->setTimezone(new DateTimeZone('Asia/Chongqing'));  
            echo "<tr><td>" . $entry['log_id'] .
            "</td><td>" . $date->format('Y-m-d H:i:s') .
            "</td><td>" . $entry['ip'] .
            "</td><td>" . $entry['user_id'] .
            "</td><td>" . $entry['priority_name'] .
            "</td><td>" . $entry['message'] .
            "</td><td>" . $entry['stack_trace'] . "<br /><br /></td></tr>";
        }
        echo "</table>";
        $this->renderScript("xotests/index.phtml");
    }
    
    public function contributionsAction()
    {
    	$data = array('treasuryName' => '2012-06-04');
    	//$data = null;
        $contribs = new Model_Treasury_Contribution($data);
        echo "Treasury Dates<br />";
        Zend_Debug::Dump($contribs->checkContribution());
        Zend_Debug::Dump($contribs->getTreasury()->getTreasuryDates());
        Zend_Debug::Dump($contribs->getContributions());
        
        
        $this->renderScript("xotests/index.phtml");
    }
    
    private function printDir(Ivc_Fs $relDir, $level = 0)
    {
    	$content = $relDir->content;
    	$spaces = str_repeat( '&nbsp;&nbsp;', ( $level * 4 ) );
    	foreach ($content as $entry)
    	{
    		if ($entry instanceof Ivc_Fs)
    		{
    			echo $spaces . "[" . $entry->getName() . "]<br />";
    			$this->printDir($entry, $level + 1);
    		}
    		else
    		{
    			echo $spaces . $entry->getName() . " "
    			. '<a href="' .  $this->view->url(array('controller'=>'xotests',
                  										'action' => 'sendfile',
            											'file' => $entry->getUrl()),
            									  'default',
            									  true) . '">download</a> '
            		. '<a href="' .  $this->view->url(array('controller'=>'xotests',
                  											 'action' => 'removefile',
            												 'file' => $entry->getUrl()),
            										   'default',
            										   true) . '">remove</a> '
         		. '<a href="' .  $this->view->url(array('controller'=>'xotests',
                  											 'action' => 'movefile',
            												 'file' => $entry->getUrl()),
            										   'default',
            										   true) . '">move</a><br />';
    		}
    	}
    }
    
    public function printdatastoreAction()
    {
    	$datastore = new Model_Document_Datastore();
    	
    	
 		echo "<H2>Usage Stats</H2>0MB/100MB used<br />";
 		echo 'root path:' . $datastore->getRootPath() . "<br />";
    	echo "<H2>Directory tree</H2>";
    	$data = $datastore->getDataStoreTree();
    	$this->printDir($data);
    	
    	echo "<H2>Upload new file :</H2>";
    	$form = new Zend_Form();
    	$form->setName('upload');
        $form->setAttrib('enctype', 'multipart/form-data');
        
        $description = new Zend_Form_Element_Text('description');
        $description->setLabel('Description')
                  ->setRequired(true)
                  ->addValidator('NotEmpty');

        $file = new Zend_Form_Element_File('file');
        $file->setLabel('File')
                 ->setRequired(true)
                 ->addValidator('NotEmpty');
             

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Upload');
        
        $form->addElements(array($description, $file, $submit));
    	if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData)) {
                
                // success - do something with the uploaded file
                $uploadedData = $form->getValues();
                Zend_Debug::dump($uploadedData, '$uploadedData');
                $datastore->saveFile('/tmp/' . $uploadedData['file'], 'data/' . date("Y-m-d-s") . '-' . $uploadedData['file']);
                
            } else {
                $form->populate($formData);
            }
        }

        echo $form;
		echo "<hr />";
		Zend_Debug::Dump($datastore->getDataStoreTree());
		$this->renderScript("xotests/index.phtml");
    }
    
	public function sendfileAction()
    {
    	$fileRelPath = $this->getRequest()->getParam('file');
    	$datastore = new Model_Document_Datastore();
    	$datastore->sendFile($fileRelPath);
    }
    
	public function movefileAction()
    {
    	$fileRelPath = $this->getRequest()->getParam('file');
    	$destRelPath = base64_decode($this->getRequest()->getParam('file')) . "-test";
    	$datastore = new Model_Document_Datastore();
    	$datastore->moveFile($fileRelPath, $destRelPath);
    	echo "file moved";
    	$this->renderScript("xotests/index.phtml");
    }
    
	public function removefileAction()
    {
    	$fileRelPath = $this->getRequest()->getParam('file');
    	$datastore = new Model_Document_Datastore();
    	$datastore->removeFile($fileRelPath);
    	echo "file removed";
    	$this->renderScript("xotests/index.phtml");
    }
    
	public function generatefileAction()
    {
    	$inputData = array('solde' => 100, 'pf' => 42);
    	$pdfGen = new Model_Document_PdfGenerator($inputData, 10);
    	$datastore = new Model_Document_Datastore();
    	$datastore->saveFile($pdfGen->filePath, "bilan/test-pdf_" . date("Y-m-d-s") . ".pdf" , 110);
    	echo "file generated:" . $pdfGen->filePath;;
    	$this->renderScript("xotests/index.phtml");
    }
    
    public function savestaticfileAction()
    {
    	Model_Document_Datastore::saveStaticFile("/tmp/gpufarming.jpg");
    	$this->renderScript("xotests/index.phtml");
    }
    
    
	public function getstaticfileAction()
    {
    	echo '<img src="' . Model_Document_Datastore::getStaticFileUrl("b7488ab069768915427877fea813ea33.jpg") . '">';
    	$this->renderScript("xotests/index.phtml");
    }
    
    
    public function yahooreqAction()
    {
    	// http://d.yimg.com/aq/autoc?query=iliad&region=US&lang=en-US&callback=YAHOO.util.ScriptNodeDataSource.callbacks
    	$client = new Zend_Http_Client('http://d.yimg.com/aq/autoc');
    	$client->setParameterGet('query', 'AIR LIQUIDE');
    	$client->setParameterGet('callback', 'YAHOO.util.ScriptNodeDataSource.callbacks');
    	$response = $client->request('GET');
    	$data = $response->getBody();
    	$data = str_replace( 'YAHOO.util.ScriptNodeDataSource.callbacks(', '', $data );
		$data = substr( $data, 0, strlen( $data ) - 1 ); //strip out last paren
		Zend_Debug::dump($data);
		$json = Zend_Json::decode($data);
		Zend_Debug::dump($json);
    	$this->renderScript("xotests/index.phtml");
    }
    
    private function adaptDate($month, $day)
    {
    	$date = new Zend_Date();
    	$dateArray = $date->toArray();
    	
    	//$dateArray['year'] = 2011;
    	//$dateArray['month'] = 5;
    	
    	$month = $dateArray['month'] + $month - 5;
    	
    	if ($month <= 0)
		{
			$month = 12 + $month;
			$dateArray['year'] -= 1;
		}
		$month = sprintf("%02d", (int)$month);
		$day = sprintf("%02d", (int)$day);
    	$year = $dateArray['year'];
    	return("$year-$month-$day");
    }
    
    public function resetportfoliotreasuryAction()
    {
    	
    	//$curDate = date("Y-m-d");
    	//echo "Curdate: $curDate<br />";
    	//$date = new Zend_Date();
    	//$dateArray = $date->toArray();
    	//Zend_Debug::dump($dateArray);
    	
		//echo $this->adaptDate(0, 24) . "<br />";
    	//echo $this->adaptDate(5, 1) . "<br />";
    	
    	$dba = Zend_Db_Table::getDefaultAdapter();
    	$dba->query("TRUNCATE `treasury_balance_sheet` ; TRUNCATE `treasury_capital_users` ; TRUNCATE `treasury_cashflow` ; TRUNCATE `treasury_departure` ; TRUNCATE `treasury_revaluation` ;" .
    				"TRUNCATE `treasury_transactions`; TRUNCATE `treasury_transac_cash`");
    	
    	
    	$treasury = new Model_Treasury_Treasury(array('date' => $this->adaptDate(5, 28), 'clubCreationDate' => $this->adaptDate(1, 1)));
   		$portfolio = new Model_Portfolio_Portfolio(array('treasuryRef' => $treasury));
   		
   		$treasury->setDebugMode(true);
   		// Month 01
    	echo "Push month 01<br />";
   		$ind = 1;
    	while ($ind <= 10) {
    		echo "Cotisations<br />";
    		$inputData = array('club_user_id' => $ind, 'value' => 185, 'comment' => "credit from user $ind", 'date' => $this->adaptDate(1,1));
    		$treasury->addContribution($inputData);
    		$ind++;
    	}
    	echo "Add Buy<br />";
    	$inputData = array('date' => $this->adaptDate(1,2), 'symbol' => 'actionZ', 'currency' => 'EUR', 'price' => 115, 'shares' => 10, 'fees' => 0, 'force' => true);
    	$portfolio->addBuy($inputData);
    	
    	// Month 02
    	echo "Push month 02<br />";
    	$ind = 1;
    	while ($ind <= 10) {
    		echo "Cotisations<br />";
    		$inputData = array('club_user_id' => $ind, 'value' => 500, 'comment' => "credit from user $ind", 'date' => $this->adaptDate(2,5));
    		$treasury->addContribution($inputData);
    		$ind++;
    	}
    	echo "Add Buy<br />";
    	$inputData = array('date' => $this->adaptDate(2,6), 'symbol' => 'actionX', 'currency' => 'EUR', 'price' => 308, 'shares' => 5, 'fees' => 0, 'force' => true);
    	$portfolio->addBuy($inputData);
    	echo "Add Buy<br />";
    	$inputData = array('date' => $this->adaptDate(2,6), 'symbol' => 'actionY', 'currency' => 'EUR', 'price' => 77, 'shares' => 10, 'fees' => 0, 'force' => true);
    	$portfolio->addBuy($inputData);
    	
    	// Month 03
    	echo "Push month 03<br />";
    	$ind = 1;
    	while ($ind <= 10) {
    		echo "Cotisations<br />";
    		$inputData = array('club_user_id' => $ind, 'value' => 185, 'comment' => "credit from user $ind", 'date' => $this->adaptDate(3,5));
    		$treasury->addContribution($inputData);
    		$ind++;
    	}
    	echo "Add Buy<br />";
    	$inputData = array('date' => $this->adaptDate(3,7), 'stock_id' => 5, 'price' => 50, 'shares' => 1, 'fees' => 0);
    	$treasury->addDividend($inputData);
    	$inputData = array('date' => $this->adaptDate(3,8), 'symbol' => 'actionX', 'currency' => 'EUR', 'price' => 306/*692*/, 'shares' => 5, 'fees' => 0, 'force' => true);
    	$portfolio->addSell($inputData);
    	echo "Add Buy<br />";
    	$inputData = array('date' => $this->adaptDate(3,8), 'symbol' => 'actionP', 'currency' => 'EUR', 'price' => 104, 'shares' => 30, 'fees' => 0, 'force' => true);
    	$portfolio->addBuy($inputData);
    	echo "Add Buy<br />";
    	$inputData = array('date' => $this->adaptDate(3,8), 'symbol' => 'actionQ', 'currency' => 'EUR', 'price' => 51, 'shares' => 20, 'fees' => 0, 'force' => true);
    	$portfolio->addBuy($inputData);
    	echo "Add Buy<br />";
    	$inputData = array('date' => $this->adaptDate(3,8), 'symbol' => 'actionR', 'currency' => 'EUR', 'price' => 123, 'shares' => 10, 'fees' => 0, 'force' => true);
    	$portfolio->addBuy($inputData);
    	
    	// Month 04
    	echo "Push month 04<br />";
    	echo "Add Sell<br />";
    	$inputData = array('date' => $this->adaptDate(4,1), 'symbol' => 'actionY', 'currency' => 'EUR', 'price' => 77, 'shares' => 10, 'fees' => 0, 'force' => true);
    	$portfolio->addSell($inputData);
    	echo "Add Sell<br />";
    	$inputData = array('date' => $this->adaptDate(4,2), 'symbol' => 'actionZ', 'currency' => 'EUR', 'price' => 115, 'shares' => 10, 'fees' => 0, 'force' => true);
    	$portfolio->addSell($inputData);
    	// Add quit
    	$ind = 1;
    	while ($ind <= 10) {
    		echo "Cotisations<br />";
    		$inputData = array('club_user_id' => $ind, 'value' => 185, 'comment' => "credit from user $ind", 'date' => $this->adaptDate(4,5));
    		$treasury->addContribution($inputData);
    		$ind++;
    	}
    	
    	echo "Add Buy<br />";
    	$inputData = array('date' => $this->adaptDate(4,28), 'symbol' => 'actionS', 'currency' => 'EUR', 'price' => 228, 'shares' => 5, 'fees' => 0, 'force' => true);
    	$portfolio->addBuy($inputData);
    	echo "Add Buy<br />";
    	$inputData = array('date' => $this->adaptDate(1,11), 'symbol' => 'ILD.PA', 'currency' => 'EUR', 'price' => 98.2, 'shares' => 5, 'fees' => 0, 'force' => true);
    	$portfolio->addBuy($inputData);
    	echo "Add Sell<br />";
    	$inputData = array('date' => $this->adaptDate(2,15), 'symbol' => 'ILD.PA', 'currency' => 'EUR', 'price' => 112.4, 'shares' => 2, 'fees' => 0, 'force' => true);
    	//echo "date: " . $inputData['date'] . "<br />\n"; 
    	//$portfolio->getError()->flush();
    	//Zend_Debug::Dump($portfolio->addSell($inputData)->getError());
    	$portfolio->addSell($inputData);
    	echo "Add Buy<br />";
    	$inputData = array('date' => $this->adaptDate(3,5), 'symbol' => 'ILD.PA', 'currency' => 'EUR', 'price' => 112, 'shares' => 3, 'fees' => 0, 'force' => true);
    	$portfolio->addBuy($inputData);
    	echo "Add Buy<br />";
    	$inputData = array('date' => $this->adaptDate(3,18), 'symbol' => 'ILD.PA', 'currency' => 'EUR', 'price' => 106.3, 'shares' => 1, 'fees' => 8, 'force' => true);
    	$portfolio->addBuy($inputData);
    	echo "Add Sell<br />";
    	$inputData = array('date' => $this->adaptDate(3,29), 'symbol' => 'ILD.PA', 'currency' => 'EUR', 'price' => 114.5, 'shares' => 6, 'fees' => 8, 'force' => true);
    	$portfolio->addSell($inputData);
    	
    	// Contrib test
    	echo "Push Extras<br />";
    	$treasury = new Model_Treasury_Treasury(array('date' => $this->adaptDate(5, 28), 'clubCreationDate' => $this->adaptDate(1, 1)));
    	$inputData = array('club_user_id' => 1, 'value' => 185, 'comment' => "credit from user 1", 'date' => "2012-08-30");
    	$treasury->addContribution($inputData);
    	$inputData = array('club_user_id' => 2, 'value' => 185, 'comment' => "credit from user 2", 'date' => $this->adaptDate(5,16));
    	$treasury->addContribution($inputData);
    	$inputData = array('club_user_id' => 3, 'value' => 185, 'comment' => "credit from user 3", 'date' => $this->adaptDate(5,30));
    	$treasury->addContribution($inputData);
    	echo "Done !";

    	
    	$this->renderScript("xotests/index.phtml");
    }
    
    public function contribsAction()
    {
    	$contrib = new Model_Treasury_Contribution();
    	
    	$inputData = array('contribution_date' => '2012-08-18',
    						'contribution_member_1' => 45,
    						'contribution_member_2' => 128,
    						'comment' => 'test');
    	Zend_Debug::Dump($contrib->addContributionList($inputData)->getError());
    	$this->renderScript("xotests/index.phtml");
    	
	}
    
	public function graphactionAction()
	{
		// Init with this !
		//$data = array('symbol' => '^FCHI');
		//$qh = new Model_Portfolio_QuotesHistorical($data);
		//$qh->getQuote('2012-01-01', '2012-08-30');
		//$data = array('symbol' => 'ILD.PA');
		//$qh = new Model_Portfolio_QuotesHistorical($data);
		//$qh->getQuote('2012-01-01', '2012-08-30');
		$chart = new Model_Charts_Charts();
		$out = $chart->getSymbolTransactionData(array('symbol' => 'ILD.PA'));
		
	
		?>
    <script type="text/javascript" src="http://www.google.com/jsapi"></script>
  <script type="text/javascript">
    google.load('visualization', '1', {packages: ['annotatedtimeline']});
    function drawVisualization() {
      var data = new google.visualization.DataTable();
      data.addColumn('date', 'Date');
      data.addColumn('number', 'close');
      data.addColumn('string', 'title1');
      data.addColumn('string', 'text1');
      data.addColumn('number', 'profits');
      data.addColumn('string', 'title2');
      data.addColumn('string', 'text2');
      data.addColumn('number', 'compCac40');
      data.addColumn('string', 'title3');
      data.addColumn('string', 'text3');
      data.addRows([
		<?php $i = 0;foreach ($out as $id => $val): ?>
		<?php list($year,$month,$day) = @split('-', $id); $month -= 1;?>
        [new Date(<?="$year, $month, $day";?>),
        <?= $val['close'];?>, 
        <?php if (isset($val['ope'])) echo "'" . $val['ope']['ope'] . "'"; else echo "null"; ?>,
        <?php if (isset($val['ope'])) echo "'price:" . $val['ope']['price'] . " shares:" . $val['ope']['shares'] . "'"; else echo "null"; ?>,
        <?= $val['profits'];?>,
        null,
        null,
        <?= $val['cac40'];?>,
        null,
        null],
        <?php endforeach; ?>
      ]);
      _chartConfigObject = {};
      _chartConfigObject.hasVerticalScaleSetting = true;
      var annotatedtimeline = new google.visualization.AnnotatedTimeLine(
          document.getElementById('visualization'));
      annotatedtimeline.draw(data, {'displayAnnotations': true});
    }
    
    google.setOnLoadCallback(drawVisualization);
  </script>



    <div id="visualization" style="width: 900px; height: 400px;"></div>

		<?php
		Zend_Debug::Dump($out);
		$this->renderScript("xotests/index.phtml");
	}
	
	public function graphclubAction()
	{
		$chart = new Model_Charts_Charts();
		$inputData = null;
		$stats = $chart->getClubStatsData($inputData);
		
		?>
		<script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      google.setOnLoadCallback(drawChart2);
      google.setOnLoadCallback(drawChart3);
      google.setOnLoadCallback(drawChart4);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
		  ['Date', 'UVS', 'compCac'],
		  <?php foreach ($stats as $id => $val): ?>
          ['<?=$id;?>', <?=$val['unit'];?>, <?=$val['compP'];?>],
          <?php endforeach; ?>
        ]);

        var options = {
          title: 'Club Performance',
          hAxis: {title: 'Year',  titleTextStyle: {color: 'red'}}
        };

        var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
      function drawChart2() {
          var data = google.visualization.arrayToDataTable([
  		  ['Date', 'perf%', 'compCac%'],
  		  <?php foreach ($stats as $id => $val): ?>
            ['<?=$id;?>', <?=$val['perf'];?>, <?=$val['compP'];?>],
            <?php endforeach; ?>
          ]);

          var options = {
            title: 'Club Performance',
            hAxis: {title: 'Year',  titleTextStyle: {color: 'red'}}
          };

          var chart = new google.visualization.AreaChart(document.getElementById('chart_div2'));
          chart.draw(data, options);
        }
      function drawChart4() {
          var data = google.visualization.arrayToDataTable([
  		  ['Date', 'Cac40'],
  		  <?php foreach ($stats as $id => $val): ?>
            ['<?=$id;?>', <?=$val['cac40'];?>],
            <?php endforeach; ?>
          ]);

          var options = {
            title: 'Club Performance',
            hAxis: {title: 'Year',  titleTextStyle: {color: 'red'}}
          };

          var chart = new google.visualization.AreaChart(document.getElementById('chart_div4'));
          chart.draw(data, options);
        }
      function drawChart3() {
          var data = google.visualization.arrayToDataTable([
  		  ['Date', 'Profit', 'Total Profit'],
  		  <?php foreach ($stats as $id => $val): ?>
            ['<?=$id;?>', <?=$val['profit'];?>, <?=$val['total_profit'];?>],
            <?php endforeach; ?>
          ]);

          var options = {
            title: 'Club Performance',
            hAxis: {title: 'Year',  titleTextStyle: {color: 'red'}}
          };

          var chart = new google.visualization.AreaChart(document.getElementById('chart_div3'));
          chart.draw(data, options);
        }
    </script>
    <div id="chart_div"  style="width: 900px; height: 500px;"></div>
    <div id="chart_div2" style="width: 900px; height: 500px;"></div>
    <div id="chart_div4" style="width: 900px; height: 500px;"></div>
    <div id="chart_div3" style="width: 900px; height: 500px;"></div>
    
	<?php	
		
		Zend_Debug::Dump($stats);
		$this->renderScript("xotests/index.phtml");
	}
	
	public function fraisAction()
	{		
		$tr = new Model_Treasury_Treasury();

		$portfolio = new Model_Portfolio_Portfolio();
		//$portfolio->setDate("2012-08-30");
		Zend_Debug::dump($portfolio->getReevaluationList());
		$data = $portfolio->getReevaluationData("2012-12-31");
		Zend_Debug::dump($data);
		$portfolio->addReevaluation(array('stocks' => $data, 'date' => "2012-12-31"));
		$this->renderScript("xotests/index.phtml");
	}
	
	public function frais2Action()
	{
		$tr = new Model_Treasury_Treasury();
	
		//Zend_Debug::dump($tr->getTreasuryListByYear());
		$ds = new Model_Document_Datastore();
		
		Zend_Debug::dump($file = $ds->findFileById(50));
		
		$file->setDestDir('data/');
		$file->setName('MeGusta.txt');
		Zend_Debug::dump($file);
		
		echo "fraisss<br />";
		echo $file->getName() . "<br />";
		echo $file->getPath() . "<br />";
		echo $file->getRelPath() . "<br />";
		echo $file->update();
		
		
		$this->renderScript("xotests/index.phtml");
	}
	
}

