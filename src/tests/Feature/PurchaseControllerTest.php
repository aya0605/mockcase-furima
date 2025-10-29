<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Address;
use App\Models\Condition;

class PurchaseControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 
     *
     * @return array
     */
    private function setupTestData()
    {
        $this->seed();

        $user = User::factory()->create();

        return ['user' => $user];
    }

    /**
     * 
     *
     * @return void
     */
    public function test_authenticated_user_can_view_profile_page()
    {
        $data = $this->setupTestData();
        $user = $data['user'];

        $response = $this->actingAs($user)->get('/user/shipping-address/edit');

        $response->assertStatus(200)
                 
                 ->assertSee('住所の変更'); 
    }

    /**
     * 
     *
     * @return void
     */
    public function test_authenticated_user_can_create_shipping_address()
    {
        $data = $this->setupTestData();
        $user = $data['user'];

        $addressData = [
            'postal_code' => '123-4567',
            'prefecture' => '大阪府',
            'city' => '大阪市北区',
            'address' => '梅田1-1-1',
            'building_name' => 'テストビル101',
        ];

        $response = $this->actingAs($user)->post('/user/shipping-address/update', $addressData);

        $response->assertRedirect('/user/shipping-address/edit')
                 ->assertSessionHas('success', '配送先住所を更新しました。');

        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'postal_code' => '123-4567',
            'address' => '梅田1-1-1',
            'building_name' => 'テストビル101',
        ]);
    }
}