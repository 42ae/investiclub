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
 * @package		View
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * Portfolio View Helper
 * 
 * Renders a portoflio
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		View
 * @subpackage	Helper
 */
class Zend_View_Helper_Portfolio extends Zend_View_Helper_Abstract
{

    public function portfolio()
    {
        return $this;
    }

    public function render($portfolio)
    {
        $rows = $portfolio['portfolio'];
        $stats = $portfolio['stats'];
        
        $html = '<table class="content-table" id="portfolio">
 <thead>
  <tr class="portfolio-head">
   <th class="text-left"><span class=""> </span></th>
   <th class="text-left"><span class="">Symbole</span></th>
   <th class="text-center"><span class="">Parts</span></th>
   <th class="text-center"><span class="">Dernier prix</span></th>
   <th class="text-center"><span class="">Poids</span></th>
   <th class="text-center"><span class="">PRU</span></th>
   <th class="text-center"><span class="">Valeur au marché</span></th>
   <th class="text-center"><span class="">+/- value latente</span></th>
   <th class="text-right"><span class="">Tableau de bord</span></th>
  </tr>
 </thead>
 <tfoot>
  <tr class="portfolio-foot">
   <td class="" colspan="4">Valeur totale du portefeuille :</td>
   <td class="text-center">100%</td>
   <td class="text-center">' . round($stats["totalCostPrice"], 2) . '</td>
   <td class="text-center">' . round($stats["totalMarketValue"], 2) . '</td>
   <td class="text-center"><span style="' . (round($stats["totalVirtualGain"], 2) >= 0 ? "color:#6fa03d" : "color:#de3f23") . '">' . round($stats["totalVirtualGain"], 2) . ' (' . round($stats["totalVirtualGainP"], 2) . '%)</span></td>
   <td class=""></td>
  </tr>
 </tfoot>' .
	$this->view->partialLoop('finance/partials/_portfolio.phtml', $rows) .'
</table>';
        return $html;
    }
    
    
}