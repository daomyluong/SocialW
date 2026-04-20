<?php

namespace Tests\Feature\TV1;

use Tests\TestCase;

class HomeSearchTest extends TestCase
{
    public function test_it_loads_home_page(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_it_loads_search_page(): void
    {
        $this->get('/search')->assertOk();
    }

    public function test_it_can_search_with_query_placeholder(): void
    {
        $this->get('/search?query=test')->assertOk();

        // TODO(TV1): assert search result payload/view content when search logic is implemented.
    }

    public function test_it_loads_latest_feed_endpoint(): void
    {
        $this->get('/feed/latest')->assertOk();
    }

    public function test_it_returns_feed_json_contract(): void
    {
        $response = $this->getJson('/feed/latest');

        $response->assertOk()->assertJsonStructure([
            'data',
            'meta' => [
                'count',
                'server_time',
            ],
        ]);
    }
}
