@extends('layouts.app')
@section('title', 'Open Chat')

@section('content')
<div class="page-header">
    <div class="page-title">OPEN CHAT</div>
    <div class="page-sub">welcome back, {{ Auth::user()->name }}</div>
</div>













@endsection
