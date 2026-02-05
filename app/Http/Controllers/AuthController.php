<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function showLoginFrom()
    {
        return view('auth.login');
    }
    public function showRegisterFrom()
    {
        return view('auth.register');
    }
    
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return Redirect::back()->withInput()->withErrors($validator);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if ($user->role == 2 && $user->status == 1) {
                return redirect()->route('admin.dashboard');
            }else{
                return Redirect::back()->with('error', 'Your Account is Deactivate');
            }
        
        }
        $user = User::where('email', $request->email)->first();

        if ($user) {
            return redirect()->back()->withInput()->withErrors(['password' => 'Incorrect password']);
        } else {
            return redirect()->back()->withInput()->withErrors(['email' => 'Invalid email']);
            }
    }


    public function registerSubmit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required',
        ]);

      
        if ($validator->fails()) {
            return Redirect::back()->withInput()->withErrors($validator);
        }
        $user = new User();
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = bcrypt($request->input('password'));
        $user->role = 2;
        $user->status = 1;

        $user->save();

        Auth::login($user);

        return Redirect::route('login')->with('success', 'User successfully registered');
    }
   

}
