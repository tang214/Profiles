<?php

function groups()
{
    global $app;
    $app->get('', 'listGroups');
    $app->get('/:name', 'getGroup');
    $app->patch('/:name', 'updateGroup');
    $app->get('/:name/non-members', 'getNonGroupMembers');
}

function listGroups()
{
    global $app;
    if(!$app->user)
    {
        $app->response->setStatus(401);
        return;
    }
    if($app->user->isInGroupNamed("LDAPAdmins"))
    {
        $auth = AuthProvider::getInstance();
        $users = $auth->getGroupsByFilter($app->odata->filter, $app->odata->select, $app->odata->top, $app->odata->skip, $app->odata->orderby);
        echo json_encode($users);
    }
    else
    {
        list_groups_for_user();
    }
}

function getGroup($name)
{
    global $app;
    $isLocal = false;
    if($_SERVER['SERVER_ADDR'] === $_SERVER['REMOTE_ADDR'])
    {
        $isLocal = true;
    }
    if(!$app->user && !$isLocal)
    {
        $app->response->setStatus(401);
        return;
    }
    if(($isLocal === true) || $app->user->isInGroupNamed('LDAPAdmins'))
    {
        $auth = AuthProvider::getInstance();
        $users = $auth->getGroupByName($name);
        $params = $app->request->params();
        $directOnly = false;
        if(isset($params['directOnly']) && $params['directOnly'] === 'true')
        {
            $directOnly = true;
        }
        if($app->odata->expand !== false)
        {
            if(in_array('member', $app->odata->expand))
            {
                $group = array();
                $group['cn'] = $users->getGroupName();
                $group['description'] = $users->getDescription();
                if($directOnly)
                {
                    $group['member'] = $users->members(true, false);
                }
                else
                {
                    $group['member'] = $users->members(true);
                }
                $users = json_decode(json_encode($group), true);
            }
        }
        else if($directOnly)
        {
            $group = array();
            $group['cn'] = $users->getGroupName();
            $group['description'] = $users->getDescription();
            $group['member'] = $users->getMemberUids(false);
            $users = json_decode(json_encode($group), true);
        }
        else
        {
            $users = json_decode(json_encode($users), true);
        }
        if($app->odata->select !== false)
        {
            $keys = $app->odata->select;
            $flipped = array();
            foreach($keys as $key)
            {
                if(strstr($key, '.'))
                {
                    $parts = explode('.', $key);
                    $tmp = array_shift($parts);
                    if(!isset($flipped[$tmp]))
                    {
                        $flipped[$tmp] = array();
                    }
                    array_push($flipped[$tmp], $parts[0]);
                }
                else
                {
                    $flipped[$key] = 1;
                }
            }
            foreach($flipped as $key=>$value)
            {
                if($value !== 1)
                {
                    $tmp = array_flip($value);
                    if(isset($users[$key][0]))
                    {
                        $count = count($users[$key]);
                        for($i = 0; $i < $count; $i++)
                        {
                            $users[$key][$i] = array_intersect_key($users[$key][$i], $tmp);
                        }
                    }
                    else
                    {
                        $users[$key] = array_intersect_key($users[$key], $tmp);
                    }
                }
            }
            $users = array_intersect_key($users, $flipped);
        }
        echo json_encode($users);
    }
    else
    {
        $groups = $app->user->getGroups();
        foreach($groups as $group)
        {
            if($group->getGroupName() === $name)
            {
                echo json_encode($group);
                die();
            }
        }
        $app->notFound();
    }
}

function updateGroup($name)
{
    global $app;
    if(!$app->user->isInGroupNamed('LDAPAdmins'))
    {
        $app->response->setStatus(401);
        return;
    }
    $auth = AuthProvider::getInstance();
    $group = $auth->getGroupByName($name);
    if($group === false)
    {
        $app->notFound();
        return;
    }
    $obj = $app->getJsonBody();
    echo json_encode($group->editGroup($obj));
}

function getNonGroupMembers($name)
{
    global $app;
    $isLocal = false;
    if($_SERVER['SERVER_ADDR'] === $_SERVER['REMOTE_ADDR'])
    {
        $isLocal = true;
    }
    if(!$app->user && !$isLocal)
    {
        $app->response->setStatus(401);
        return;
    }
    if(($isLocal === false) && !$app->user->isInGroupNamed('LDAPAdmins'))
    {
        $app->response->setStatus(401);
        return;
    }
    $auth = AuthProvider::getInstance();
    if($name === 'none')
    {
        $res = array();
        $groups = $auth->getGroupsByFilter(false);
        $count  = count($groups);
        $keys   = false;
        if($app->odata->select !== false)
        {
            $keys = array_flip($app->odata->select);
        }
        for($i = 0; $i < $count; $i++)
        {
            $tmp = json_decode(json_encode($groups[$i]), true);
            $tmp['type'] = 'Group';
            if($keys !== false)
            {
                $tmp = array_intersect_key($tmp, $keys);
            } 
            array_push($res, $tmp);
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
            array_push($res, $tmp);
        }
        echo json_encode($res);
        return;
    }
    $group = $auth->getGroupByName($name);
    if($group === false)
    {
        $app->notFound();
    }
    $res = $group->getNonMemebers();
    if($app->odata->select !== false)
    {
        $count = count($res);
        $keys = array_flip($app->odata->select);
        for($i = 0; $i < $count; $i++)
        {
            $tmp = json_decode(json_encode($res[$i]), true);
            if(is_subclass_of($res[$i], 'Auth\Group'))
            {
                $tmp['type'] = 'Group';
            }
            else
            {
                $tmp['type'] = 'User';
            }
            $res[$i] = array_intersect_key($tmp, $keys);
        }
    }
    echo json_encode($res);
}

?>