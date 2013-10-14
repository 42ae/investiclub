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
 * @package		Controller
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * Dashboard controller
 * 
 * Shows general information to users, as well as notification and charts 
 * about their personal portfolio and/or club portfolio.
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		Controller
 */
class DashboardController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->hideBreadcrumbs = true;
        $this->view->hideSidebar = true;
    }

    /**
     * Index action
     * 
     * Redirect to the view action
     */
    public function indexAction()
    {
        // Check dashboard ACL
        $dashboard = new Model_Dashboard_Dashboard;
        $dashboard->index();
        
        $user = Ivc::getCurrentUser();
        $data = array();
        
        if (true === $user->hasClub()) {
            $member = Ivc::getCurrentMember();
            $club = $member->getClub();
            
            $treasury = new Model_Treasury_Treasury;
            $contributions = new Model_Treasury_Contribution(array('treasuryName' => Zend_Date::now()->toString(Zend_Date::ISO_8601)));
            $chart = new Model_Charts_Charts();
            
            $data['club'] = $club;
            $data['member'] = $member;
            $data['members'] = $club->getMembers();
            $data['membersStats'] = $treasury->getMembersStats();
            $data['treasury'] = $treasury->getData();
            $data['checkContributions'] = $contributions->checkContribution();
            $data['contributions'] = $contributions->getContributions();
            $data['chart'] = $chart->getClubStatsData();
            
            $portfolio = new Model_Portfolio_Portfolio();
            $data['portfolio'] = $portfolio->getData();

            $this->view->headScript()->prependFile('https://www.google.com/jsapi');
        } else {
            // TODO
        }
        
        $this->view->currentUser = $user;
        $this->view->data = $data;
    }
}
