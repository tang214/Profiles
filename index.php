<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('class.ProfilesPage.php');
$page = new ProfilesPage('Burning Flipside Profiles');

$page->body .= '
<div id="content">
    <h1>Welcome to the Burning Flipside Profile System</h1>
    <p>This system allows you to login to the new and improved Burning Flipside website systems.</p>
    <p>This system will contain all your private data seperately so as to help prevent unwanted display of your data on www.burningflipside.com.
       Additionally, completing your profile on this site will enable you to complete ticket requests and sign up for volunteer shifts even faster than before.</p>';

$user = FlipSession::get_user();
if($user != FALSE)
{
    if(!$user->isProfileComplete())
    {
        $page->body .= '<div class="alert alert-warning alert-dismissible" role="alert">
                            <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                            Your profile is not yet complete. Click <a href="/profile.php" class="alert-link">here</a> to complete your profile.
                </div>';
    }
    $page->body .= '<h1>Need to reset your password?</h1>
    <p>You can reset your password <a href="reset.php">here.</a></p>
</div>';
}
else
{
    $page->body .= '
    <h1>Need to register for the first time?</h1>
    <p>You can sign up for an account <a href="register.php">here</a>.
    <h1>Forgot your username or password?</h1>
    <p>You can lookup a forgotten username or reset your password <a href="reset.php">here.</a></p>
</div>
<script async src="/js/index.min.js"></script>';
}

$page->print_page();
?>
