<?php

namespace Tests\Feature;

use DiDom\Document;
use Tests\TestCase;

class UrlTest extends TestCase
{
    public int $urlId;
    public function setUp(): void
    {
        parent::setUp();
        $this->urlId = app('db')->table('urls')->insertGetId(['name' => 'https://vk.com']);
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
//        $body = optional($response)->getContent();
//        $document = new Document($body);
//        $h1 = optional($document->first('#url'))->text();
        $response->assertOk();
//        $this->assertTrue($url->name ?? null === $h1);
//        $this->assertEquals($url->name ?? null, $h1);
    }

    public function testStore(): void
    {
        $data = ['name' => 'https://ok.com'];
        $this->post(route('urls.store'), ['url' => $data]);
        $this->assertDatabaseHas('urls', $data);
    }
}
