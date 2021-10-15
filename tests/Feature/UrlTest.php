<?php

namespace Tests\Feature;

use DiDom\Document;
use Tests\TestCase;

class UrlTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
        app('db')->table('urls')->insert(['name' => 'https://vk.com']);
    }

    public function testIndex(): void
    {
        $response = $this->get(route('urls.index'));
        $response->assertOk();
    }

    public function testShow(): void
    {
        $url = app('db')->table('urls')->first();

        $response = $this->get(route('urls.show', [
                'id' => optional($url)->id
            ]));
        $body = $response->getContent();
        $document = new Document($body);
        $h1 = optional($document->first('#url'))->text();
        $this->assertEquals($h1, $url->name);
        $response->assertOk();
    }

    public function testStore(): void
    {
        $data = ['name' => 'https://ok.com'];
        $this->post(route('urls.store'), ['url' => $data]);
        $this->assertDatabaseHas('urls', $data);
    }
}
