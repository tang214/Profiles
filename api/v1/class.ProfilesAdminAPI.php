<?php
class ProfilesAdminAPI extends Http\Rest\RestAPI
{
    protected $user;

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
}
