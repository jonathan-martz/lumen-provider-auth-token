<?php

namespace App\Providers;

use App\Model\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;


class AuthTokenServiceProvider extends ServiceProvider
{
    public $request;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['auth']->viaRequest('api', function ($request) {
            $validator = Validator::make($request->all(), [
                'auth.userid' => 'required|integer',
                'auth.topic' => 'required|string',
                'auth.token' => 'required|string|size:512'
            ]);

            $users = DB::table('users')
                ->where('id', '=', $request->input('auth.userid'));

            $count = $users->count();

            if($count === 1) {
                $user = new User((array)$users->first());
                if($user->getActive()) {
                    $tokens = DB::table('topic_token')
                        ->where('userid', '=', $user->getAuthIdentifier())
                        ->where('token', '=', $request->input('auth.token'))
                        ->where('topic', '=', $request->input('auth.topic'));
                    if($tokens->count() === 1) {
                        return $user;
                    }
                }
            }


            return null;
        });
    }
}
