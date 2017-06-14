<?php
class GroupsAPI extends Http\Rest\RestAPI
{
    public function setup($app)
    {
        $app->get('[/]', array($this, 'getGroups'));
        $app->get('/{name}[/]', array($this, 'getGroup'));
        $app->patch('/{name}[/]', array($this, 'updateGroup'));
        $app->get('/{name}/non-members', array($this, 'getNonMembers'));
    }

    public function validateLoggedIn($request)
    {
        $this->user = $request->getAttribute('user');
        if($this->user === false)
        {
            throw new Exception('Must be logged in', \Http\Rest\ACCESS_DENIED);
        }
    }

    public function validateIsAdmin($request, $nonFatal = false)
    {
        $this->user = $request->getAttribute('user');
        if($this->user === false)
        {
            throw new Exception('Must be logged in', \Http\Rest\ACCESS_DENIED);
        }
        if(!$this->user->isInGroupNamed('LDAPAdmins'))
        {
            if($nonFatal)
            {
                return false;
            }
            throw new Exception('Must be Admin', \Http\Rest\ACCESS_DENIED);
        }
        return true;
    }

    public function getGroups($request, $response, $args)
    {
        if($this->validateIsAdmin($request, true) === false)
        {
            return $response->withStatus(301)->withHeader('Location', '../users/me/groups');
        }
        $auth = AuthProvider::getInstance();
        $odata = $request->getAttribute('odata', new \ODataParams(array()));
        $groups = $auth->getGroupsByFilter($odata->filter, $odata->select, $odata->top, $odata->skip, 
                                           $odata->orderby);
        return $response->withJson($groups);
    }

    private function expandGroupMembers($group, $odata, $directOnly)
    {
        if($odata->expand !== false && in_array('member', $odata->expand))
        {
            $ret = array();
            $ret['cn'] = $group->getGroupName();
            $ret['description'] = $group->getDescription();
            $ret['member'] = $group->members(true, ($directOnly !== true));
            return json_decode(json_encode($ret), true);
        }
        else if($directOnly)
        {
            $ret = array();
            $ret['cn'] = $group->getGroupName();
            $ret['description'] = $group->getDescription();
            $ret['member'] = $group->getMemberUids(false);
            return json_decode(json_encode($ret), true);
        }
        return json_decode(json_encode($group), true);
    }

    private function getGroupForUserByName($name)
    {
        $groups = $this->user->getGroups();
        $count = count($groups);
        for($i = 0; $i < $count; $i++)
        {
            if(strcasecmp($groups[$i]->getGroupName(), $name) === 0)
            {
                return $groups[$i];
            }
        }
        return false;
    }

    public function getGroup($request, $response, $args)
    {
        $odata = $request->getAttribute('odata', new \ODataParams(array()));
        $group = false;
        $expand = false;
        if($this->validateIsAdmin($request, true) === false)
        {
            $group = $this->getGroupForUserByName($args['name']);
        }
        else
        {
            $auth = AuthProvider::getInstance();
            $group = $auth->getGroupByName($args['name']);
            $expand = true;
        }
        if(empty($group))
        {
            return $response->withStatus(404);
        }
        $params = $request->getQueryParams();
        $directOnly = false;
        if(isset($params['directOnly']) && $params['directOnly'] === 'true')
        {
            $directOnly = true;
        }
        if($expand)
        {
            $group = $this->expandGroupMembers($group, $odata, $directOnly);
        }
        return $response->withJson($group);
    }

    public function getAllGroupsAndUsers($keys)
    {
        $auth = AuthProvider::getInstance();
        $groups = $auth->getGroupsByFilter(false);
        $count  = count($groups);
        $res = array();
        for($i = 0; $i < $count; $i++)
        {
            $tmp = json_decode(json_encode($groups[$i]), true);
            $tmp['type'] = 'Group';
            if($keys !== false)
            {
                $tmp = array_intersect_key($tmp, $keys);
            }
            $res[] = $tmp;
        }
        $users  = $auth->getUsersByFilter(false);
        $count  = count($users);
        for($i = 0; $i < $count; $i++)
        {
            $tmp = json_decode(json_encode($users[$i]), true);
            $tmp['type'] = 'User';
            if($keys !== false)
            {
                $tmp = array_intersect_key($tmp, $keys);
            }
            $res[] = $tmp;
        }
        return $res;
    }

    public function getTypeOfEntity($entity)
    {
        if(is_subclass_of($entity, 'Auth\Group'))
        {
            return 'Group';
        }
        else
        {
            return 'User';
        }
    }

    public function getNonMemberEntities($nonMembers, $keys)
    {
        if($keys !== false)
        {
            $count = count($nonMembers);
            for($i = 0; $i < $count; $i++)
            {
                $tmp = json_decode(json_encode($nonMembers[$i]), true);
                $tmp['type'] = $this->getTypeOfEntity($nonMembers[$i]);
                $nonMembers[$i] = array_intersect_key($tmp, $keys);
            }
        }
        return $nonMembers;
    }

    public function getNonMembers($request, $response, $args)
    {
        $this->validateIsAdmin($request);
        $odata = $request->getAttribute('odata', new \ODataParams(array()));
        $keys = false;
        if($odata->select !== false)
        {
            $keys = array_flip($odata->select);
        }
        $auth = AuthProvider::getInstance();
        if($args['name'] === 'none')
        {
            $res = $this->getAllGroupsAndUsers($keys);
            return $response->withJson($res);
        }
        $group = $auth->getGroupByName($args['name']);
        if($group === false)
        {
            return $response->withStatus(404);
        }
        $res = $group->getNonMembers($odata->select);
        $res = $this->getNonMemberEntities($res, $keys);
        return $response->withJson($res);
    }

    public function updateGroup($request, $response, $args)
    {
        $this->validateIsAdmin($request);
        $auth = AuthProvider::getInstance();
        $group = $auth->getGroupByName($args['name']);
        if($group === false)
        {
            return $response->withStatus(404);
        }
        $obj = $request->getParsedBody();
        $ret = $group->editGroup($obj);
        return $response->withJson($ret);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
