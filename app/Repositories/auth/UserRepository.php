<?php
namespace App\Repositories\Auth;

use App\User;
use App\UserPreference;
use Illuminate\Validation\ValidationException;

class UserRepository {
	protected $user;

	/**
	 * Instantiate a new instance.
	 *
	 * @return void
	 */
	public function __construct(
		User $user
	) {
		$this->user = $user;
	}

    /**
     * Find user by Id
     *
     * @param integer $id
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     * @throws ValidationException
     */

	public function findOrFail($id = null) {
		$user = $this->user->with('roles', 'userPreference', 'userPreference.academicSession')->find($id);

		if (!$user) {
			throw ValidationException::withMessages(['message' => trans('user.could_not_find')]);
		}

		return $user;
	}

	/**
	 * Find user by Email
	 *
	 * @param email $email
	 * @return User
	 */

	public function findByEmail($email = null) {
		return $this->user->with('roles', 'userPreference')->filterByEmail($email, 1)->first();
	}

	/**
	 * Find user by Username
	 *
	 * @param username $username
	 * @return User
	 */

	public function findByUsername($username = null) {
		return $this->user->with('roles', 'userPreference')->filterByUsername($username, 1)->first();
	}

	/**
	 * Update given user preference.
	 *
	 * @param UserPreference $user_preference
	 * @param array $params
	 *
	 * @return User
	 */
	public function updatePreference(UserPreference $user_preference, $params = array()) {
		$user_preference->color_theme = gv($params, 'theme', config('config.theme'));
		$user_preference->direction = gv($params, 'direction', config('config.direction'));
		$user_preference->locale = gv($params, 'locale', config('config.locale'));
		$user_preference->sidebar = gv($params, 'sidebar', config('config.sidebar'));
		$user_preference->save();

		if ($user_preference->direction === 'rtl') {
			\Cache::put('direction', 'rtl', config('jwt.ttl'));
		} else {
			\Cache::put('direction', 'ltr', config('jwt.ttl'));
		}
		\Cache::put('locale', $user_preference->locale, config('jwt.ttl'));
	}
}
