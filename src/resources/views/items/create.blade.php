@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/create.css') }}">
@endsection

@section('content')
    <div class="sell-container">
        <form action="/sell" method="POST" enctype="multipart/form-data" class="sell-form">
            @csrf
            <h1>商品の出品</h1>
            <div>
                <label for="image">商品画像</label>
                <input type="file" id="image" name="image">
                @error('image')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <h2>商品の詳細</h2>
            <div>
                <label>カテゴリー</label>
                <div class="category-options">
                    @foreach ($categories as $category)
                        <input type="checkbox" name="categories[]" value="{{ $category->id }}" id="category_{{ $category->id }}" class="category-hidden-checkbox"
                            @if (is_array(old('categories')) && in_array($category->id, old('categories')))
                                checked
                            @endif
                        > <label for="category_{{ $category->id }}" class="category-custom-label">
                            {{ $category->name }}
                        </label>
                    @endforeach
                </div>
                @error('categories')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="condition">商品の状態</label>
                <select name="condition" id="condition">
                    <option value="new">新品、未使用</option>
                    <option value="near_unused">未使用に近い</option>
                    <option value="slightly_damaged">目立った傷や汚れなし</option>
                    <option value="damaged">傷や汚れあり</option>
                    <option value="bad_condition">全体的に状態が悪い</option>
                </select>
                @error('condition')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <h2>商品名と説明</h2>
            <div>
                <label for="name">商品名</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}">
                @error('name')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="brand">ブランド名</label>
                <input type="text" id="brand" name="brand" value="{{ old('brand') }}">
                @error('brand')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="description">商品の説明</label>
                <textarea id="description" name="description">{{ old('description') }}</textarea>
                @error('description')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="product_price">販売価格</label>
                <div class="price-container">
                    <input type="text" class="text" placeholder="￥" name="product_price" id="product_price" value="{{ old('product_price') }}">
                </div>
                @error('product_price')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit">出品する</button>
        </form>
    </div>
@endsection