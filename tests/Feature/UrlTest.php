<?php

namespace Tests\Feature;

use DiDom\Document;
use Tests\TestCase;

class UrlTest extends TestCase
{
    public int $urlId;
    public string $domain;
    public function setUp(): void
    {
        parent::setUp();
        $this->domain = 'https://vk.com';
        $this->urlId = app('db')->table('urls')->insertGetId(['name' => $this->domain]);
    }

    public function testIndex(): void
    {
        $response = $this->get(route('urls.index'));
        $response->assertOk();
    }

    public function testShow(): void
    {
        $response = $this->get(route('urls.show', [
                'id' => $this->urlId
            ]));
        $response->assertSee($this->domain);
        $response->assertOk();
    }

    public function testStore(): void
    {
        $data = ['name' => 'https://ok.com'];
        $this->post(route('urls.store'), ['url' => $data]);
        $this->assertDatabaseHas('urls', $data);
    }
}
