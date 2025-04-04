@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<div class="login-form__content">
    <h2 class="login-form__heading">
        管理者ログイン
    </h2>
    <form class="login-form" action="{{route('admin.login')}}" method="post">
        @csrf
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
            <span class="form__group-title">パスワード
            </span>
            <div class="form__group-content">
                <input class="form__input--text" type="password" name="password" />
                <div class="form__error">
                    @error('password')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>
        <div class="form__button">
            <button class="form__button-submit" type="submit">管理者ログインする</button>
        </div>
    </form>
</div>
@endsection