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
 * @package		Ivc_Form
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * Autocomplete symbol element
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Form
 * @subpackage	Element
 */
class Ivc_Form_Element_AutocompleteYqlSymbol extends ZendX_JQuery_Form_Element_AutoComplete
{
    /* @var string */
    protected $_column = 'broker_id';
    /* @var string */
    protected $_table;

    protected $_translatorDisabled = true;

    public function init()
    {
        $brokers = array();
        $this->_table = 'Ivc_Model_Clubs_DbTable_Brokers';
        
//        if (isset($this->_table)) {
//            /* @var $table Zend_Db_Table_Abstract */
//            $table = new $this->_table();
//            $select = $table->select();
//            $select->from($table, array('id' => $this->_column, 'value' => 'name', 'url', 'country'))
//                   ->where("is_default = ?", true)
//                   ->group($this->_column);
//            $adapter = $table->getAdapter();
//            $brokers = $adapter->fetchAll($select);
//            foreach ($brokers as $key => $broker) {
//                $brokers[$key]['icon'] = $broker['id'] . '.png';
//            }
//        }
        
        $this->setJQueryParam('focus', new Zend_Json_Expr('function( event, ui ) { 
        	$( "#symbol" ).val(ui.item.value); 
        	return false;
    	}'));
        $this->setJQueryParam('open', new Zend_Json_Expr('function(event, ui) { 
        	$(this).autocomplete("widget")
        	.css({
        		"width": 550 
    		});
    	}'));
        
//          $( "#stock-name" ).html( \'<img src="/assets/img/sprites/blank.png" class="flag \' + ui.item.country.toLowerCase() + \'" alt="\' + ui.item.country + \'" />\' + \' \' + \'<span style="font-weight:bold">\' + ui.item.value + \'</span>\' + \'<br />\' + \'<a href="\' + ui.item.url + \'" title="\' + ui.item.value + \'">\' + ui.item.url + \'</a>\' );
//        	$( "#symbol-icon" ).attr( "src", "/assets/img/brokers/" + ui.item.icon );
//        	$( "#broker-icon" ).css( "display", "block" ); 
        
        $this->setJQueryParam('select', new Zend_Json_Expr('function( event, ui ) {
        console.log(ui.item);
        	$( "#symbol" ).val( ui.item.value );
        	$.ajax({
				url: "http://query.yahooapis.com/v1/public/yql",
          		dataType: "jsonp",
          		data: {
            		q: "select symbol,name,price,time,exchange from csv where url=\'http://download.finance.yahoo.com/d/quotes.csv?s=" + ui.item.value + "&f=snl1t1x&e=.csv\' and columns=\'symbol,name,price,time,exchange\'",
            		format: "json",
            	callback: "cbfunc"
          		},
                success: function(html) {
                	console.log(html);
                	console.log("success");
                	$("#stock-name").html(html.query.results.row.name);
                	$("#stock-symbol").html(html.query.results.row.symbol);
                	$("#stock-price").html(html.query.results.row.price);
                	$("#stock-time").html(html.query.results.row.time);
                	$("#stock-exchange").html(html.query.results.row.exchange);
                   	$("#stock-info").css("display", "block"); 
    			},
                error: function(){
                },
                complete: function(){
                }
            });
        	return false;
    	}'));
        // TODO: verifier tous les retours de la request Ajax (if html.query exists) ... - ex: ^FCHI symbol
        
        $this->setJQueryParam('source', new Zend_Json_Expr('function( request, response ) {
			$.ajax({
				type: "GET",
        		dataType: "jsonp",
        		jsonp: "callback",
        		jsonpCallback: "YAHOO.Finance.SymbolSuggest.ssCallback",
     		    data: {
            		query: request.term
        		},
        		cache: true,
        		url: "http://autoc.finance.yahoo.com/autoc",
				
			});
			YAHOO.Finance.SymbolSuggest.ssCallback = function (data) {
        		console.log(data.ResultSet.Result);
        		response( $.map( data.ResultSet.Result, function( item ) { 
				    return {
					    label: item,
					    value: item.symbol
				    }
			    }))
        	}
		}'));
        $this->getView()->jQuery()->addJavascript('var YAHOO={Finance:{SymbolSuggest:{}}};');
        $this->getView()->jQuery()->addOnLoad('$.ui.autocomplete.prototype._renderItem = function (ul, item) { 
        	if (typeof(item.label.exchDisp) == "undefined")
        		item.label.exchDisp = "";
            item.label.name = item.label.name.replace(new RegExp("(?![^&;]+;)(?!<[^<>]*)(" + $.ui.autocomplete.escapeRegex(this.term) + ")(?![^<>]*>)(?![^&;]+;)", "gi"), \'<span style="font-weight:bold">$1</span>\');
            return $("<li></li>")
                    .data("item.autocomplete", item)
                    .append("<a><span class=\'symbol\'>" + item.label.symbol + "</span></a>" + "<span class=\'name\'>" + item.label.name + "</span>" + "<span class=\'exchange\'>" + item.label.exchDisp + "</span>")
                    .appendTo(ul);
        };');
    }

    public function setColumn($column)
    {
        $this->_column = $column;
    }

    public function setTable($table)
    {
        $this->_table = $table;
    }

    public function setWhere($where)
    {
        $this->_where = $where;
    }
}
?>