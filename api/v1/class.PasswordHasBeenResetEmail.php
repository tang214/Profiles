<?php
require_once('class.FlipsideProfileEmail.php');

class PasswordHasBeenResetEmail extends FlipsideProfileEmail
{
    protected $ip_addr;
    protected $forwarded_for;
 
    public function __construct($user, $ip_addr, $forwarded_for = false)
    {
        parent::__construct($user);
        $this->ip_addr = $ip_addr;
        if($forwarded_for !== false)
        {
            $this->forwarded_for = 'Behind Proxy: '.$forwarded_for;
        }
        else
        {
            $this->forwarded_for = '';
        }
        $this->addToAddress($user->getEmail(), $user->getDisplayName());
    }

    public function getSubject()
    {
        return 'Burning Flipside Password Reset Notification';
    }

    public function getHTMLBody()
    {
        return 'Someone (quite possibly you) has changed your Flipside password.<br/>
                If you did not request this change please notify the technology team (technology@burningflipside.com).<br/>
                IP Address: '.$this->ip_addr.'<br/>
                '.$this->forwarded_for.'<br/>
                Thank you,<br/>
                Burning Flipside Technology Team';
    }

    public function getTextBody()
    {
        return 'Someone (quite possibly you) has changed your Flipside password.
                If you did not request this change please notify the technology team (technology@burningflipside.com).
                IP Address: '.$this->ip_addr.'
                '.$this->forwarded_for.'
                Thank you,
                Burning Flipside Technology Team';
    }
}
?>
