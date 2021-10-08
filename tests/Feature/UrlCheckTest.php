<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;

class UrlCheckTest extends TestCase
{
    protected $model;
    protected $fixture;

    public function setUp(): void
    {
        parent::setUp();

        $data = [
            'name' => 'https://vk.com'
        ];
        app('db')->table('urls')->insert($data);
        $this->model = app('db')->table('urls')->latest()->first();
        $this->fixture = __DIR__ . '/../Fixtures/index.html';
    }
    /**
     * @group fail
     */
    public function testStore(): void
    {

        $body = file_get_contents($this->fixture);

        Http::fake([
            // Stub a JSON response for endpoints...
            '*' => Http::response($body, 200)
        ]);

        $this->post(route('urls.checks.store', [$this->model->id]));

        $checkData = [
            'status_code' => '200',
            'url_id' => $this->model->id,
            'keywords' => 'laravel',
            'description' => 'laravel analyze',
            'h1' => 'laravel analyze'
        ];
        $this->assertDatabaseHas('urls_check', $checkData);
    }
}
