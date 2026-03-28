<!DOCTYPE html>
<html>
<head>
    <title>取引完了通知</title>
</head>
<body>
    <h1>取引が完了しました！</h1>
    <p>{{ $item->seller->name }} 様</p>
    <p>出品された以下の商品の取引が完了しましたのでお知らせいたします。</p>
    
    <hr>
    <p><strong>商品名：</strong> {{ $item->name }}</p>
    <p><strong>販売価格：</strong> ¥{{ number_format($item->price) }}</p>
    <hr>

    <p>マイページから詳細を確認してください。</p>
</body>
</html>