@extends('smsgateway::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('smsgateway.name') !!}</p>
@endsection
