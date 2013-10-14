<?php
class Ivc_Profiler extends Zend_Db_Profiler_Firebug
{
    public function queryEnd($queryId) 
    {
        //echo '<pre>';var_dump(debug_print_backtrace());
        return parent::queryEnd($queryId);
    }
    
}