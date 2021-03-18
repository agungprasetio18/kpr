<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Pangkat;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |array
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var stringarray
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    // public function index()
    // {
    //     dd('asd');
    //     return view('auth.register', [
    //         'pangkats' => Pangkat::get()
    //     ]);
    // }
    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'username' => ['required', 'string', 'min:3', 'max:255', 'unique:users,username'],
            'password' => ['required', 'min:8', 'confirmed'],
            'pangkat_id' => ['required']
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => "2",
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'status_verif' => 0,
            'pangkat_id' => $data['pangkat_id']
        ]);
    }

    // cegah login dari registrasi
    protected function registered()
    {
        $this->guard()->logout();
        return redirect()->route('login')->with('success', 'untuk melanjutkan silahkan verifikasi email anda.');
    }
}
