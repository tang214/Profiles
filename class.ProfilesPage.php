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
        $css_tag = $this->create_open_tag('link', array('rel'=>'stylesheet', 'href'=>'css/jquery-ui.css', 'type'=>'text/css'), true);
        $this->add_head_tag($css_tag);

        $css_tag = $this->create_open_tag('link', array('rel'=>'stylesheet', 'href'=>'css/bootstrap.min.css', 'type'=>'text/css'), true);
        $this->add_head_tag($css_tag);

        $css_tag = $this->create_open_tag('link', array('rel'=>'stylesheet', 'href'=>'/css/bootstrap-formhelpers.min.css', 'type'=>'text/css'), true);
        $this->add_head_tag($css_tag);

        $css_tag = $this->create_open_tag('link', array('rel'=>'stylesheet', 'href'=>'css/bootstrap-theme.min.css', 'type'=>'text/css'), true);
        $this->add_head_tag($css_tag);

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
            if($this->user != FALSE && ($this->user->isInGroupNamed("Leads") || $this->user->isInGroupNamed("CC")))
            {
                $this->add_link('Leads', 'https://profiles.burningflipside.com/lead/index.php');
            }
            $this->add_link('My Profile', 'https://profiles.burningflipside.com/profile.php');
            $this->add_link('Logout', 'https://profiles.burningflipside.com/logout.php');
        }
        $about_menu = array(
            'Burning Flipside'=>'http://www.burningflipside.com/about/event',
            'AAR, LLC'=>'http://www.burningflipside.com/LLC',
            'Privacy Policy'=>'http://www.burningflipside.com/about/rivacy'
        );
        $this->add_link('About', 'http://www.burningflipside.com/about', $about_menu);
    }

    function add_script()
    {
        $this->add_js_from_src('/js/jquery.validate.js');
        $this->add_js_from_src('/js/bootstrap-formhelpers.min.js');
        $this->add_js_from_src('/js/login.js');
    }

    function add_login_form()
    {
        $this->body .= '<div class="modal fade" role="dialog" id="login-dialog" title="Login" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">
                                            <span aria-hidden="true">&times;</span>
                                            <span class="sr-only">Close</span>
                                        </button>
                                        <h4 class="modal-title">Login</h4>
                                    </div>
                                    <div class="modal-body">
                                        <form id="login_dialog_form" role="form">
                                            <input class="form-control" type="text" name="username" placeholder="Username or Email" required autofocus/>
                                            <input class="form-control" type="password" name="password" placeholder="Password" required/>
                                            <button class="btn btn-lg btn-primary btn-block" type="submit">Login</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>';
    }
}
?>
