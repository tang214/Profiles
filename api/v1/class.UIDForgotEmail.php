<?php
require_once('class.FlipsideProfileEmail.php');

class UIDForgotEmail extends FlipsideProfileEmail
{
    public function __construct($user)
    {
        parent::__construct($user);
        $this->addToAddress($user->mail, $user->displayName);
    }

    public function getSubject()
    {
        return 'Burning Flipside Username Recovery';
    }

    public function getHTMLBody()
    {
        return 'Someone (quite possibly you) has requested a reminder of your Flipside username.<br/>
                Your Flipside username is <strong>'.$this->user->uid.'</strong><br/>
                If you did not request this reminder, don\'t worry. This email was sent only to you.<br/>
                If you receive many of these requests, you can notify the technology team (technology@burningflipside.com).<br/>
                Thank you,<br/>
                Burning Flipside Technology Team';
    }

    public function getTextBody()
    {
        return 'Someone (quite possibly you) has requested a reminder of your Flipside username.
                Your Flipside username is '.$this->user->uid.'
                If you did not request this reminder, don\'t worry. This email was sent only to you.
                If you receive many of these requests, you can notify the technology team (technology@burningflipside.com).
                Thank you,
                Burning Flipside Technology Team';
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
