<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Address;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * テストに必要な初期データを準備する
     *
     * @return array
     */
    private function setupTestData()
    {
        // データベースを初期化し、シーダーを実行
        $this->seed();

        // テストユーザーの準備
        $user = User::factory()->create();

        return ['user' => $user];
    }

    /**
     * ログインユーザーがプロフィール（住所設定）ページを閲覧できることを検証する
     *
     * @return void
     */
    public function test_authenticated_user_can_view_profile_page()
    {
        $data = $this->setupTestData();
        $user = $data['user'];

        $response = $this->actingAs($user)->get('/user/shipping-address/edit');

        // ステータスコード200 (OK)が返されていることを確認
        $response->assertStatus(200)
                 ->assertSee('住所の変更'); 
    }

    /**
     * ログインユーザーが配送先情報を新規登録できることを検証する
     *
     * @return void
     */
    public function test_authenticated_user_can_create_shipping_address()
    {
        $data = $this->setupTestData();
        $user = $data['user'];

        // フォームから送信するデータ
        $addressData = [
            'postal_code' => '123-4567',
            'prefecture' => '大阪府',
            'city' => '大阪市北区',
            'address' => '梅田1-1-1',
            'building_name' => 'テストビル101',
        ];

        $response = $this->actingAs($user)->post('/user/shipping-address/update', $addressData);

        // 成功メッセージとともにリダイレクトされることを確認
        $response->assertRedirect('/user/shipping-address/edit')
                 ->assertSessionHas('success', '配送先住所を更新しました。');

        // データベースに住所レコードが作成されたことを確認
        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'postal_code' => '123-4567',
            'address' => '梅田1-1-1', 
            'building_name' => 'テストビル101', 
        ]);
    }
}