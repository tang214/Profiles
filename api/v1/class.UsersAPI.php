<?php
class UsersAPI extends ProfilesAdminAPI
{
    public function setup($app)
    {
        $app->get('[/]', array($this, 'listUsers'));
        $app->post('[/]', array($this, 'createUser'));
        $app->get('/{uid}[/]', array($this, 'showUser'));
        $app->patch('/{uid}[/]', array($this, 'editUser'));
        $app->delete('/{uid}[/]', array($this, 'deleteUser'));
        $app->get('/{uid}/groups[/]', array($this, 'listGroupsForUser'));
        $app->post('/{uid}/Actions/link[/]', array($this, 'linkUser'));
        $app->post('/{uid}/Actions/reset_pass[/]', array($this, 'resetUserPassword'));
        $app->post('/Actions/check_email_available[/]', array($this, 'checkEmailAvailable'));
        $app->post('/Actions/check_uid_available[/]', array($this, 'checkUidAvailable'));
        $app->post('/Actions/remind_uid[/]', array($this, 'remindUid'));
    }

    public function listUsers($request, $response)
    {
        $odata = $request->getAttribute('odata', new \ODataParams(array()));
        if($this->validateIsAdmin($request, true) === false)
        {
            $users = array($this->user);
            $users = $odata->filterArrayPerSelect($users);
        }
        else
        {
            $auth = AuthProvider::getInstance();
            $users = $auth->getUsersByFilter($odata->filter, $odata->select, $odata->top, $odata->skip, 
                                             $odata->orderby);
        }
        return $response->withJson($users);
    }

    protected function validateCanCreateUser($proposedUser, $auth, &$message)
    {
        $user = $auth->getUsersByFilter(new \Data\Filter('mail eq '.$proposedUser->mail));
        if(!empty($user))
        {
            $message = 'Email already exists!';
            return false;
        }
        $user = $auth->getUsersByFilter(new \Data\Filter('uid eq '.$proposedUser->uid));
        if(!empty($user))
        {
            $message = 'Username already exists!';
            return false;
        }
        return true;
    }

    protected function validEmail($email)
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
        $domain = substr($email, $pos + 1);
        if(checkdnsrr($domain, 'MX') === false)
        {
            return false;
        }
        return true;
    }

    protected function getFailArray($message)
    {
        return array('res'=>false, 'message'=>$message);
    }

    public function createUser($request, $response)
    {
        $this->user = $request->getAttribute('user');
        //This one is different. If they are logged in fail...
        if($this->user !== false)
        {
            return $response->withStatus(404);
        }
        $obj = $request->getParsedBody();
        if(!isset($obj->captcha))
        {
            return $response->withStatus(401);
        }
        $captcha = FlipSession::getVar('captcha');
        if($captcha === false)
        {
            return $response->withStatus(401);
        }
        if(!$captcha->is_answer_right($obj->captcha))
        {
            return $response->withJson($this->getFailArray('Incorrect answer to CAPTCHA!'), 412);
        }
        $auth = AuthProvider::getInstance();
        $message = false;
        if($this->validateCanCreateUser($obj, $auth, $message) === false)
        {
            return $response->withJson($this->getFailArray($message), 412);
        }
        else if($this->validEmail($obj->mail) === false)
        {
            return $response->withJson($this->getFailArray('Invalid Email Address!'));
        }
        $ret = $auth->createPendingUser($obj);
        if($ret == false)
        {
            return $response->withJson($this->getFailArray('Failed to save user registration!'), 500);
        }
        return $response->withJson($ret);
    }

    protected function userIsMe($request, $uid)
    {
        $this->user = $request->getAttribute('user');
        return ($uid === 'me' || ($this->user !== false && $uid === $this->user->uid));
    }

    protected function hasLeadAccess()
    {
        return ($this->user->isInGroupNamed('Leads') || $this->user->isInGroupNamed('CC') || $this->user->isInGroupNamed('AFs'));
    }

    protected function getUserByUIDReadOnly($request, $uid)
    {
        if($this->userIsMe($request, $uid))
        {
            return $this->user;
        }
        if($this->user->isInGroupNamed('LDAPAdmins') || $this->hasLeadAccess())
        {
            $auth = \AuthProvider::getInstance();
            $filter = new \Data\Filter("uid eq $uid");
            $users = $auth->getUsersByFilter($filter);
            if(!empty($users))
            {
                return $users[0];
            }
        }
        return false;
    }

    protected function getUserByUID($request, $uid)
    {
        if($this->userIsMe($request, $uid))
        {
            return $this->user;
        }
        if($this->user->isInGroupNamed('LDAPAdmins'))
        {
            $auth = \AuthProvider::getInstance();
            $filter = new \Data\Filter("uid eq $uid");
            $users = $auth->getUsersByFilter($filter);
            if(!empty($users))
            {
                return $users[0];
            }
        }
        return false;
    }

    public function showUser($request, $response, $args)
    {
        $uid = $args['uid'];
        $user = $request->getAttribute('user');
        if($user === false)
        {
            if($_SERVER['SERVER_ADDR'] === $_SERVER['REMOTE_ADDR'])
            {
                $user = \AuthProvider::getInstance()->getUsersByFilter(new \Data\Filter("uid eq $uid"));
                if(empty($user))
                {
                    return $response->withStatus(404);
                }
                return $response->withJson($user[0]);
            } 
            return $response->withStatus(401);
        }
        $user = $this->getUserByUIDReadOnly($request, $uid);
        if($user === false)
        {
            return $response->withStatus(404);
        }
        if(!is_object($user) && isset($user[0]))
        {
            $user = $user[0];
        }
        if($request->getAttribute('format') === 'vcard')
        {
            $response = $response->withHeader('Content-Type', 'text/x-vCard');
            $response->getBody()->write($user->getVcard());
            return $response;
        }
        return $response->withJson($user);
    }

    protected function sendPasswordResetEmail($user)
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

    protected function exceptionCodeToHttpCode($e)
    {
        if($e->getCode() === 3)
        {
            return 401;
        }
        return 500;
    }

    protected function getUser($request, $uid, $payload)
    {
        $this->user = $request->getAttribute('user');
        if($this->user === false)
        {
            if(isset($payload->hash))
            {
                $auth = AuthProvider::getInstance();
                $this->user = $auth->getUserByResetHash($payload->hash);
                return $this->user;
            }
            return false;
        }
        return $this->getUserByUID($request, $uid);
    }

    public function editUser($request, $response, $args)
    {
        $uid = 'me';
        if(isset($args['uid']))
        {
            $uid = $args['uid'];
        }
        $obj = $request->getParsedBody();
        $user = $this->getUser($request, $uid, $obj);
        if($user === false)
        {
            return $response->withStatus(404);
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
            return $response->withJson($e, exceptionCodeToHttpCode($e));
        }
        if($this->userIsMe($request, $uid))
        {
            \FlipSession::setUser($user);
        }
        if(isset($obj->password))
        {
            $this->sendPasswordResetEmail($user);
        }
        return $response->withJson(array('success'=>true));
    }

    public function deleteUser($request, $response, $args)
    {
        $uid = 'me';
        if(isset($args['uid']))
        {
            $uid = $args['uid'];
        }
        $user = false;
        if($this->validateIsAdmin($request, true) === false && $this->userIsMe($request, $uid))
        {
            $user = $this->user;
        }
        else
        {
            $auth = AuthProvider::getInstance();
            $filter = new \Data\Filter("uid eq $uid");
            $user = $auth->getUsersByFilter($filter);
            if(empty($user))
            {
                $user = false;
            }
            else
            {
                $user = $user[0];
            }
        }
        if($user === false)
        {
            return $response->withStatus(404);
        }
        return $response->withJson($user->delete());
    }

    public function listGroupsForUser($request, $response, $args)
    {
        $uid = 'me';
        if(isset($args['uid']))
        {
            $uid = $args['uid'];
        }
        $this->validateLoggedIn($request);
        $user = $this->getUserByUID($request, $uid);
        if($user === false)
        {
            return $response->withStatus(404);
        }
        $groups = $user->getGroups();
        if($groups === false)
        {
            $groups = array();
        }
        return $response->withJson($groups);
    }

    public function linkUser($request, $response, $args)
    {
        $uid = 'me';
        if(isset($args['uid']))
        {
            $uid = $args['uid'];
        }
        $this->validateLoggedIn($request);
        $obj = $request->getParsedBody();
        if($this->userIsMe($request, $uid))
        {
            $this->user->addLoginProvider($obj->provider);
            AuthProvider::getInstance()->impersonateUser($this->user);
        }
        else if($this->user->isInGroupNamed("LDAPAdmins"))
        {
            $user = AuthProvider::getInstance()->getUser($uid);
            if($user === false)
            {
                return $response->withStatus(404);
            }
            $user->addLoginProvider($obj->provider);
        }
        else
        {
            return $response->withStatus(404);
        }
        return $response->withJson(array('success'=>true));
    }

    protected function getAllUsersByFilter($filter, &$pending)
    {
        $auth = AuthProvider::getInstance();
        $user = $auth->getUsersByFilter($filter);
        if(!empty($user))
        {
            $pending = false;
            return $user[0];
        }
        $user = $auth->getPendingUsersByFilter($filter);
        if(!empty($user))
        {
            $pending = true;
            return $user[0];
        }
        return false;
    }

    public function checkEmailAvailable($request, $response)
    {
        $params = $request->getQueryParams();
        $email = false;
        if(isset($params['email']))
        {
            $email = $params['email'];
        }
        if($email === false)
        {
            return $response->withStatus(400);
        }
        if(filter_var($email, FILTER_VALIDATE_EMAIL) === false || strpos($email, '@') === false)
        {
            return $response->withJson(false);
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
        $user = $this->getAllUsersByFilter($filter, $pending);
        if($user === false)
        {
            return $response->withJson(true);
        }
        return $response->withJson(array('res'=>false, 'email'=>$user->mail, 'pending'=>$pending));
    }

    public function checkUidAvailable($request, $response)
    {
        $params = $request->getQueryParams();
        $uid = false;
        if(isset($params['uid']))
        {
            $uid = $params['uid'];
        }
        if($uid === false)
        {
            return $response->withStatus(400);
        }
        if(strpos($uid, '=') !== false || strpos($uid, ',') !== false)
        {
            return $response->withJson(false);
        }
        $filter = new \Data\Filter('uid eq '.$uid);
        $pending = false;
        $user = $this->getAllUsersByFilter($filter, $pending);
        if($user === false)
        {
            return $response->withJson(true);
        }
        return $response->withJson(array('res'=>false, 'uidl'=>$user->uid, 'pending'=>$pending));
    }

    public function resetUserPassword($request, $response, $args)
    {
        $uid = false;
        if(isset($args['uid']))
        {
            $uid = $args['uid'];
        }
        else
        {
            return $response->withStatus(400);
        }
        $auth = AuthProvider::getInstance();
        $users = $auth->getUsersByFilter(new \Data\Filter('uid eq '.$uid));
        if(empty($users))
        {
            return $response->withStatus(404);
        }
        $email_msg = new PasswordResetEmail($users[0]);
        $email_provider = EmailProvider::getInstance();
        if($email_provider->sendEmail($email_msg) === false)
        {
            throw new \Exception('Unable to send email!');
        }
        return $response->withJson(true);
    }

    public function remindUid($request, $response)
    {
        $params = $request->getQueryParams();
        $email = false;
        if(isset($params['email']))
        {
            $email = $params['email'];
        }
        if($email === false)
        {
            return $response->withStatus(400);
        }
        if(filter_var($email, FILTER_VALIDATE_EMAIL) === false)
        {
            return $response->withStatus(400);
        }
        $auth = AuthProvider::getInstance();
        $users = $auth->getUsersByFilter(new \Data\Filter('mail eq '.$email));
        if(empty($users))
        {
            return $response->withStatus(404);
        }
        $email_msg = new UIDForgotEmail($users[0]);
        $email_provider = EmailProvider::getInstance();
        if($email_provider->sendEmail($email_msg) === false)
        {
            throw new \Exception('Unable to send email!');
        }
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
