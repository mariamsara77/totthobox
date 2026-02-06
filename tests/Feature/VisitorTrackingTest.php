<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VisitorTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_is_created_on_first_visit()
    {
        $response = $this->get('/', [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'HTTP_REFERER' => 'https://google.com',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('visitors', [
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'referrer' => 'https://google.com',
            'referrer_domain' => 'google.com',
        ]);
    }

    public function test_visitor_is_updated_on_subsequent_visits()
    {
        $visitor = Visitor::factory()->create([
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'first_seen_at' => now()->subDay(),
            'last_seen_at' => now()->subDay(),
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);

        $this->assertDatabaseHas('visitors', [
            'id' => $visitor->id,
            'last_seen_at' => now()->toDateTimeString(),
        ]);
    }

    public function test_page_view_is_recorded()
    {
        $visitor = Visitor::factory()->create();

        $response = $this->get('/test-page');

        $response->assertStatus(200);

        $this->assertDatabaseHas('page_views', [
            'visitor_id' => $visitor->id,
            'url' => 'http://localhost/test-page',
        ]);
    }

    public function test_event_tracking_api()
    {
        $visitor = Visitor::factory()->create();

        $response = $this->postJson('/track-event', [
            'event_type' => 'test',
            'event_name' => 'Test Event',
            'event_data' => ['foo' => 'bar'],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('visitor_events', [
            'visitor_id' => $visitor->id,
            'event_type' => 'test',
            'event_name' => 'Test Event',
        ]);
    }
}
