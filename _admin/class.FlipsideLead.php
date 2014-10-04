<?php
require_once('class.FlipsideDBObject.php');
class FlipsideLead extends FlipsideDBObject
{
    protected $_tbl_name = 'position';

    public $short_name;
    public $name;
    public $area;
}
?>
