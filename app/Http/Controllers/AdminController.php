<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Registration;

class AdminController extends Controller
{
    //
    public function admin_dashboard()
    {
        $user_count = Registration::count();
        $active_count = Registration::where('status', 'Active')->count();
        $inactive_count = Registration::where('status', 'Inactive')->count();
        $deleted_count = Registration::where('status', 'Deleted')->count();

        $data = ['deleted' => $deleted_count, 'active' => $active_count, 'inactive' => $inactive_count, 'user_count' => $user_count];
        return view('admin_dashboard', compact('data'));
    }

    public function user_change_password()
    {
        return view('user_change_password');
    }

    public function user_change_password_action(REQUEST $req)
    {
        
    }
}
