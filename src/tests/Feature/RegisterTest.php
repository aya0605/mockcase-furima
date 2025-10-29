<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 
     *
     * @return void
     */
    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    /**
     * 
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

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        $response->assertRedirect('/');
    }

    /**
     * 
     *
     * @return void
     */
    public function test_new_user_registration_fails_with_invalid_data()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'invalid@example.com',
            'password' => 'password',
            'password_confirmation' => 'wrong-password', 
        ]);

        $this->assertGuest();
        
        $response->assertSessionHasErrors('password');
    }
}