<?php

class Ivc_Model_Users_DbTable_Notifications extends Zend_Db_Table_Abstract
{
    protected $_name = 'notifications';
    protected $_referenceMap = array(
        'User' => array(
            'columns'       => array('recipient_id'),
            'refTableClass' => 'Ivc_Model_Users_DbTable_Users',
            'refColumns'    => array('user_id')
        )
   );
}
