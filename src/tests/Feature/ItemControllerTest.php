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
        $this->seed();

        $user = User::factory()->create();
        $seller = User::factory()->create();

        $conditionId = Condition::$HARMLESS;

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
     * 
     *
     * @return void
     */
    public function test_authenticated_user_can_view_item_detail()
    {
        $data = $this->setupTestData();
        $user = $data['user'];
        $item = $data['item'];

        $response = $this->actingAs($user)->get("/items/{$item->id}");

        $response->assertStatus(200)
                 ->assertSee('商品詳細') 
                 ->assertSee($item->name) 
                 ->assertViewHas('item', function ($viewItem) use ($item) {
                     return $viewItem->id === $item->id;
                 });
    }

    /**
     * 
     *
     * @return void
     */
    public function test_user_can_like_an_item()
    {
        $data = $this->setupTestData();
        $user = $data['user'];
        $item = $data['item'];

        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $response = $this->actingAs($user)->post("/items/{$item->id}/like");

        $response->assertRedirect("/items/{$item->id}");

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $response->assertSessionHas('status', 'いいねしました！');
    }

    /**
     * 
     *
     * @return void
     */
    public function test_user_can_unlike_an_item()
    {
        $data = $this->setupTestData();
        $user = $data['user'];
        $item = $data['item'];

        Like::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $response = $this->actingAs($user)->post("/items/{$item->id}/like");

        $response->assertRedirect("/items/{$item->id}");

        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $response->assertSessionHas('status', 'いいね機能解除');
    }
}