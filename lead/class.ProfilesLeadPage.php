<?php
require_once('class.ProfilesPage.php');
require_once('class.FlipSession.php');
class ProfilesLeadPage extends FlipAdminPage
{
    private $is_lead;

    public function __construct($title)
    {
        parent::__construct($title);
        if($this->user == false)
        {
            $this->is_lead = false;
        }
        else
        {
            $this->is_lead = $this->user->isInGroupNamed('Leads');
            if(!$this->is_lead)
            {
                $this->is_lead = $this->user->isInGroupNamed('CC');
            }
        }
        if($this->is_lead)
        {
            $this->is_admin = $this->is_lead;
        }
        $this->add_leads_css();
        $this->add_links();
        $this->addWellKnownJS(JS_DATATABLE, false);
        $this->addWellKnownJS(JQUERY_VALIDATE);
        $this->addWellKnownJS(JS_METISMENU);
        $this->addJSByURI('../_admin/js/admin.js');
        $this->addWellKnownJS(JS_LOGIN);
    }

    protected function add_leads_css()
    {
        $this->addWellKnownCSS(CSS_DATATABLE);
        $this->addCSSByURI('../css/profiles.css');
        $this->addCSSByURI('css/lead.css');
    }

    public function add_links()
    {
        $dirMenu = array(
                'All' => 'directory.php',
                'AAR' => 'directory.php?filter=aar',
                'AFs' => 'directory.php?filter=af',
                'CC'  => 'directory.php?filter=cc',
                '360/24/7 Department' => 'directory.php?filter=360',
                'Art' => 'directory.php?filter=Art',
                'City Planning' => 'directory.php?filter=CityPlanning',
                'Communications' => 'directory.php?filter=Comm',
                'Genesis' => 'directory.php?filter=Genesis',
                'Safety' => 'directory.php?filter=Safety',
                'Site-Ops' => 'directory.php?filter=site-ops',
                'Site Prep' => 'directory.php?filter=siteprep',
                'Site Sign-Off' => 'directory.php?filter=sign-off',
                'Volunteer Coordinator' => 'directory.php?filter=vc'
                );
        $this->addLink('<span class="fa fa-dashboard"></span> Dashboard', 'index.php');
        $this->addLink('<span class="fa fa-th-list"></span> Directory', false, $dirMenu);
    }
    
    public function isAdmin()
    {
        return $this->is_lead;
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
