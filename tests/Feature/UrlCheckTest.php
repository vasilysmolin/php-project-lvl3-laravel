<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;

class UrlCheckTest extends TestCase
{
    protected string $pathFixture;
    public int $urlId;

    public function setUp(): void
    {
        parent::setUp();

        $this->urlId = app('db')->table('urls')->insertGetId(['name' => 'https://vk.com']);
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

        $this->post(route('urls.checks.store', [$this->urlId]));

        $checkData = [
            'status_code' => '200',
            'url_id' => $this->urlId,
            'keywords' => 'laravel',
            'description' => 'laravel analyze',
            'h1' => 'laravel analyze'
        ];
        $this->assertDatabaseHas('url_checks', $checkData);
    }
}
