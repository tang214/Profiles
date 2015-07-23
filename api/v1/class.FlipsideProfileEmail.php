<?php
require_once('Autoload.php');

class FlipsideProfileEmail extends \Email\Email
{
    protected $user;

    public function __construct($user)
    {
        parent::__construct();
        $this->user = $user;
    }

    public function getFromAddress()
    {
        return 'Burning Flipside Profile System <webmaster@burningflipside.com>';
    }
}
?>
