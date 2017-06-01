<?php

function groups()
{
    global $app;
    $app->get('(/)', 'listGroups');
    $app->get('/:name(/)', 'getGroup');
    $app->patch('/:name(/)', 'updateGroup');
    $app->get('/:name/non-members(/)', 'getNonGroupMembers');
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
        $groups = $auth->getGroupsByFilter($app->odata->filter, $app->odata->select, $app->odata->top, $app->odata->skip, $app->odata->orderby);
        echo json_encode($groups);
    }
    else
    {
        list_groups_for_user();
    }
}

function expandGroupMembers($group, $odata, $directOnly)
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

function getFlippedKeys($keys)
{
    $ret = array();
    $count = count($keys);
    for($i = 0; $i < $count; $i++)
    {
        $key = $keys[$i];
        if(strstr($key, '.'))
        {
            $parts = explode('.', $key);
            $tmp = array_shift($parts);
            if(!isset($ret[$tmp]))
            {
                $ret[$tmp] = array();
            }
            $ret[$tmp][] = $parts[0];
            continue;
        }
        $ret[$key] = 1;
    }
    return $ret;
}

function selectFieldsFromGroup($group, $select)
{
    if($select !== false)
    {
        $flipped = getFlippedKeys($select);
        foreach($flipped as $key=>$value)
        {
            if($value !== 1)
            {
                $tmp = array_flip($value);
                if(isset($group[$key][0]))
                {
                    $count = count($group[$key]);
                    for($i = 0; $i < $count; $i++)
                    {
                        if(is_array($group[$key][$i]))
                        {
                            $group[$key][$i] = array_intersect_key($group[$key][$i], $tmp);
                        }
                    }
                    continue;
                }
                $group[$key] = array_intersect_key($group[$key], $tmp);
            }
        }
    }
    return $group;
}

function getGroupForUserByName($name, $app)
{
    $groups = $app->user->getGroups();
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

function getGroup($name)
{
    global $app;
    if(!hasUser($app))
    {
        $app->response->setStatus(401);
        return;
    }
    if(isAdmin($app))
    {
        $auth = AuthProvider::getInstance();
        $users = $auth->getGroupByName($name);
        $params = $app->request->params();
        $directOnly = false;
        if(isset($params['directOnly']) && $params['directOnly'] === 'true')
        {
            $directOnly = true;
        }
        $users = expandGroupMembers($users, $app->odata, $directOnly);
        $users = selectFieldsFromGroup($users, $app->odata->select);
        echo json_encode($users);
    }
    else
    {
        $group = getGroupForUserByName($name, $app);
        if($group === false)
        {
            $app->notFound();
            return;
        }
        echo json_encode($group);
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

function getTypeOfEntity($entity)
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

function getAllGroupsAndUsers($keys)
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

function getNonMemberEntities($nonMembers, $keys)
{
    if($keys !== false)
    {
        $count = count($nonMembers);
        for($i = 0; $i < $count; $i++)
        {
            $tmp = json_decode(json_encode($nonMembers[$i]), true);
            $tmp['type'] = getTypeOfEntity($nonMembers[$i]);
            $nonMembers[$i] = array_intersect_key($tmp, $keys);
        }
    }
    return $nonMembers;
}

function getNonGroupMembers($name)
{
    global $app;
    if(!hasUser($app) || !isAdmin($app))
    {
        $app->response->setStatus(401);
        return;
    }
    $keys = false;
    if($app->odata->select !== false)
    {
        $keys = array_flip($app->odata->select);
    }
    $auth = AuthProvider::getInstance();
    if($name === 'none')
    {
        $res = getAllGroupsAndUsers($keys);
        echo json_encode($res);
        return;
    }
    $group = $auth->getGroupByName($name);
    if($group === false)
    {
        $app->notFound();
    }
    $res = $group->getNonMembers($app->odata->select);
    $res = getNonMemberEntities($res, $keys);
    echo json_encode($res);
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
