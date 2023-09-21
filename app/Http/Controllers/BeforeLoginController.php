<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Registration;
use Illuminate\Support\Facades\Mail;
use Exception;
use Carbon\Carbon;
use App\Models\DeleteToken;


class BeforeLoginController extends Controller
{
    //
    public function register_insert_data(Request $request)
    {
        $rules = [
            'fn' => 'required|min:3|max:40',
            'em' => 'required|email|unique:registration,email',
            'pwd' => 'required|confirmed|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,20}$/',
            'pwd_confirmation' => 'required',
            'mobile' => 'required|digits:10',
            'pic' => 'required|max:300|mimes:jpg,png,gif,bmp'
        ];
        $error_msg = [
            'fn.required' => 'Fullname cannot be empty',
            'fn.max' => 'Fullname must be at maximum 40 chracters',
            'fn.min' => 'Fullname must be atleast 3 characters',
            'em.required' => 'Email address cannot be empty',
            'em.email' => 'Invalid email address',
            'em.unique' => 'Email address already registered',
            'pwd.required' => 'Password cannot be empty',
            'pwd.confirmed' => 'Password and Confirm Password must match',
            'pwd.regex' => 'Password must contain one digit,one character both upper and lower and a special character',
            'pwd_confirmation.required' => 'Confirm Password cannot be empty',
            'mobile.required' => 'Mobile number cannot be empty',
            'mobile.digits' => 'Mobile number must contain only 10 digits',
            'pic.required' => 'Please select a profile picture',
            'pic.max' => 'Please select a profile picture of size less than or eqaul to 300Kb',
            'pic.mimes' => 'Select JPG,PNG,GIF,BMP Files only'
        ];
        $request->validate($rules, $error_msg);
        $register = new Registration();
        $register->fullname = $request->fn;
        $register->email = $request->em;
        $register->password = $request->pwd;
        $register->mobile = $request->mobile;
        $original_name = uniqid() . $request->file('pic')->getClientOriginalName();
        $register->pic = $original_name;
        $request->pic->move("Images/profile_pictures/", $original_name);
        if ($register->save()) {
            $data = array('fn' => $request->fn, 'email' => $request->em);
            Mail::send(['text' => 'create_account_msg'], ['data1' => $data], function ($message) use ($data) {
                $message->to($data['email'], $data['fn']);
            });
            session()->flash('success', 'Your account has been created and Activation link sent to your email address');
        } else {
            session()->flash('error', 'Error in creating account. Please try again later.');
        }
        return redirect('register');
    }

    public function activate_account($email)
    {
        $result = Registration::whereEmail($email)->first();
        if (empty($result)) {
            session()->flash('error', 'Your account is not registered. kindly register here.');
            return redirect('register');
        } else {
            if ($result->status == 'Active') {
                session()->flash('succ', 'Your account is already activated kindly login');
            } else {
                $update = Registration::where('email', $email)->update(array('status' => 'Active'));
                if ($update) {
                    session()->flash('succ', 'Your account is activated successfully. kindly login');
                } else {
                    session()->flash('error', 'Account activation failed please try after sometime.');
                }
            }
            return redirect('login')->with('status', 'Task was successful!');
        }
    }

    public function forget_password_form_submit(Request $req)
    {
        date_default_timezone_set("Asia/Kolkata");
        $current_time = Carbon::now();
        DeleteToken::where('expiry_time', '<', $current_time)->delete();
        $rules = ['em' => 'required|email'];
        $errors = [
            'em.required' => 'Email addrss is a required field',
            'em.email' => 'Enter a valid email address'
        ];
        $req->validate($rules, $errors);
        $em = $req->em;
        $data = Registration::where('email', $em)->first();
        if ($data) {
            $data1 = DeleteToken::where('email', $em)->first();
            if ($data1) {
                session()->flash('warning', 'A Password reset link is already sent to your mail please check. New link will be generated after old link expires');
            } else {
                date_default_timezone_set("Asia/Kolkata");
                $s_time = date("Y-m-d G:i:s");

                $token = hash('sha512', $s_time);
                $otp = mt_rand(100000, 999999);
                $data2 = array('name' => $data->fn, 'email' => $em, 'token' => $token, 'otp' => $otp);
                try {
                    Mail::send(['text' => 'mail_forget_pwd'], ["data3" => $data2], function ($message) use ($data2) {
                        $message->to($data2['email'], $data2['name'])->subject('Account Activation Link');
                        $message->from('janki.kansagra@rku.ac.in', 'Janki Kansagra');
                    });
                } catch (Exception $ex) {
                    session()->flash('error', 'We encountered some error in sending the password reset token');
                    return redirect('forget_password_form');
                }
                $expiry_time = Carbon::now()->addMinutes(30);
                $token_ins = new DeleteToken();
                $token_ins->email = $em;
                $token_ins->otp = $otp;
                $token_ins->token = $token;
                $token_ins->expiry_time = $expiry_time;
                if ($token_ins->save()) {
                    session()->flash('success', 'Password reset tokens sent to your registered email address');
                }
            }
        } else {
            session()->flash('error', 'Sorry the email address you entered is not registered.');
        }
        return redirect('forget_password_form');
    }

    public function verify_forget_pwd_otp($email, $token)
    {
        date_default_timezone_set("Asia/Kolkata");
        session()->put('forget_pwd_email', $email);
        session()->put('forget_pwd_token', $token);
        $current_time = Carbon::now();
        DeleteToken::where('expiry_time', '<', $current_time)->delete();
        $data1 = DeleteToken::where('email', $email)->first();
        if ($data1) {
            return view('verify_otp_forget_pwd');
        } else {
            session()->flash('error', 'Password reset token expired. Please Generate the link again by submitting the form');
            return redirect('forget_password_form');
        }
    }

    public function verify_otp_forget_password_action(Request $req)
    {

        $req->validate(['otp' => 'required|size:6'], ['otp.required' => 'OTP cannot be empty', 'otp.size' => 'OTP must have 6 digits only']);
        $otp = $req->otp;
        if (session('forget_pwd_token') && session('forget_pwd_email')) {
            $email = session()->get('forget_pwd_email');
            $token = session()->get('forget_pwd_token');
        }
        $data = DeleteToken::where('email', $email)->where('token', $token)->first();
        if ($data) {
            if ($data->otp == $otp) {
                return view('reset_pwd');
            } else {
                session()->flash('error', 'Incorrect OTP');
                return view('verify_otp_forget_pwd');
            }
        } else {
            session()->flash('error', 'Password reset token expired. Please Generate the link again by submitting the form');
            return redirect('forget_password_form');
        }
    }

    public function reset_pwd_action(Request $req)
    {
        $rules = [
            'npwd' => 'required|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,20}$/|confirmed',
            'npwd_confirmation' => 'required',
        ];
        $errors = [
            'npwd.required' => 'Password field cannot be empty',
            'npwd.regex' => 'Password must contain one small letter one capital letter, one number and one special symbol',
            'npwd.confirmed' => 'Password and Confirm Password must match',
            'npwd_confirmation.required' => 'Confirm Password must not be empty'
        ];
        $req->validate($rules, $errors);
        if (session('forget_pwd_token') && session('forget_pwd_email')) {
            $email = session()->get('forget_pwd_email');
            $token = session()->get('forget_pwd_token');
        }
        date_default_timezone_set("Asia/Kolkata");
        $current_time = Carbon::now();
        DeleteToken::where('expiry_time', '<', $current_time)->delete();
        $data = DeleteToken::where('email', $email)->where('token', $token)->first();
        if ($data) {
            $data1 = Registration::where('email', $email)->update(array('password' => $req->npwd));
            if ($data1) {
                DeleteToken::where('email', $email)->delete();
                session()->flash('succ', 'Password changed successfully');
                return redirect('login');
            }
        } else {
            session()->flash('error', 'Password reset token expired. Please Generate the link again by submitting the form');
            return redirect('forget_password_form');
        }
    }
}
