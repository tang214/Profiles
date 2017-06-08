<?php
class LeadsAPI extends Http\Rest\DataTableAPI
{
    public function __construct()
    {
        parent::__construct('profiles', 'position', 'short_name');
    }

    public function setup($app)
    {
        parent::setup($app);
    }

    protected function validateIsAdmin($request)
    {
        $user = $request->getAttribute('user');
        if($user === false)
        {
            throw new Exception('Must be logged in', \Http\Rest\ACCESS_DENIED);
        }
        if(!$user->isInGroupNamed('LDAPAdmins'))
        {
            throw new Exception('Must be Admin', \Http\Rest\ACCESS_DENIED);
        }
    }

    protected function canCreate($request)
    {
        $this->validateIsAdmin($request);
        return true;
    }

    protected function canUpdate($request, $entity)
    {
        $this->validateIsAdmin($request);
        return true;
    }

    protected function hasPositionAccess()
    {
        return ($this->user->isInGroupNamed('Leads') ||
                $this->user->isInGroupNamed('CC') ||
                $this->user->isInGroupNamed('AFs'));
    }

    protected function getPositionsByType($type, $auth)
    {
        switch($type)
        {
            case 'aar':
                $aarGroup = $auth->getGroupByName('AAR');
                return $aarGroup->members(true, false);
            case 'af':
                $afGroup = $auth->getGroupByName('AFs');
                return $afGroup->members(true, false);
            case 'cc':
                $ccGroup = $auth->getGroupByName('CC');
                return $ccGroup->members(true, false);
            case 'lead':
                $leadGroup = $auth->getGroupByName('Leads');
                return $leadGroup->members(true, false);
            default:
                $filter = new \Data\Filter('ou eq '.$type);
                return $auth->getUsersByFilter($filter);
        }
    }

    protected function getPositionsWithParams($params)
    {
        $auth = AuthProvider::getInstance();
        if(isset($params['type']))
        {
            return $this->getPositionsByType($params['type'], $auth);
        }
        $leads = array();
        $leadGroup = $auth->getGroupByName('Leads');
        $aarGroup  = $auth->getGroupByName('AAR');
        $afGroup   = $auth->getGroupByName('AFs');
        $ccGroup   = $auth->getGroupByName('CC');
        $leads     = array_merge($leads, $leadGroup->members(true, false));
        $leads     = array_merge($leads, $aarGroup->members(true, false));
        $leads     = array_merge($leads, $afGroup->members(true, false));
        $leads     = array_merge($leads, $ccGroup->members(true, false));
        return $leads;
    }

    public function readEntries($request, $response, $args)
    {
        if($this->canRead($request) === false || $this->hasPositionAccess() === false)
        {
            return $response->withStatus(401);
        }
        $dataTable = $this->getDataTable();
        $odata = $request->getAttribute('odata', new \ODataParams(array()));
        $leads = $this->getPositionsWithParams($request->getQueryParams());
        $leads = $odata->filterArrayPerSelect($leads);
        return $response->withJson($leads);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
