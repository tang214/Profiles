<?php
require_once('class.FlipJax.php');
require_once('class.FlipsideLead.php');
class LeadAJAX extends FlipJax
{
    public function get($params)
    {
        $db = new FlipsideDB('registration');
        if(isset($params['area_name']))
        {
            $leads = FlipsideLead::select_from_db($db, 'area', $params['area_name']);
            if($leads != FALSE && !is_array($leads))
            {
                $leads = array($leads);
            }
            return array('leads'=>$leads);
        }
        else
        {
            $leads = FlipsideLead::get_all_of_type($db);
            return array('leads'=>$leads);
        }
    }

    public function post($params)
    {
        $db = new FlipsideDB('registration');
        $type = new FlipsideLead();
        $type->short_name = $params['short_name'];
        $type->name = $params['name'];
        $type->area = $params['area'];
        if($type->replace_in_db($db) !== FALSE)
        {
            return self::SUCCESS;
        }
        else
        {
            return array('err_code' => self::INTERNAL_ERROR, 'reason' => "Unable to save in DB!");
        }
    }
}

$ajax = new LeadAJAX();
$ajax->run();
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>
