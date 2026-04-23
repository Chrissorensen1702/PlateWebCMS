@extends('sales.layouts.default')

@section('title', 'Forside')
@section('body-class', 'marketing-body marketing-body--home')

@section('header')
    @include('sales.layouts.header')
@endsection

@section('main-content')
    @include('sales.partials.home-hero')

    @include('sales.partials.home-benefits')
@endsection
