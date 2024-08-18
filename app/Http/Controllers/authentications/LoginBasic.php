<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginBasic extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except(["logout"]);
    }
    public function index()
    {
        return view('auth.login');
    }
    public function login(LoginRequest $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return redirect("/")->with("success", "You have successfully logged in. Welcome back!");
        }
        return redirect()->back()->with("error", "Login failed. Please check your credentials and try again.")->withInput();
    }
    public function logout()
    {
        Auth::logout();
        return redirect()->route('login')->with("success","You have been logged out. Please sign in again to continue.");
    }
}
