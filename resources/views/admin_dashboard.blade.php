@extends('admin_master');

@section('admin_dynamic_1')
    <h1>Registered Users::{{ $data['user_count'] }}</h1>
    <h1>Active Users::{{ $data['active'] }}</h1>
    <h1>Inactive Users::{{ $data['inactive'] }}</h1>
    <h1>Deleted Users::{{ $data['deleted'] }}</h1>
@endsection
