{{-- resources/views/user/edit_shipping_address.blade.php --}}

@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/address_edit.css') }}">
@endsection

@section('content')
    <div class="address-edit-container">
        <h1>住所の変更</h1>

        {{-- 更新完了時にControllerから送られてくるメッセージを表示 --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form action="/user/shipping-address/update" method="POST" class="address-edit-form">
            @csrf

            {{-- 
                郵便番号入力エリア
                old('postal_code', $address->postal_code) の意味:
                1. フォーム送信後にバリデーションエラーで戻ってきた場合は old('postal_code') を優先
                2. 初めてアクセスした場合は DBから取得した $address->postal_code を表示
            --}}
            <div class="form-group">
                <label for="postal_code">郵便番号</label>
                <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code', $address->postal_code) }}" class="form-control" required> 
                @error('postal_code')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- 住所入力エリア --}}
            <div class="form-group">
                <label for="address">住所</label>
                <input type="text" id="address" name="address" value="{{ old('address', $address->address) }}" class="form-control" required>
                @error('address')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- 建物名入力エリア --}}
            <div class="form-group">
                <label for="building_name">建物名</label> 
                <input type="text" id="building_name" name="building_name" value="{{ old('building_name', $address->building_name) }}" class="form-control" required> 
                @error('building_name')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">更新する</button>
        </form>
    </div>
@endsection