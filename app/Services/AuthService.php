<?php 

namespace App\Services;

use Auth;

use App\Models\User;

class AuthService {

	public function login(array $credentials, bool $remember): Bool
	{
		$user = User::where('username', $credentials['username'])->where('password', md5($credentials['password']))->first();

		if ($user) {
			Auth::login($user);
		}

		return !!$user;

	}

	public function logout()
	{
		Auth::logout();
	}

}

 ?>