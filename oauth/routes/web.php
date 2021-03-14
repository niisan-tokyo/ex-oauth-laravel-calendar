<?php

use App\Http\Controllers\TodoController;
use App\Models\GoogleUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Niisan\Laravel\GoogleCalendar\OauthCalendarService;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('google_login');
});

// oauth認証するためのURLにリダイレクトする
Route::get('/auth/redirect', function (OauthCalendarService $service) {
    return redirect($service->getAuthUri(null, true));
});

// oauthで飛んできたコードを使ってユーザを認証している
Route::get('/auth/callback', function (OauthCalendarService $service, Request $request) {
    $token = $service->getTokenByCode($request->code);
    $social_user = $service->getUserInfo($token);
    $google_user = GoogleUser::whereGoogleId($social_user->id)->first();
    $user = ($google_user) ? $google_user->user: new User;
    if (!$google_user) {
        $user->name = $social_user->name;
        $user->email = $social_user->email;
        $user->password = bcrypt(Str::random(20));
        $user->save();

        $google_user = new GoogleUser;
        $google_user->google_id = $social_user->id;
        $google_user->email = $social_user->email;
    }
    $google_user->access_token = $token->access_token;
    $google_user->refresh_token = $token->refresh_token ?? $google_user->refresh_token;
    $google_user->expires = $token->expires;

    $user->googleUser()->save($google_user);
    Auth::login($user);
    return redirect('/todo');
});

Route::middleware('auth')->group(function () {
    Route::resource('/todo', TodoController::class);
});
