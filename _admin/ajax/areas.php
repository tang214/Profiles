<?php
require_once('class.FlipJax.php');
require_once('class.FlipsideArea.php');
class AreaAJAX extends FlipJax
{
    public function get($params)
    {
        $db = new FlipsideDB('registration');
        $areas = FlipsideArea::get_all_of_type($db);
        return array('areas'=>$areas);
    }

    public function post($params)
    {
        $db = new FlipsideDB('registration');
        $type = new FlipsideArea();
        $type->short_name = $params['short_name'];
        $type->name = $params['name'];
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

$ajax = new AreaAJAX();
$ajax->run();
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>
