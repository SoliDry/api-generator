<?php
namespace SoliDryTest\_data;

use Modules\V2\Entities\User;

class UserFixture
{
    /**
     * @return User
     */
    public static function createAndGet() : User
    {
        $user              = new User();
        $user->first_name  = 'Linus';
        $user->last_name   = 'Gates';
        $user->password    = 'secret';
        $user->jwt         = 'jwt';
        $user->permissions = 2;
        $user->save();

        return $user;
    }

    /**
     * @param $id
     */
    public static function delete($id) : void
    {
        User::destroy($id);
    }
}