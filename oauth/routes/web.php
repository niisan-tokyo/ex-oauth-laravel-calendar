<?php

use App\Http\Controllers\TodoController;
use App\Models\GoogleUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

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
Route::get('/auth/redirect', function () {
    return Socialite::driver('google')
        ->scopes(['https://www.googleapis.com/auth/calendar.events'])
        ->with(['approval_prompt' => 'force', 'access_type' => 'offline'])
        ->redirect();
});

// oauthで飛んできたコードを使ってユーザを認証している
Route::get('/auth/callback', function () {
    $social_user = Socialite::driver('google')->user();
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
    $google_user->access_token = $social_user->token;
    $google_user->refresh_token = $social_user->refreshToken ?? $google_user->refreshToken;
    $google_user->expires = Carbon::now()->timestamp + $social_user->expiresIn;

    $user->googleUser()->save($google_user);
    Auth::login($user);
    return redirect('/todo');
});

Route::middleware('auth')->group(function () {
    Route::resource('/todo', TodoController::class);
});
