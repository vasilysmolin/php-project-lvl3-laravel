<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;

class UrlCheckTest extends TestCase
{
    protected string $pathFixture;

    public function setUp(): void
    {
        parent::setUp();

        $data = [
            'name' => 'https://vk.com'
        ];
        app('db')->table('urls')->insert($data);
        $this->pathFixture = __DIR__ . '/../Fixtures/index.html';
    }

    /**
     * @throws \Exception
     */
    public function testStore(): void
    {

        $body = file_get_contents($this->pathFixture);

        if ($body === false) {
            throw new \Exception('fixtures file not found');
        }

        Http::fake([
            // Stub a JSON response for endpoints...
            '*' => Http::response($body, 200)
        ]);
        $url = app('db')->table('urls')->first();
        if (!is_null($url)) {
            $this->post(route('urls.checks.store', [$url->id]));
        }

        $checkData = [
            'status_code' => '200',
            'url_id' => optional($url)->id,
            'keywords' => 'laravel',
            'description' => 'laravel analyze',
            'h1' => 'laravel analyze'
        ];
        $this->assertDatabaseHas('url_checks', $checkData);
    }
}
