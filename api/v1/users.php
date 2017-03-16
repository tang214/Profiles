<?php
require('class.UIDForgotEmail.php');
require('class.PasswordResetEmail.php');
require('class.PasswordHasBeenResetEmail.php');

function users()
{
    global $app;
    $app->get('(/)', 'list_users');
    $app->post('(/)', 'create_user');
    $app->get('/me(/)', 'show_user');
    $app->get('/:uid(/)', 'show_user');
    $app->patch('/:uid(/)', 'editUser');
    $app->delete('/:uid(/)', 'deleteUser');
    $app->get('/me/groups(/)', 'list_groups_for_user');
    $app->get('/:uid/groups(/)', 'list_groups_for_user');
    $app->post('/me/Actions/link(/)', 'link_user');
    $app->post('/:uid/Actions/link(/)', 'link_user');
    $app->post('/:uid/Actions/reset_pass(/)', 'reset_pass');
    $app->post('/Actions/check_email_available(/)', 'check_email_available');
    $app->post('/Actions/check_uid_available(/)', 'check_uid_available');
    $app->post('/Actions/remind_uid(/)', 'remind_uid');
}

function list_users()
{
    global $app;
    if(!$app->user)
    {
        $app->response->setStatus(401);
        return;
    }
    if($app->user && !$app->user->isInGroupNamed('LDAPAdmins'))
    {
        //Only return this user. This user doesn't have access to other accounts
        echo json_encode(array($app->user));
    }
    else
    {
        $auth = AuthProvider::getInstance();
        $users = $auth->getUsersByFilter($app->odata->filter, $app->odata->select, $app->odata->top, $app->odata->skip, $app->odata->orderby);
        echo json_encode($users);
    }
}

function validateCanCreateUser($proposedUser, $auth, &$message)
{
    $user = $auth->getUsersByFilter(new \Data\Filter('mail eq '.$proposedUser->mail));
    if($user !== false && isset($user[0]))
    {
        $message = 'Email already exists!';
        return false;
    }
    $user = $auth->getUsersByFilter(new \Data\Filter('uid eq '.$proposedUser->uid));
    if($user !== false && isset($user[0]))
    {
        $message = 'Username already exists!';
        return false;
    }
    return true;
}

function validEmail($email)
{
    if(filter_var($email) === false)
    {
        return false;
    }
    $pos = strpos($email, '@');
    if($pos === false)
    {
        return false;
    }
    $domain = substr($email, $pos+1);
    if(checkdnsrr($domain, 'MX') === false)
    {
        return false;
    }
    return true;
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
    $captcha = FlipSession::getVar('captcha');
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
    $message = false;
    if(validateCanCreateUser($obj, $auth, $message) === false)
    {
        echo json_encode(array('res'=>false, 'message'=>$message));
        return;
    }
    else if(validEmail($obj->mail) === false)
    {
        echo json_encode(array('res'=>false, 'message'=>'Invalid Email Address!'));
        return;
    }
    $ret = $auth->createPendingUser($obj);
    if($ret == false)
    {
        echo json_encode(array('res'=>false, 'message'=>'Failed to save user registration!'));
        return;
    }
    echo json_encode($ret);
}

function userIsMe($app, $uid)
{
    return ($uid === 'me' || $uid === $app->user->uid);
}

function getUserByUIDReadOnly($app, $uid)
{
    if(userIsMe($app, $uid))
    {
        return $app->user;
    }
    if($app->user->isInGroupNamed('LDAPAdmins') || hasLeadAccess($app))
    {
        $auth = \AuthProvider::getInstance();
        $filter = new \Data\Filter("uid eq $uid");
        $users = $auth->getUsersByFilter($filter);
        if($users !== false && isset($users[0]))
        {
            return $users[0];
        }
    }
    return false;
}

function getUserByUID($app, $uid)
{
    if(userIsMe($app, $uid))
    {
        return $app->user;
    }
    if($app->user->isInGroupNamed('LDAPAdmins'))
    {
        $auth = \AuthProvider::getInstance();
        $filter = new \Data\Filter("uid eq $uid");
        $users = $auth->getUsersByFilter($filter);
        if($users !== false && isset($users[0]))
        {
            return $users[0];
        }
    }
    return false;
}

function show_user($uid = 'me')
{
    global $app;
    if(!$app->user)
    {
        if($_SERVER['SERVER_ADDR'] === $_SERVER['REMOTE_ADDR'])
        {
            $user = \AuthProvider::getInstance()->getUsersByFilter(new \Data\Filter("uid eq $uid"));
            if($user === false || !isset($user[0]))
            {
                $app->notFound();
            }
            echo $user[0]->serializeObject();
            return;
        } 
        $app->response->setStatus(401);
        return;
    }
    $user = getUserByUIDReadOnly($app, $uid);
    if($user === false)
    {
        $app->halt(404);
    }
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

function sendPasswordResetEmail($user)
{
    $forwardedFor = false;
    if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
    {
        $forwardedFor = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    $emailMsg = new PasswordHasBeenResetEmail($user, $_SERVER['REMOTE_ADDR'], $forwardedFor);
    $emailProvider = EmailProvider::getInstance();
    if($emailProvider->sendEmail($emailMsg) === false)
    {
        throw new \Exception('Unable to send password reset email!');
    }
}

function exceptionCodeToHttpCode($e)
{
    if($e->getCode() === 3)
    {
        return 401;
    }
    return 500;
}

function getUser($app, $uid, $payload)
{
    if(!$app->user)
    {
        if(isset($payload->hash))
        {
            $auth = AuthProvider::getInstance();
            $app->user = $auth->getUserByResetHash($payload->hash);
            return $app->user;
        }
        return false;
    }
    return getUserByUID($app, $uid);
}

function editUser($uid = 'me')
{
    global $app;
    $obj = $app->getJsonBody();
    $user = getUser($app, $uid, $obj);
    if($user === false)
    {
        $app->response->setStatus(404);
        return;
    }
    try
    {
        if(isset($obj->old_uid))
        {
            unset($obj->old_uid);
        }
        $user->editUser($obj);
    }
    catch(\Exception $e)
    {
        $app->response->setStatus(exceptionCodeToHttpCode($e));
        echo json_encode($e);
        return;
    }
    if(userIsMe($app, $uid))
    {
        \FlipSession::setUser($user);
    }
    if(isset($obj->password))
    {
        sendPasswordResetEmail($user);
    }
    echo json_encode(array('success'=>true));
}

function deleteUser($uid = 'me')
{
    global $app;
    if(!$app->user)
    {
        $app->response->setStatus(401);
        return;
    }
    $user = false;
    if(userIsMe($app, $uid))
    {
        $user = $app->user;
    }
    else if($app->user->isInGroupNamed("LDAPAdmins"))
    {
        $auth = AuthProvider::getInstance();
        $filter = new \Data\Filter("uid eq $uid");
        $user = $auth->getUsersByFilter($filter);
        if(isset($user[0]))
        {
            $user = $user[0];
        }
    }
    return $user->delete();
}

function list_groups_for_user($uid = 'me')
{
    global $app;
    if(!$app->user)
    {
        $app->response->setStatus(401);
        return;
    }
    $user = getUserByUID($app, $uid);
    if($user === false)
    {
        $app->response->setStatus(404);
        return;
    }
    $groups = $user->getGroups();
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
    if(userIsMe($uid))
    {
        $app->user->addLoginProvider($obj->provider);
        AuthProvider::getInstance()->impersonateUser($app->user);
    }
    else if($app->user->isInGroupNamed("LDAPAdmins"))
    {
        $user = AuthProvider::getInstance()->getUser($uid);
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

function getAllUsersByFilter($filter, &$pending)
{
    $auth = AuthProvider::getInstance();
    $user = $auth->getUsersByFilter($filter);
    if($user !== false && isset($user[0]))
    {
        $pending = false;
        return $user[0];
    }
    $user = $auth->getPendingUsersByFilter($filter);
    if($user !== false && isset($user[0]))
    {
        $pending = true;
        return $user[0];
    }
    return false;
}

function check_email_available()
{
    global $app;
    $email = $app->request->params('email');
    if(strpos($email, '@') === false)
    {
        //Not a valid email
        echo 'false';
    }
    if(strstr($email, '+') !== false)
    {
        //Remove everything between the + and the @
        $begining = strpos($email, '+');
        $end = strpos($email, '@');
        $to_delete = substr($email, $begining, $end - $begining);
        $email = str_replace($to_delete, '', $email);
    }
    $filter = new \Data\Filter('mail eq '.$email);
    $pending = false;
    $user = getAllUsersByFilter($filter, $pending);
    if($user === false)
    {
        echo 'true';
        return;
    }
    echo json_encode(array('res'=>false, 'email'=>$user->mail, 'pending'=>$pending));
}

function check_uid_available()
{
    global $app;
    $uid = $app->request->params('uid');
    if(strpos($uid, '=') !== false || strpos($uid, ',') !== false)
    {
        return false;
    }
    $filter = new \Data\Filter('uid eq '.$uid);
    $pending = false;
    $user = getAllUsersByFilter($filter, $pending);
    if($user === false)
    {
        echo 'true';
        return;
    }
    echo json_encode(array('res'=>false, 'uidl'=>$user->uid, 'pending'=>$pending));
}

function reset_pass($uid)
{
    global $app;
    $auth = AuthProvider::getInstance();
    $users = $auth->getUsersByFilter(new \Data\Filter('uid eq '.$uid));
    if($users === false || !isset($users[0]))
    {
        $app->response->setStatus(404);
        return;
    }
    else
    {
        $email_msg = new PasswordResetEmail($users[0]);
        $email_provider = EmailProvider::getInstance();
        if($email_provider->sendEmail($email_msg) === false)
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
    $users = $auth->getUsersByFilter(new \Data\Filter('mail eq '.$email));
    if($users === false || !isset($users[0]))
    {
        $app->response->setStatus(404);
        return;
    }
    else
    {
        $email_msg = new UIDForgotEmail($users[0]);
        $email_provider = EmailProvider::getInstance();
        if($email_provider->sendEmail($email_msg) === false)
        {
            throw new \Exception('Unable to send email!');
        }
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>
