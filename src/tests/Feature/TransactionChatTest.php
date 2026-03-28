<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Item;
use App\Models\Condition;
use Tests\TestCase;

class TransactionChatTest extends TestCase
{
    use RefreshDatabase; 

    public function test_user_can_send_chat_message()
    {
        $condition = Condition::create(['name' => '新品']);
        $user = User::factory()->create();
        $item = Item::factory()->create([
            'seller_id' => $user->id,
            'condition_id' => $condition->id
        ]);

        $response = $this->actingAs($user)->post("/trade/chat/{$item->id}/send", [
            'content' => 'テストメッセージです',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('messages', [
            'item_id' => $item->id,
            'user_id' => $user->id,
            'content' => 'テストメッセージです',
        ]);
    }

    public function test_chat_content_is_required()
    {
        $condition = Condition::create(['name' => '新品']);
        $user = User::factory()->create();
        $item = Item::factory()->create(['condition_id' => $condition->id]);

        $response = $this->actingAs($user)->post("/trade/chat/{$item->id}/send", [
            'content' => '',
        ]);

        $response->assertSessionHasErrors(['content']);
    }

    public function test_chat_content_max_length()
    {
        $condition = Condition::create(['name' => '新品']);
        $user = User::factory()->create();
        $item = Item::factory()->create(['condition_id' => $condition->id]);

        $response = $this->actingAs($user)->post("/trade/chat/{$item->id}/send", [
            'content' => str_repeat('あ', 401),
        ]);

        $response->assertSessionHasErrors(['content']);
    }
}
