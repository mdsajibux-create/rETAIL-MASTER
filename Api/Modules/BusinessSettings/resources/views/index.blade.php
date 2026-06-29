@extends('businesssettings::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('businesssettings.name') !!}</p>
@endsection
