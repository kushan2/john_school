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
<select name="numbers" id="number-select" style="font-size: 16px;">
  <option value="1">SUNY Adirondack</option>
  <option value="2">University of Albany</option>
  <option value="3">Alfred University</option>
  <option value="4">Alfred State College</option>
  <option value="5">Binghamton University</option>
  <option value="6">SUNY Brockport</option>
  <option value="7">SUNY Broome Community College</option>
  <option value="8">University of Buffalo</option>
  <option value="9">Buffalo State University</option>
  <option value="10">SUNY Canton</option>
  <option value="11">Cayuga Community College</option>
  <option value="12">Clinton Community College</option>
  <option value="13">SUNY Colbleskill</option>
  <option value="14">Columbia-Greene Community College</option>
  <option value="15">Cornell University</option>
  <option value="16">Corning Community College</option>
  <option value="17">SUNY Cortland</option>
  <option value="18">SUNY Delhi</option>
  <option value="19">SUNY Downstate Health Sciences University</option>
  <option value="20">Dutchess Community College</option>
  <option value="21">SUNY Empire State University</option>
  <option value="22">SUNY College of Environmental Science</option>
  <option value="23">Erie Community College</option>
  <option value="24">Farmingdale State College</option>
  <option value="25">Fashion Institute of Technology</option>
  <option value="26">Finger Lakes Community College</option>
  <option value="27">SUNY Fredonia</option>
  <option value="28">Fulton-Montgomery Community College</option>
  <option value="29">Genesee Community College</option>
  <option value="30">SUNY Geneseo</option>
  <option value="31">Herkimer County Community College</option>
  <option value="32">Hudson Valley Community College</option>
  <option value="33">Jamestown Community College</option>
  <option value="34">Jefferson Community College</option>
  <option value="35">SUNY Maritime College</option>
  <option value="36">Mohawk Valley Community College</option>
  <option value="37">Monroe Community College</option>
  <option value="38">SUNY Morrisville</option>
  <option value="39">Nassau Community College</option>
  <option value="40">SUNY New Paltz</option>
  <option value="41">Niagara County Community College</option>
  <option value="42">North Country Community College</option>
  <option value="43">SUNY Old Westbury</option>
  <option value="44">SUNY Oneonta</option>
  <option value="45">Onondaga Community College</option>
  <option value="46">SUNY College of Optometry</option>
  <option value="47">Orange County Community College</option>
  <option value="48">SUNY Oswego</option>
  <option value="49">SUNY Plattsburg</option>
  <option value="50">SUNY Potsdam</option>
  <option value="51">Purchase College</option>
  <option value="52">Rockland Community College</option>
  <option value="53">Schenectady Community College</option>
  <option value="54">Stony Brook University </option>
  <option value="55">Suffolk Community College</option>
  <option value="56">SUNY Sullivan</option>
  <option value="57">SUNY Polytechnic Institute</option>
  <option value="58">Tompkins Cortland Community College</option>
  <option value="59">Ulster Community College</option>
  <option value="60">Upsate Medical University</option>
  <option value="61">Westchester Community College</option>
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
