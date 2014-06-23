<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
//Redirect users to https
if($_SERVER["HTTPS"] != "on")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}
require_once("class.FlipsideLDAPServer.php");
require_once("class.FlipSession.php");
$server = new FlipsideLDAPServer();
if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST')
{
    /*This is a post*/
    $res = $server->testLogin($_POST["username"], $_POST["password"]);
    if($res == FALSE)
    {
        $res = $server->testLoginByEmail($_POST["username"], $_POST["password"]);
    }
    if($res == FALSE)
    {
        echo "Login Failed!";
    }
    else
    {
        $users = $server->getUsers("(uid=".$_POST["username"].")");
        if($users == FALSE)
        {
            $users = $server->getUsers("(mail=".$_POST["username"].")");
        }
        if($users == FALSE)
        {
            echo "Invalid Username or Password!";
        }
        else
        {
            FlipSession::set_user($users[0]);
            if(isset($_POST['return']))
            {
                 $return_url = $_POST['return'];
            }
            else
            {
                 $return_url = $_SERVER["HTTP_REFERER"];
            }
?>
<script type="text/javascript">
<!--
window.location = "<?php echo $return_url;?>"
//-->
</script>
<?php
        }
    }
}
else
{
require_once('class.ProfilesPage.php');
$page = new ProfilesPage('Burning Flipside Profiles Login');

if(isset($_GET['return']))
{
    $return = '<input type="hidden" name="return" value="'.$_GET['return'].'"/>';
}
else
{
    $return = '';
}

$page->body = '
<div id="content">
    <h3>Burning Flipside Profile Login</h3>
    <form action="login.php" method="post" name="form">
        <table>
            <tr><td>Username or email:</td><td><input type="text" name="username"/></td></tr>
            <tr><td>Password:</td><td><input type="password" name="password"/></td></tr>'.$return.'
            <tr><td>&nbsp;</td><td><input type="submit" name="submit" value="Login"/></td></tr>
        </table>
    </form>
</div>';

$page->print_page();
}
// vim: set tabstop=4 shiftwidth=4 expandtab:
?>


