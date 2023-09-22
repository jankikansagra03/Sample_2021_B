@extends('admin_master')

@section('admin_dynamic_1')
    <div class="container">
        <div class="row">
            <div class="offset-lg-3 offset-md-3 col-6">
                <h1>Change Password</h1>
                <br>
                <form action="{{ URL::to('/') }}/user_change_password_action" method="post">
                    @csrf
                    <div class="row">
                        <div class="col">
                            <input type="text" name="old_pwd" placeHolder="Old Password" id="em1"
                                class="form-control">
                            <p id="em_err"></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <input type="password" name="pwd" placeHolder="New Password" id="pwd1"
                                class="form-control">
                            <p id="pwd_err"></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <input type="password" name="pwd_confirmation" placeHolder="Confirm New Password" id="pwd1"
                                class="form-control">
                            <p id="pwd_err"></p>
                        </div>
                    </div>
                    <div class="row" style="text-align:center;">
                        <div class="col">
                            <input type="submit" value="Change Password" name="btn-login" class="btn btn-danger">

                            <input type="reset" value="Reset" name="btn-message" class="btn btn-danger">
                        </div>
                    </div>
                    <br>

                    <br>
                </form>
            </div>
        </div>
    @endsection
