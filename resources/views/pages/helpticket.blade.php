@extends('layouts.app')
@section('title', 'Help Ticket')

@section('content')
<div class="page-header">
    <div class="page-title">HELP TICKET</div>
    <div class="page-sub">reach-out if needing help</div>
</div>


<div class="card" style="max-width:480px;">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif


<form method="POST" action="{{ route('help.ticket.submit') }}">
    @csrf
                        
<!-- Name -->
<div class="form-group">
<label>Username</label>
<input type="text" id="name" name="name" value="" required>
</div>

    
<!-- Email -->
<div class="form-group">
<label>Email</label>
<input type="email" id="email" name="email" value="" required>
</div>

   
<!-- Subject -->
<div class="form-group">
<label>Subject</label>
<input type="text" id="subject" name="subject" value="" required>
</div>

    
<!-- Priority -->
<div class="form-group">
<label>Priority</label>
<select id="priority" name="priority" required
    <option value=""></option>
    <option value="low">Low</option>
    <option value="medium">Medium</option>
    <option value="high">High</option>
    <option value="urgent">Urgent</option>
</select>
</div>

    
<!-- Message -->
<div class="form-group">
<label>Message</label>
<textarea id="message" name="message" style="width:100%;" rows="8" required></textarea>
</div>

<!-- Submit Button -->
<button type="submit" class="btn btn-primary">Submit Ticket</button>
<a href="{{ route('pages.profile') }}" class="btn btn-ghost">Cancel</a>
</div>
</form>
</div>
@endsection
