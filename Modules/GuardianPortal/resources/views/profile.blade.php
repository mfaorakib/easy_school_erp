@extends('guardianportal::layouts.portal')
@section('title', __('ui.my_profile'))

@section('content')
<div class="page-head"><h1>{{ __('ui.my_profile') }}</h1></div>

@include('partials.profile-form')
@endsection
