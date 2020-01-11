<?php
namespace App\Repositories\Auth;

use App\Notifications\TwoFactorSecurity;

class TwoFactorSecurityRepository
{

    /**
     * Set two factor security code.
     *
     *
     * @param $user
     * @return int|void;
     */

    public function set($user)
    {
        if (! config('config.two_factor_security')) {
            return;
        }

        $two_factor_code = rand(100000, 999999);

        $user->notify(new TwoFactorSecurity($two_factor_code));

        return $two_factor_code;
    }
}
