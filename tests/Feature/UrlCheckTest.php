<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;

class UrlCheckTest extends TestCase
{
    protected string $fixture;

    public function setUp(): void
    {
        parent::setUp();

        $data = [
            'name' => 'https://vk.com'
        ];
        app('db')->table('urls')->insert($data);
        $this->fixture = __DIR__ . '/../Fixtures/index.html';
    }
    /**
     * @group fail
     */
    public function testStore(): void
    {

        Http::fake([
            // Stub a JSON response for endpoints...
            '*' => Http::response([], 200)
        ]);
        $model = app('db')->table('urls')->latest()->first();
        $this->post(route('urls.checks.store', [$model->id]));

        $checkData = [
            'status_code' => '200',
            'url_id' => $model->id,
            'keywords' => 'laravel',
            'description' => 'laravel analyze',
            'h1' => 'laravel analyze'
        ];
        $this->assertDatabaseHas('url_checks', $checkData);
    }
}
