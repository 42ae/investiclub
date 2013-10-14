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
 * Notifications View Helper
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		View
 * @subpackage	Helper
 */
class Zend_View_Helper_Notifications extends Zend_View_Helper_Abstract
{

   public function notifications()
   {
        return $this;
   }
   
   // TODO: add delete field for notifications
   public function render($notification)
   { 
       $id = $notification->notification_id;
       $type = $notification->notification_type;
       $senderId = $notification->sender_id;
       $recipientId = $notification->recipient_id;
       
       $json = json_decode($notification->json);
       $message = $json->message;
       $params = $json->params;
       
       switch ($message) {
           case 'NOTIFICATION_MEMBER_JOIN_REQUEST_FOR_USER':
               $msg = $this->memberJoinRequestForUser($params);
               return $this->format($id, $msg, $type);
           case 'NOTIFICATION_MEMBER_JOIN_REQUEST_FOR_ADMIN':
               $msg = $this->memberJoinRequestForAdmin($params);
               return $this->format($id, $msg, $type);
       }
   }
   
   public function memberJoinRequestForUser($params)
   {
       $html = sprintf($this->view->translate("NOTIFICATION_MEMBER_JOIN_REQUEST_FOR_USER"), $params->clubName);
       return $html;
   }

   public function memberJoinRequestForAdmin($params)
   {
       $acceptLink = '<a href="' 
                   . $this->view->url(array('controller'    => 'clubs',
                                            'action'        => 'join',
                                            'accept-member' => Ivc_Utils::encryptText($params->memberId)),
                                      'default',
                                      true)
                   . '">' . $this->view->translate('NOTIFICATION_MEMBER_JOIN_REQUEST_FOR_ADMIN_ACCEPT') . '</a>';
       $declineLink = '<a href="' 
                   . $this->view->url(array('controller'    => 'clubs',
                                            'action'        => 'join',
                                            'decline-member' => Ivc_Utils::encryptText($params->memberId)),
                                      'default',
                                      true)
                   . '">' . $this->view->translate('NOTIFICATION_MEMBER_JOIN_REQUEST_FOR_ADMIN_DECLINE') . '</a>'; 
       $html = sprintf($this->view->translate("NOTIFICATION_MEMBER_JOIN_REQUEST_FOR_ADMIN"), $params->firstName, $params->lastName, $acceptLink, $declineLink);
       return $html;
   }
   
   public function format($id, $message, $type)
   {
       $class = 'notification ' . $type;
       $style = ($message) ? '' : 'display:none';
       
       $html = '<div class="'. $class . '" style="' . $style . '">';
       if ($message) {
           $markAsRead = '<span class="right"><a class="mark-as-read" href="/ajax/dashboard/mark-as-read/id/'. Ivc_Utils::encryptText($id) .'"><img src="' . $this->view->baseUrl('/assets/img/icons/cross.png') .'" /></a></span>';
           $html .= '<p>' . $message . $markAsRead . '</p>';
       }
       $html .= '</div>';
       return $html;
   }
}