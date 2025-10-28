<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 登録画面が表示されることを検証する
     *
     * @return void
     */
    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    /**
     * 新規ユーザーが登録できることを検証する
     *
     * @return void
     */
    public function test_new_users_can_register()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // データベースにユーザーが作成されたことを確認
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        $response->assertRedirect('/');
    }

    /**
     * 不正なデータでユーザー登録が失敗することを検証する (例: パスワード不一致)
     *
     * @return void
     */
    public function test_new_user_registration_fails_with_invalid_data()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'invalid@example.com',
            'password' => 'password',
            'password_confirmation' => 'wrong-password', // パスワード不一致
        ]);

        // 認証されていないことを確認
        $this->assertGuest();
        
        // バリデーションエラーで登録ページに戻されることを確認
        $response->assertSessionHasErrors('password');
    }
}