<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Registration;
use Illuminate\Support\Facades\Mail;

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

    
}
