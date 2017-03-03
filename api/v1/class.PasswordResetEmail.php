<?php
require_once('class.FlipsideProfileEmail.php');

class PasswordResetEmail extends FlipsideProfileEmail
{
    public function __construct($user)
    {
        parent::__construct($user);
        $this->addToAddress($user->mail, $user->displayName);
    }

    public function getSubject()
    {
        return 'Burning Flipside Password Reset';
    }

    protected function getResetLink()
    {
        $settings = \Settings::getInstance();
        $profilesUrl = $settings->getGlobalSetting('profiles_url', 'https://profiles.burningflipside.com/');
        return $profilesUrl.'/change.php?hash='.$this->user->getPasswordResetHash();
    }

    public function getHTMLBody()
    {
        return 'Someone (quite possibly you) has requested a password reset of your Flipside account.<br/>
                To reset your password click on the link below.<br/>
                <a href="'.$this->getResetLink().'">Reset Password</a><br/>
                If you did not request this reset, don\'t worry. This email was sent only to you and your password has not been changed.<br/>
                If you receive many of these requests, you can notify the technology team (technology@burningflipside.com).<br/>
                Thank you,<br/>
                Burning Flipside Technology Team';
    }

    public function getTextBody()
    {
        return 'Someone (quite possibly you) has requested a password reset of your Flipside account.
                To reset your password copy the following URL into your browser.
                '.$this->getResetLink().'
                If you did not request this reset, don\'t worry. This email was sent only to you and your password has not been changed.
                If you receive many of these requests, you can notify the technology team (technology@burningflipside.com).
                Thank you,
                Burning Flipside Technology Team';
    }
}
?>
