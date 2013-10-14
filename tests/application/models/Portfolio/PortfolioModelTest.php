<?php
class PortfolioModelTest extends ControllerTestCase
{
    
	private function getPortfolio()
	{
		$portfolio = new Model_Portfolio_Portfolio(array('clubId' => 10));
		return ($portfolio);
	}
	
    public function testPortfolioStocksNumber()
    {
        $portfolio = $this->getPortfolio();
        $this->assertSame(7, count($portfolio->getPortfolioStocks()));
    }
    
    public function testPortfolioActiveStocksNumber()
    {
        $portfolio = $this->getPortfolio();
        $this->assertSame(4, count($portfolio->getPortfolioActiveStocks()));
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
     * Portfolio Buy Unit Tests
     */
    
    public function testPortfolioBuyInvalidSymbol()
    {
        $portfolio = $this->getPortfolio();
        $data = array('symbol' => 'MDR.PA', 'date' => '2011-04-25', 'price' => 308, 'shares' => 5, 'fees' => 4, 'currency' => 'EUR');
        $this->checkError($portfolio->addBuy($data)->getError(), 'WARNING', 'invalid symbol (no auto update)');
    }
    
    //public function testPortfolioBuyInvalidSymbolForce()
    
    public function testPortfolioBuyNotEnoughMoney()
    {
        $portfolio = $this->getPortfolio();
        $data = array('symbol' => 'ILD.PA', 'date' => '2011-04-25', 'price' => 308, 'shares' => 20, 'fees' => 4, 'currency' => 'EUR');
        $this->checkError($portfolio->addBuy($data)->getError(), 'WARNING', 'notEnoughMoney');
    }
    
    //public function testPortfolioBuyNotEnoughMoneyForce()
    
    public function testPortfolioBuyTreasuryClosed()
    {
        $portfolio = $this->getPortfolio();
        $data = array('symbol' => 'actionZ', 'date' => '2011-03-25', 'price' => 308, 'shares' => 1, 'fees' => 4, 'currency' => 'EUR');
        $this->checkError($portfolio->addBuy($data)->getError(), 'ERROR', 'treasuryClosed');
    }
    
    public function testPortfolioBuyTreasuryDontExist()
    {
        $portfolio = $this->getPortfolio();
        $data = array('symbol' => 'actionZ', 'date' => '2011-05-25', 'price' => 308, 'shares' => 1, 'fees' => 4, 'currency' => 'EUR');
        $this->checkError($portfolio->addBuy($data)->getError(), 'ERROR', 'treasuryDontExist');
    }
    
    public function testPortfolioBuyByIdNoId()
    {
        $portfolio = $this->getPortfolio();
        $data = array('stock_id' => 42, 'date' => '2011-05-25', 'price' => 308, 'shares' => 1, 'fees' => 4);
        $this->checkError($portfolio->addBuy($data)->getError(), 'ERROR', 'no such id');
    }
    
    public function testPortfolioBuyNoParam()
    {
        $portfolio = $this->getPortfolio();
        $data = array();
        $this->checkError($portfolio->addBuy($data)->getError(), 'ERROR', 'no good param provided');
    }
    
    /**
     * 
     * Portfolio Sell Unit Tests
     */
    
    public function testPortfolioSellBadId()
    {
        $portfolio = $this->getPortfolio();
        $data = array('stock_id' => 43, 'date' => '2011-04-25', 'price' => 308, 'shares' => 5, 'fees' => 4);
        $this->checkError($portfolio->addSell($data)->getError(), 'ERROR', 'no such id');
    }
    
    public function testPortfolioSellBadSymbol()
    {
        $portfolio = $this->getPortfolio();
        $data = array('symbol' => 'SDL.PA', 'date' => '2011-04-25', 'price' => 308, 'shares' => 5, 'fees' => 4, 'currency' => 'EUR');
        $this->checkError($portfolio->addSell($data)->getError(), 'ERROR', 'symbol not in list');
    }
    
    public function testPortfolioSellSymbolNotInList()
    {
        $portfolio = $this->getPortfolio();
        $data = array('symbol' => 'ILD.PA', 'date' => '2011-04-25', 'price' => 308, 'shares' => 5, 'fees' => 4, 'currency' => 'EUR');
        $this->checkError($portfolio->addSell($data)->getError(), 'ERROR', 'symbol not in list');
    }
    
    public function testPortfolioSellTreasuryClosed()
    {
        $portfolio = $this->getPortfolio();
        $data = array('symbol' => 'actionP', 'date' => '2011-03-25', 'price' => 308, 'shares' => 1, 'fees' => 4, 'currency' => 'EUR');
        $this->checkError($portfolio->addSell($data)->getError(), 'ERROR', 'treasuryClosed');
    }
    
    public function testPortfolioSellTreasuryDontExist()
    {
        $portfolio = $this->getPortfolio();
        $data = array('symbol' => 'actionP', 'date' => '2011-05-25', 'price' => 308, 'shares' => 1, 'fees' => 4, 'currency' => 'EUR');
        $this->checkError($portfolio->addSell($data)->getError(), 'ERROR', 'treasuryDontExist');
    }
    
    public function testPortfolioSellTreasuryNotEnoughShares()
    {
        $portfolio = $this->getPortfolio();
        $data = array('symbol' => 'actionS', 'date' => '2011-04-25', 'price' => 308, 'shares' => 6, 'fees' => 4, 'currency' => 'EUR');
        $this->checkError($portfolio->addSell($data)->getError(), 'ERROR', 'not enough shares');
    }
        
    public function testPortfolioSellNoParam()
    {
        $portfolio = $this->getPortfolio();
        $data = array();
        $this->checkError($portfolio->addSell($data)->getError(), 'ERROR', 'no good param provided');
    }
}