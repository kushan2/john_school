@extends('layouts.app')
@section('title', 'Change Password')

@section('content')
<div class="page-header">
    <div class="page-title">CHANGE PASSWORD</div>
    <div class="page-sub">update your account password</div>
</div>

<div class="card" style="max-width:480px;">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('pages.change-password.update') }}">
        @csrf
        <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="current_password" placeholder="Enter current password" required>
        </div>
        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="password" placeholder="Min. 8 characters" required>
        </div>
        <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="password_confirmation" placeholder="Repeat new password" required>
        </div>
        <div style="display:flex; gap:12px; margin-top:24px;">
            <button type="submit" class="btn btn-primary">Update Password</button>
            <a href="{{ route('pages.profile') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
