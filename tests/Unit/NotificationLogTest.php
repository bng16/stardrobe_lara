<?php

namespace Tests\Unit;

use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationLogTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_notification_log()
    {
        $user = User::factory()->create();

        $notificationLog = NotificationLog::create([
            'user_id' => $user->id,
            'type' => 'auction_won',
            'subject' => 'You won the auction!',
            'sent_at' => now(),
        ]);

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $user->id,
            'type' => 'auction_won',
            'subject' => 'You won the auction!',
        ]);

        $this->assertInstanceOf(NotificationLog::class, $notificationLog);
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $user = User::factory()->create();

        $notificationLog = NotificationLog::create([
            'user_id' => $user->id,
            'type' => 'payment_confirmation',
            'subject' => 'Payment received',
            'sent_at' => now(),
        ]);

        $this->assertInstanceOf(User::class, $notificationLog->user);
        $this->assertEquals($user->id, $notificationLog->user->id);
    }

    /** @test */
    public function user_has_many_notification_logs()
    {
        $user = User::factory()->create();

        NotificationLog::create([
            'user_id' => $user->id,
            'type' => 'auction_won',
            'subject' => 'You won!',
            'sent_at' => now(),
        ]);

        NotificationLog::create([
            'user_id' => $user->id,
            'type' => 'payment_confirmation',
            'subject' => 'Payment received',
            'sent_at' => now(),
        ]);

        $this->assertCount(2, $user->notificationLogs);
    }

    /** @test */
    public function it_uses_uuid_as_primary_key()
    {
        $user = User::factory()->create();

        $notificationLog = NotificationLog::create([
            'user_id' => $user->id,
            'type' => 'test_notification',
            'subject' => 'Test',
            'sent_at' => now(),
        ]);

        $this->assertIsString($notificationLog->id);
        $this->assertEquals(36, strlen($notificationLog->id)); // UUID length
    }

    /** @test */
    public function it_casts_sent_at_to_datetime()
    {
        $user = User::factory()->create();

        $notificationLog = NotificationLog::create([
            'user_id' => $user->id,
            'type' => 'test_notification',
            'subject' => 'Test',
            'sent_at' => '2024-01-15 10:30:00',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $notificationLog->sent_at);
    }
}
