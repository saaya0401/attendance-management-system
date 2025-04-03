@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')
<div class="register-form__content">
    <h2 class="register-form__heading">
        会員登録
    </h2>
    <form class="register-form" method="post" action="{{route('register')}}">
        @csrf
        <div class="form__group">
            <span class="form__group-title">名前</span>
            <div class="form__group-content">
                <input class="form__input--text" type="text" name="name" value="{{ old('name') }}" />
                <div class="form__error">
                    @error('name')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>
        <div class="form__group">
            <span class="form__group-title">メールアドレス</span>
            <div class="form__group-content">
                <input class="form__input--text" type="email" name="email" value="{{ old('email') }}" />
                <div class="form__error">
                    @error('email')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>
        <div class="form__group">
            <span class="form__group-title">パスワード</span>
            <div class="form__group-content">
                <input class="form__input--text" type="password" name="password" />
                <div class="form__error">
                    @error('password')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>
        <div class="form__group">
            <span class="form__group-title">パスワード確認</span>
            <div class="form__group-content">
                <input class="form__input--text" type="password" name="password_confirmation" />
            </div>
        </div>
        <div class="form__button">
            <button class="form__button-submit" type="submit">登録する</button>
        </div>
    </form>
    <div class="login__link">
        <a class="login__button-submit" href="/login">ログインはこちら</a>
    </div>
</div>
@endsection
