@extends('layouts.app')
@section('title', 'Profile Settings')

@section('content')
<div class="page-header">
    <div class="page-title">PROFILE SETTINGS</div>
    <div class="page-sub">manage your profile settings</div>
</div>



<div class="page-header">
    <div class="page-sub">Upload Avatar</div>
</div>

<div class="page-header">
    <div class="page-sub">MAJOR/MINOR</div>
</div>

<div class="page-header">
<div class="page-sub">Select Your Campus</div>
<select name="campus" id="number-select" style="font-size: 16px;">
  @foreach(config('campuses.list') as $campus)
    <option value="{{ $campus }}" {{ auth()->user()->campus === $campus ? 'selected' : '' }}>{{ $campus }}</option>
  @endforeach
</select>
</div>


<div class="page-header">
    <div class="page-sub">UPDATE BIO INTERESTS</div>
</div>


</br>
</br>


<a href="{{ route('help.ticket') }}" class="btn btn-primary {{ request()->routeIs('help.ticket') }}">
Help Ticket
</a>

</br>
</br>


<a href="{{ route('pages.change-password') }}" class="btn btn-primary {{ request()->routeIs('pages.change-password') }}">
Change Password
</a>

</br>
</br>
            
<form method="POST" action="{{ route('user.logout') }}">
    @csrf
<button class="btn btn-primary {{ request()->routeIs('user.logout') }}"> Logout </button>  
</form>


</br>


<form method="POST" action="{{ route('user.logout') }}">
    @csrf
<button class="btn btn-primary {{ request()->routeIs('user.logout') }}"> Delete Account </button>  
</form>





@endsection
