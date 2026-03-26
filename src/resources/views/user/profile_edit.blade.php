@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/profile_edit.css') }}"> 
@endsection

@section('content')
    <div class="profile-edit-container">
        <h1>プロフィール設定</h1>

        {{-- 更新完了時の成功メッセージ --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- 
            enctype="multipart/form-data": 
            画像を送信するために必須の属性です。
        --}}
        <form action="/user/profile/update" method="POST" enctype="multipart/form-data" class="profile-form">
            @csrf

            {{-- 1. プロフィール画像編集エリア --}}
            <div class="form-group profile-image-group">
                <div class="current-image-wrapper">
                    {{-- 既に画像があればそれを表示、なければデフォルト画像を表示 --}}
                    @if ($user->profile_image_path)
                        <img src="{{ Storage::url($user->profile_image_path) }}" alt="プロフィール画像" class="current-profile-image">
                    @else
                        <img src="{{ asset('images/default_profile.png') }}" alt="デフォルト画像" class="current-profile-image">
                    @endif
                </div>
                {{-- ファイルアップロードフィールド --}}
                <input type="file" id="profile_image" name="profile_image" class="form-control-file">
                @error('profile_image')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- 2. ユーザー名の編集 --}}
            <div class="form-group">
                <label for="name">ユーザー名</label>
                {{-- old関数で、前回の入力値がある場合はそれを優先して表示 --}}
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
                @error('name')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- 3. 郵便番号の編集 --}}
            <div class="form-group">
                <label for="postal_code">郵便番号</label>
                <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code', $address->postal_code) }}" class="form-control" required>
                @error('postal_code')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- 4. 住所の編集 --}}
            <div class="form-group">
                <label for="address">住所</label>
                <input type="text" id="address" name="address" value="{{ old('address', $address->address) }}" class="form-control" required>
                @error('address')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- 5. 建物名の編集（任意項目） --}}
            <div class="form-group">
                <label for="building_name">建物名 (任意)</label>
                <input type="text" id="building_name" name="building_name" value="{{ old('building_name', $address->building_name) }}" class="form-control">
                @error('building_name')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">更新する</button>
            </div>
        </form>
    </div>
@endsection