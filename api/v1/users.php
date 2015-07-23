<?php
require('class.UIDForgotEmail.php');
require('class.PasswordResetEmail.php');

function users()
{
    global $app;
    $app->get('', 'list_users');
    $app->post('', 'create_user');
    $app->get('/me', 'show_user');
    $app->get('/:uid', 'show_user');
    $app->patch('/me', 'edit_user');
    $app->patch('/:uid', 'edit_user');
    $app->get('/me/groups', 'list_groups_for_user');
    $app->get('/:uid/groups', 'list_groups_for_user');
    $app->post('/me/Actions/link', 'link_user');
    $app->post('/:uid/Actions/link', 'link_user');
    $app->post('/:uid/Actions/reset_pass', 'reset_pass');
    $app->post('/Actions/check_email_available', 'check_email_available');
    $app->post('/Actions/check_uid_available', 'check_uid_available');
    $app->post('/Actions/remind_uid', 'remind_uid');
}

function list_users()
{
    global $app;
    if(!$app->user)
    {
        $app->response->setStatus(401);
        return;
    }
    if($app->user && !$app->user->isInGroupNamed("LDAPAdmins"))
    {
        //Only return this user. This user doesn't have access to other accounts
        echo json_encode(array(encode_user($app->user)));
    }
    else
    {
        $auth = AuthProvider::getInstance();
        $users = $auth->get_users_by_filter(false, $app->odata->filter, $app->odata->select, $app->odata->top, $app->odata->skip, $app->odata->orderby);
        echo json_encode($users);
    }
}

function create_user()
{
    global $app;
    //This one is different. If they are logged in fail...
    if($app->user)
    {
        $app->response->setStatus(404);
        return;
    }
    $body = $app->request->getBody();
    $obj  = json_decode($body);
    if(!isset($obj->captcha))
    {
        $app->response->setStatus(401);
        return;
    }
    $captcha = FlipSession::get_var('captcha');
    if($captcha === false)
    {
        $app->response->setStatus(401);
        return;
    }
    if(!$captcha->is_answer_right($obj->captcha))
    {
        echo json_encode(array('res'=>false, 'message'=>'Incorrect answer to CAPTCHA!'));
        return;
    }
    $auth = AuthProvider::getInstance();
    $user = $auth->get_users_by_filter(false, new \Data\Filter('mail eq '.$obj->mail));
    if($user !== false && isset($user[0]))
    {
        echo json_encode(array('res'=>false, 'message'=>'Email already exists!'));
        return;
    }
    $user = $auth->get_users_by_filter(false, new \Data\Filter('uid eq '.$obj->uid));
    if($user !== false && isset($user[0]))
    {
        echo json_encode(array('res'=>false, 'message'=>'Username already exists!'));
    }
    $ret = $auth->create_pending_user(false, $obj);
    echo json_encode($ret);
}

function show_user($uid = 'me')
{
    global $app;
    if(!$app->user)
    {
        $app->response->setStatus(401);
        return;
    }
    $user = false;
    if($uid === 'me' || $uid === $app->user->getUid())
    {
        $user = $app->user;
    }
    else if($app->user->isInGroupNamed("LDAPAdmins"))
    {
        $user = \AuthProvider::getInstance()->get_users_by_filter(false, new \Data\Filter("uid eq $uid"));
    }
    else if($app->user->isInGroupNamed("Leads") || $app->user->isInGroupNamed("CC"))
    {
        $user = \AuthProvider::getInstance()->get_users_by_filter(false, new \Data\Filter("uid eq $uid"));
    }
    if($user === false) $app->halt(404);
    if(!is_object($user) && isset($user[0]))
    {
        $user = $user[0];
    }
    if($app->fmt === 'vcard')
    {
        $app->response->headers->set('Content-Type', 'text/x-vCard');
        echo $user->getVcard();
        $app->fmt = 'passthru';
    }
    else
    {
        echo $user->serializeObject();
    }
}

function edit_user($uid = 'me')
{
    global $app;
    if(!$app->user)
    {
        $app->response->setStatus(401);
        return;
    }
    $body = $app->request->getBody();
    $obj  = json_decode($body);
    if($uid === 'me')
    {
        $app->user->edit_user($obj);
    }
    else if($uid === $app->user->getUid())
    {
        $app->user->edit_user($obj);
    }
    else if($app->user->isInGroupNamed("LDAPAdmins"))
    {
        $user = AuthProvider::getInstance()->get_user(false, $uid);
        if($user === false)
        {
            $app->response->setStatus(404);
            return;
        }
        $user->edit_user($obj);
    }
    else
    {
        $app->response->setStatus(404);
        return;
    }
    echo json_encode(array('success'=>true));
}

function list_groups_for_user($uid = 'me')
{
    global $app;
    if(!$app->user)
    {
        $app->response->setStatus(401);
        return;
    }
    $groups = false;
    if($uid === 'me' || $uid === $app->user->getUid())
    {
        $groups = $app->user->getGroups();
    }
    else if($app->user->isInGroupNamed("LDAPAdmins"))
    {
        $user = AuthProvider::getInstance()->get_user(false, $uid);
        if($user === false)
        {
            $app->response->setStatus(404);
            return;
        }
        $groups = $user->getGroups();
    }
    else
    {
        $app->response->setStatus(404);
        return;
    }
    if($groups === false)
    {
        echo json_encode(array());
    }
    else
    {
        echo json_encode($groups);
    }
}

function link_user($uid = 'me')
{
    global $app;
    if(!$app->user)
    {
        $app->response->setStatus(401);
        return;
    }
    $body = $app->request->getBody();
    $obj  = json_decode($body);
    if($uid === 'me' || $uid === $app->user->getUid())
    {
        $app->user->addLoginProvider($obj->provider);
        AuthProvider::getInstance()->impersonate_user($app->user);
    }
    else if($app->user->isInGroupNamed("LDAPAdmins"))
    {
        $user = AuthProvider::getInstance()->get_user(false, $uid);
        if($user === false)
        {
            $app->response->setStatus(404);
            return;
        }
        $user->addLoginProvider($obj->provider);
    }
    else
    {
        $app->response->setStatus(404);
        return;
    }
    echo json_encode(array('success'=>true));
}

function check_email_available()
{
    global $app;
    $email = $app->request->params('email');
    if(strpos($email, '@') === false)
    {
        //Not a valid email
        return false;
    }
    if(strstr($email, '+') !== false)
    {
        //Remove everything between the + and the @
        $begining = strpos($email, '+');
        $end = strpos($email, '@');
        $to_delete = substr($email, $begining, $end - $begining);
        $email = str_replace($to_delete, '', $email);
    }
    $auth = AuthProvider::getInstance();
    $filter = new \Data\Filter('mail eq '.$email);
    $user = $auth->get_users_by_filter(false, $filter);
    if($user === false || !isset($user[0]))
    {
        $user = $auth->get_pending_users_by_filter(false, $filter);
        if($user === false || !isset($user[0]))
        {
            echo 'true';
        }
        else
        {
            echo json_encode(array('res'=>false, 'email'=>$user[0]->getEmail(), 'pending'=>true));
        }
    }
    else
    {
        echo json_encode(array('res'=>false, 'email'=>$user[0]->getEmail()));
    }
}

function check_uid_available()
{
    global $app;
    $uid = $app->request->params('uid');
    if(strpos($uid, '=') !== false || strpos($uid, ',') !== false)
    {
        return false;
    }
    $user = AuthProvider::getInstance()->get_users_by_filter(false, new \Data\Filter('uid eq '.$uid));
    if($user === false || !isset($user[0]))
    {
         $user = $auth->get_pending_users_by_filter(false, $filter);
        if($user === false || !isset($user[0]))
        {
            echo 'true';
        }
        else
        {
            echo json_encode(array('res'=>false, 'uid'=>$user[0]->getUid(), 'pending'=>true));
        }
    }
    else
    {
        echo json_encode(array('res'=>false, 'uid'=>$user[0]->getUid()));
    }
}

function reset_pass($uid)
{
    global $app;
    $auth = AuthProvider::getInstance();
    $users = $auth->get_users_by_filter(false, new \Data\Filter('uid eq '.$uid));
    if($users === false || !isset($users[0]))
    {
        $app->response->setStatus(404);
        return;
    }
    else
    {
        $email_msg = new PasswordResetEmail($users[0]);
        $email_provider = EmailProvider::getInstance();
        if($email_provider->sendEmail(false, $email_msg) === false)
        {
            throw new \Exception('Unable to send email!');
        }
    }
}

function remind_uid()
{
    global $app;
    $email = $app->request->params('email');
    $auth = AuthProvider::getInstance();
    $users = $auth->get_users_by_filter(false, new \Data\Filter('mail eq '.$email));
    if($users === false || !isset($users[0]))
    {
        $app->response->setStatus(404);
        return;
    }
    else
    {
        $email_msg = new UIDForgotEmail($users[0]);
        $email_provider = EmailProvider::getInstance();
        if($email_provider->sendEmail(false, $email_msg) === false)
        {
            throw new \Exception('Unable to send email!');
        }
    }
}

?>
