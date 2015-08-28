<?php
require_once('class.ProfilesPage.php');
require_once('class.FlipSession.php');
class ProfilesLeadPage extends FlipAdminPage
{
    private $is_lead;

    function __construct($title)
    {
        parent::__construct($title, 'Leads');
        if($this->user !== false && $this->user !== null)
        {
            $this->is_lead  = $this->user->isInGroupNamed('CC');
        }
        else
        {
            $this->is_lead  = true;
        }
        $this->add_leads_css();
        $this->add_links();
        $this->add_js(JS_DATATABLE);
        $this->add_js(JQUERY_VALIDATE);
        $this->add_js(JS_METISMENU);
        $this->add_js_from_src('../_admin/js/admin.js');
        $this->add_js(JS_LOGIN);
    }

    function add_leads_css()
    {
        $this->add_css(CSS_DATATABLE);
        $this->add_css_from_src('../css/profiles.css');
        $this->add_css_from_src('css/lead.css');
    }

    function add_links()
    {
        $dir_menu = array(
            'All' => 'directory.php',
            'AAR' => 'directory.php?filter=aar',
            'AFs' => 'directory.php?filter=af',
            'CC'  => 'directory.php?filter=cc',
            '360/24/7 Department' => 'directory.php?filter=360',
            'Art' => 'directory.php?filter=Art',
            'City Planning' => 'directory.php?filter=CityPlanning',
            'Communications' => 'directory.php?filter=Comm',
            'Safety' => 'directory.php?filter=Safety',
            'Site-Ops' => 'directory.php?filter=site-ops',
            'Site Prep' => 'directory.php?filter=siteprep', 
            'Site Sign-Off' => 'directory.php?filter=sign-off',
            'Volunteer Coordinator' => 'directory.php?filter=vc'
        );
        $this->add_link('<i class="fa fa-tachometer"></i> Dashboard', 'index.php');
        $this->add_link('<i class="fa fa-th-list"></i> Directory', '#', $dir_menu);
    }

    function print_page($header=true)
    {
        if($this->user == FALSE)
        {
            $this->body = '
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">You must <a href="/login.php?return='.$this->current_url().'">log in <span class="glyphicon glyphicon-log-in"></span></a> to access the Burning Flipside Profile Admin system!</h1>
            </div>
        </div>';
        }
        else if(!$this->is_lead)
        {
            $this->body = '
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">You must be a lead to access this page!</h1>
            </div>
        </div>';
        }
        parent::print_page(true);
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
                                            <input type="hidden" name="return" value="'.$this->current_url().'"/>
                                            <button class="btn btn-lg btn-primary btn-block" type="submit">Login</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>';
    }
}
?>
