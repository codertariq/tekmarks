<?php
namespace App\Repositories\Auth;

use Carbon\Carbon;
use App\Events\UserLogin;
use Illuminate\Support\Str;
use App\Models\Employee\Employee;
use App\Notifications\PasswordReset;
use App\Notifications\PasswordResetted;
use App\Repositories\Auth\UserRepository;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Validation\ValidationException;
use App\Repositories\Utility\IpFilterRepository;
use App\Repositories\Auth\LoginThrottleRepository;
use App\Repositories\Auth\TwoFactorSecurityRepository;
use App\Repositories\Configuration\ConfigurationRepository;

class AuthRepository
{
    protected $user;
    protected $throttle;
    protected $two_factor;
    protected $config;
    protected $ip_filter;
    protected $employee;

    /**
     * Instantiate a new instance.
     *
     * @param \App\Repositories\Auth\UserRepository $user
     * @param \App\Repositories\Auth\LoginThrottleRepository $throttle
     * @param \App\Repositories\Auth\TwoFactorSecurityRepository $two_factor
     * @param ConfigurationRepository $config
     * @param IpFilterRepository $ip_filter
     * @param Employee $employee
     */
    public function __construct(
        UserRepository $user,
        LoginThrottleRepository $throttle,
        TwoFactorSecurityRepository $two_factor,
        ConfigurationRepository $config,
        IpFilterRepository $ip_filter
//        Employee $employee
    ) {
        $this->user       = $user;
        $this->throttle   = $throttle;
        $this->two_factor = $two_factor;
        $this->config     = $config;
        $this->ip_filter  = $ip_filter;
//        $this->employee = $employee;
    }

    /**
     * Authenticate an user.
     *
     * @param array $params
     * @return array
     * @throws ValidationException
     */
    public function auth($params = array())
    {

        $email_or_username = gv($params, 'email_or_username');

        $this->throttle->validate();

        $token = $this->validateLogin($params);

        if (filter_var($email_or_username, FILTER_VALIDATE_EMAIL)) {
            $auth_user = $this->user->findByEmail($email_or_username);
        } else {
            $auth_user = $this->user->findByUsername($email_or_username);
        }

        $this->validateStatus($auth_user);

        event(new UserLogin($auth_user));

        $two_factor_code = $this->two_factor->set($auth_user);

        $this->checkPreference($auth_user);

        $auth_user = $auth_user->fresh();

        $auth_user->two_factor_code = $two_factor_code;

        return compact('token','auth_user');
    }

    public function checkPreference($auth_user)
    {
        $user_preference = $auth_user->userPreference;

        if (!isset($user_preference) || $user_preference === '' || $user_preference === null) {
            $user_preference = new \App\UserPreference;
            $user_preference->user()->associate($auth_user);
            $user_preference->save();
        }

        $user_preference->theme = ($user_preference->theme) ? : config('config.theme');
        $user_preference->direction = ($user_preference->direction) ? : config('config.direction');
        $user_preference->locale = ($user_preference->locale) ? : config('config.locale');
        $user_preference->sidebar = ($user_preference->sidebar) ? : config('config.sidebar');
        $user_preference->save();
    }

    /**
     * Validate login credentials.
     *
     * @param array $params
     * @return auth token
     * @throws ValidationException
     */
    public function validateLogin($params = array())
    {
        $email_or_username = gv($params, 'email_or_username');
        $password          = gv($params, 'password');

        if (filter_var($email_or_username, FILTER_VALIDATE_EMAIL)) {
            $credentials = array('email' => $email_or_username, 'password' => $password);
        } else {
            $credentials = array('username' => $email_or_username, 'password' => $password);
        }

        try {
            if (! $token = \JWTAuth::attempt($credentials)) {
                $this->throttle->update();

                throw ValidationException::withMessages(['email_or_username' => trans('auth.failed')]);
            }
        } catch (JWTException $e) {
            throw ValidationException::withMessages(['email_or_username' => trans('general.something_wrong')]);
        }

        $this->throttle->clearCache();

        return $token;
    }

    /**
     * Validate authenticated user status.
     *
     * @param authenticated user
     * @return null
     * @throws ValidationException
     */
    public function validateStatus($auth_user)
    {
        if ($auth_user->status === 'pending_activation') {
            throw ValidationException::withMessages(['email_or_username' => trans('auth.pending_activation')]);
        }

        if ($auth_user->status === 'pending_approval') {
            throw ValidationException::withMessages(['email_or_username' => trans('auth.pending_approval')]);
        }

        if ($auth_user->status === 'disapproved') {
            throw ValidationException::withMessages(['email_or_username' => trans('auth.not_activated')]);
        }

        if ($auth_user->status === 'banned') {
            throw ValidationException::withMessages(['email_or_username' => trans('auth.account_banned')]);
        }

        if ($auth_user->status != 'activated') {
            throw ValidationException::withMessages(['email_or_username' => trans('auth.not_activated')]);
        }

//        if (!$auth_user->hasPermissionTo('enable-login')) {
//            throw ValidationException::withMessages(['email_or_username' => trans('auth.login_permission_disabled')]);
//        }

        $user_roles = $auth_user->getRoleNames()->all();

        if (in_array(config('system.default_role.user'), $user_roles)) {
            $student = $auth_user->Student;

            $valid_student = $this->student->filterById($student->id)->whereHas('studentRecords', function ($q) {
                $q->whereNull('date_of_exit')->whereIsPromoted(0);
            })->first();

            if (! $valid_student) {
                throw ValidationException::withMessages(['email_or_username' => trans('student.login_permission_disabled')]);
            }
        } elseif (
            count(array_diff($user_roles, [config('system.default_role.admin')]))
        ) {
            $employee = $auth_user->Employee;

            $valid_employee = $this->employee->filterById($employee->id)->whereHas('employeeTerms', function ($q) {
                $q->whereNull('date_of_leaving');
            })->first();

            if (! $valid_employee) {
                throw ValidationException::withMessages(['email_or_username' => trans('employee.login_permission_disabled')]);
            }
        }
        return $auth_user;
    }

    /**
     * Check for registration availability.
     *
     * @return null
     * @throws ValidationException
     */
    public function validateRegistrationStatus()
    {
        if (! config('config.registration')) {
            throw ValidationException::withMessages(['message' => trans('general.feature_not_available')]);
        }
    }

    /**
     * Check for email verification availability.
     *
     * @return null
     * @throws ValidationException
     */
    public function validateEmailVerificationStatus()
    {
        if (! config('config.email_verification')) {
            throw ValidationException::withMessages(['message' => trans('general.feature_not_available')]);
        }
    }

    /**
     * Check for account approval availability.
     *
     * @return null
     * @throws ValidationException
     */
    public function validateAccountApprovalStatus()
    {
        if (! config('config.account_approval')) {
            throw ValidationException::withMessages(['message' => trans('general.feature_not_available')]);
        }
    }

    /**
     * Check for reset password availability.
     *
     * @return null
     * @throws ValidationException
     */
    public function validateResetPasswordStatus()
    {
        if (! config('config.reset_password')) {
            throw ValidationException::withMessages(['message' => trans('general.feature_not_available')]);
        }
    }

    /**
     * Validate user for reset password.
     *
     * @param email $email
     * @return User
     * @throws ValidationException
     */
    public function validateUserAndStatusForResetPassword($email = null)
    {
        $user = $this->user->findByEmail($email);

        if (! $user) {
            throw ValidationException::withMessages(['email' => trans('passwords.user')]);
        }

        if ($user->status != 'activated') {
            throw ValidationException::withMessages(['email' => trans('passwords.account_not_activated')]);
        }

        return $user;
    }

    /**
     * Request password reset token of user.
     *
     * @param array
     * @return null
     * @throws ValidationException
     */
    public function password($params = array())
    {
        $email = gv($params, 'email');

        $this->validateResetPasswordStatus();

        $user = $this->validateUserAndStatusForResetPassword($email);

        $token = Str::uuid();
        \DB::table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        $user->notify(new PasswordReset($user, $token));
    }

    /**
     * Validate reset password token.
     *
     * @param string $token
     * @param email $email
     * @return null
     * @throws ValidationException
     */
    public function validateResetPasswordToken($token, $email = null)
    {
        if ($email) {
            $reset = \DB::table('password_resets')->where('email', '=', $email)->where('token', '=', $token)->first();
        } else {
            $reset = \DB::table('password_resets')->where('token', '=', $token)->first();
        }

        if (! $reset) {
            throw ValidationException::withMessages(['message' => trans('passwords.token')]);
        }

        if (date("Y-m-d H:i:s", strtotime($reset->created_at . "+".config('config.reset_password_token_lifetime')." minutes")) < date('Y-m-d H:i:s')) {
            throw ValidationException::withMessages(['email' => trans('passwords.token_expired')]);
        }
    }

    /**
     * Reset password of user.
     *
     * @param array
     * @return null
     * @throws ValidationException
     */
    public function reset($params = array())
    {
        $email = gv($params, 'email');
        $token = gv($params, 'token');
        $password = gv($params, 'password');

        $this->validateResetPasswordStatus();

        $user = $this->validateUserAndStatusForResetPassword($email);

        $this->validateResetPasswordToken($token, $email);

        $this->resetPassword($password, $user);

        \DB::table('password_resets')->where('email', '=', $email)->where('token', '=', $token)->delete();

        $user->notify(new PasswordResetted($user));
    }

    /**
     * Update user password.
     *
     * @param string $password
     * @param User $user
     * @return null
     */
    public function resetPassword($password, $user = null)
    {
        $user = ($user) ? : \Auth::user();
        $user->password = bcrypt($password);
        $user->save();
    }

    /**
     * Validate current password of user.
     *
     * @param string $password
     * @return null
     * @throws ValidationException
     */
    public function validateCurrentPassword($password)
    {
        if (!\Hash::check($password, \Auth::user()->password)) {
            throw ValidationException::withMessages(['password' => trans('passwords.lock_screen_password_mismatch')]);
        }
    }
}
