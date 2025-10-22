<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Like;
use App\Models\Condition;
use App\Models\Category;

class ItemControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 
     *
     * @return array
     */
    private function setupTestData()
    {
        // データベースを初期化し、シーダーを実行
        $this->seed();

        // テストユーザーの準備
        $user = User::factory()->create();
        $seller = User::factory()->create();

        // 状態IDを取得
        $conditionId = Condition::$HARMLESS;

        // テスト用の商品を出品
        $item = Item::create([
            'name' => 'テスト商品',
            'price' => 10000,
            'brand' => 'TestBrand',
            'description' => '商品詳細テスト用の商品です。',
            'image_url' => '/storage/test/image.png',
            'seller_id' => $seller->id,
            'condition_id' => $conditionId,
            'is_sold' => false,
        ]);

        return ['user' => $user, 'item' => $item];
    }

    /**
     * ログインしているユーザーが商品詳細ページを正しく閲覧できることを検証する
     *
     * @return void
     */
    public function test_authenticated_user_can_view_item_detail()
    {
        $data = $this->setupTestData();
        $user = $data['user'];
        $item = $data['item'];

        // 認証済みユーザーとして詳細ページにアクセス
        $response = $this->actingAs($user)->get("/items/{$item->id}");

        // ステータスコード200 (OK)が返され、商品名が表示されていることを確認
        $response->assertStatus(200)
                 ->assertSee('商品詳細') // Viewのタイトルや見出しを確認
                 ->assertSee($item->name) // 商品名が表示されていることを確認
                 ->assertViewHas('item', function ($viewItem) use ($item) {
                     return $viewItem->id === $item->id;
                 });
    }

    /**
     * ログインユーザーが商品に「いいね」を追加できることを検証する
     *
     * @return void
     */
    public function test_user_can_like_an_item()
    {
        $data = $this->setupTestData();
        $user = $data['user'];
        $item = $data['item'];

        // likesテーブルにいいねがないことを確認
        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        // いいね切り替えエンドポイントにPOSTリクエストを送信
        $response = $this->actingAs($user)->post("/items/{$item->id}/like");

        // 商品詳細ページにリダイレクトされることを確認
        $response->assertRedirect("/items/{$item->id}");

        // likesテーブルに新しいレコードが作成されたことを確認
        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        // セッションに成功メッセージが含まれていることを確認
        $response->assertSessionHas('status', 'いいねしました！');
    }

    /**
     * ログインユーザーが商品から「いいね」を解除できることを検証する
     *
     * @return void
     */
    public function test_user_can_unlike_an_item()
    {
        $data = $this->setupTestData();
        $user = $data['user'];
        $item = $data['item'];

        // 事前にいいねを登録
        Like::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        // いいねがlikesテーブルに存在することを確認
        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        // いいね切り替えエンドポイントにPOSTリクエストを送信（解除の動作となる）
        $response = $this->actingAs($user)->post("/items/{$item->id}/like");

        // 商品詳細ページにリダイレクトされることを確認
        $response->assertRedirect("/items/{$item->id}");

        // likesテーブルからレコードが削除されたことを確認
        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        // セッションに解除メッセージが含まれていることを確認
        $response->assertSessionHas('status', 'いいね機能解除');
    }
}