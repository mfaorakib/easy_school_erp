<?php

namespace Modules\Chat\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Chat\Models\ChatGroup;
use Modules\Chat\Services\ChatService;

class ChatDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@easyschool.test')->first();
        $other = User::where('id', '!=', optional($admin)->id)->orderBy('id')->first();

        if (! $admin || ! $other) {
            return;
        }

        $chat = app(ChatService::class);

        // A short direct exchange.
        if (\Modules\Chat\Models\ChatMessage::between($admin->id, $other->id)->doesntExist()) {
            $chat->sendDirect($admin->id, $other->id, ['body' => 'Welcome to EasySchool chat!']);
            $chat->sendDirect($other->id, $admin->id, ['body' => 'Thank you!']);
        }

        // A demo group with a welcome message.
        if (! ChatGroup::where('name', 'Staff Room')->exists()) {
            $group = $chat->createGroup(
                ['name' => 'Staff Room', 'description' => 'General staff discussion', 'type' => ChatGroup::TYPE_OPEN],
                $admin->id,
                [$other->id],
            );
            $chat->sendGroup($group, $admin->id, ['body' => 'Welcome everyone to the Staff Room group.']);
        }
    }
}
