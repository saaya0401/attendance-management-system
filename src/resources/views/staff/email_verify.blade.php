@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{asset('css/email_verify.css')}}">
@endsection

@section('content')
<div class="content">
    @if (session('message'))
        <div class="alert-success">
            {{ session('message') }}
        </div>
    @endif
    <div class="content-inner">
        <div class="email-text__group">
            <p class="email-text">登録していただいたメールアドレスに認証メールを送付しました。</p>
            <p class="email-text">メール認証を完了してください。</p>
        </div>
        <form action="http://localhost:8025" method="get" class="email-form">
            @csrf
            <button class="email-form__button" type="submit">認証はこちらから</button>
        </form>
        <form class="email-link" action="/email/verification-notification" method="post">
            @csrf
            <input type="hidden" value="{{$user->id}}" name="id">
            <button class="email-link__again" type="submit">認証メールを再送する</button>
        </form>
    </div>
</div>
@endsection