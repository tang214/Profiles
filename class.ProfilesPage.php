<?php
require_once('class.FlipPage.php');
require_once('class.FlipSession.php');
class ProfilesPage extends FlipPage
{
    function __construct($title)
    {
        parent::__construct($title, true);
        $this->add_css();
        $this->add_script();
        $this->add_sites();
        $this->add_links();
        $this->add_login_form();
    }

    function add_css()
    {
        $css_tag = $this->create_open_tag('link', array('rel'=>'stylesheet', 'href'=>'css/profiles.css', 'type'=>'text/css'), true);
        $this->add_head_tag($css_tag);
    }

    function add_sites()
    {
        $this->add_site('Profiles', 'https://profiles.burningflipside.com');
        $this->add_site('WWW', 'http://www.burningflipside.com');
        $this->add_site('Pyropedia', 'http://wiki.burningflipside.com');
        $this->add_site('Secure', 'https://secure.burningflipside.com');
    }

    function add_links()
    {
        if(!FlipSession::is_logged_in())
        {
            $this->add_link('Login', 'https://profiles.burningflipside.com/login.php');
        }
        else
        {
            $this->user = FlipSession::get_user(TRUE);
            if($this->user != FALSE && $this->user->isInGroupNamed("LDAPAdmins"))
            {
                $this->add_link('Admin', 'https://profiles.burningflipside.com/_admin/index.php');
            }
            $this->add_link('My Profile', 'https://profiles.burningflipside.com/profile.php');
            $this->add_link('Logout', 'https://profiles.burningflipside.com/logout.php');
        }
        $about_menu = array(
            'Burning Flipside'=>'http://www.burningflipside.com/about/event',
            'AAR, LLC'=>'http://www.burningflipside.com/about/aar',
            'Privacy Policy'=>'http://www.burningflipside.com/about/privacy'
        );
        $this->add_link('About', 'http://www.burningflipside.com/about', $about_menu);
    }

    function add_script()
    {
        $script_start_tag = $this->create_open_tag('script', array('src'=>'js/login.js'));
        $script_close_tag = $this->create_close_tag('script');
        $this->add_head_tag($script_start_tag.$script_close_tag);
    }

    function add_login_form()
    {
        $this->body .= '<div id="login-form" title="Login" style="display: none;">
                            <fieldset>
                                <form action="/login.php" method="post" name="form">
                                    <table>
                                        <tr><td>Username or email:</td><td><input type="text" name="username"/></td></tr>
                                        <tr><td>Password:</td><td><input type="password" name="password"/></td></tr>
                                        <tr><td>&nbsp;</td><td><input type="submit" name="submit" value="Login"/></td></tr>
                                    </table>
                                </form>
                            </fieldset>
                        </div>';
    }
}
?>
