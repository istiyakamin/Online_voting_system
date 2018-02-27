<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Mail;
use Session;
use App\Mail\verifyEmail;
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
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'department' => 'required|string|max:255',
            'phone' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
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
        Session::flush('status', 'Registered! But verify your email to Activate your account');
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'department' => $data['department'],
            'phone' => $data['phone'],
            'password' => bcrypt($data['password']),
            'VerifyToken' => Str::random(40)
        ]);

        $thisuser = User::findOrFail($user->id);
        $this->sendEmail($thisuser);
        return $user;
    }


    public function sendEmail($thisuser)
    {
        Mail::to($thisuser['email'])->send(new verifyEmail($thisuser));
    }

    public function verifyEmailFirst()
    {
        return view('email.verifyEmailFirst');
    }

    public function sendEmailDone($email, $VerifyToken)
    {
        
        $user = User::where(['email' => $email, 'VerifyToken' => $VerifyToken])->first();
        if($user){
         User::where(['email' => $email, 'VerifyToken' => $VerifyToken])->update(['status'=>'1' , 'VerifyToken'=>NULL]);
         return view('auth.login');
        }else{
            return "User Not Found";
        }
    }
}
