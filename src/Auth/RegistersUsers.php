<?php

namespace JvrSolis\LaravelCognitoAuth\Auth;

use App\Events\Frontend\Auth\UserRegistered;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use JvrSolis\LaravelCognitoAuth\CognitoClient;
use JvrSolis\LaravelCognitoAuth\Exceptions\InvalidUserFieldException;
use Illuminate\Foundation\Auth\RegistersUsers as BaseSendsRegistersUsers;

trait RegistersUsers
{
    use BaseSendsRegistersUsers;

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * @throws InvalidUserFieldException
     */
    public function register(RegisterRequest $request)
    {
        $this->validator($request->all())->validate();

        $attributes = [];

        $userFields = config('cognito.sso_user_fields');
        $request->request->add(['name' => $request->get('first_name') . ' ' . $request->get('last_name')]);

        foreach ($userFields as $userField) {
            if ($request->filled($userField)) {
                $attributes[$userField] = $request->get($userField);
            } else {
                throw new InvalidUserFieldException("The configured user field {$userField} is not provided in the request.");
            }
        }

        app()->make(CognitoClient::class)->register($request->email, $request->password, $attributes);

        event(new UserRegistered($user = $this->userRepository->create($request->only('first_name', 'last_name', 'email', 'password'))));

        auth()->login($user);

        return $this->registered($request, $user) ? : redirect($this->redirectPath());
    }
}
