<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 
     *
     * @return void
     */
    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /**
     * ユーザーがメールアドレスとパスワードでログインできることを検証する
     *
     * @return void
     */
    public function test_users_can_authenticate_using_the_login_screen()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // 認証されていることを確認
        $this->assertAuthenticated();
        
        // ログイン後のトップページにリダイレクトされることを確認
        $response->assertRedirect('/');
    }

    /**
     * 不正な認証情報でログインできないことを検証する
     *
     * @return void
     */
    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        // 認証されていないことを確認
        $this->assertGuest();
    }
}