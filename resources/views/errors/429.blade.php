@extends('errors.layout')

@section('title', 'Too many requests')
@section('code', '429')
@section('heading', 'Please slow down a little.')
@section('message', 'We received too many requests from you in a short time. Wait a minute, then try again.')
