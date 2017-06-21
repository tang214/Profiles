<?php
class ProfilesAdminDataAPI extends Http\Rest\DataTableAPI
{
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
}
