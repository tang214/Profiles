<?php
require_once('class.FlipsideProfileEmail.php');

class PasswordResetEmail extends FlipsideProfileEmail
{
    public function __construct($user)
    {
        parent::__construct($user);
        $this->addToAddress($user->getEmail(), $user->getDisplayName());
    }

    public function getSubject()
    {
        return 'Burning Flipside Password Reset';
    }

    public function getHTMLBody()
    {
        return 'Someone (quite possibly you) has requested a password reset of your Flipside account.<br/>
                To reset your password click on the link below.<br/>
                <a href="https://profiles.burningflipside.com/change.php?hash='.$this->user->getPasswordResetHash().'">Reset Password</a><br/>
                If you did not request this reset, don\'t worry. This email was sent only to you and your password has not been changed.<br/>
                If you receive many of these requests, you can notify the technology team (technology@burningflipside.com).<br/>
                Thank you,<br/>
                Burning Flipside Technology Team';
    }

    public function getTextBody()
    {
        return 'Someone (quite possibly you) has requested a password reset of your Flipside account.
                To reset your password copy the following URL into your browser.
                https://profiles.burningflipside.com/change.php?hash='.$this->user->getPasswordResetHash().'
                If you did not request this reset, don\'t worry. This email was sent only to you and your password has not been changed.
                If you receive many of these requests, you can notify the technology team (technology@burningflipside.com).
                Thank you,
                Burning Flipside Technology Team';
    }
}
?>
