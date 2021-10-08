<?php

namespace Tests\Feature;

use Tests\TestCase;

class UrlTest extends TestCase
{
    protected $model;

    public function setUp(): void
    {
        parent::setUp();
        app('db')->table('urls')->insert(['name' => 'https://vk.com']);
        $this->model = app('db')->table('urls')->latest()->first();
    }

    public function testIndex(): void
    {
        $response = $this->get(route('urls.index'));
        $response->assertOk();
    }

    public function testShow(): void
    {
        $response = $this->get(route('urls.show', [
            'id' => $this->model->id
            ])
        );
        $response->assertOk();
    }

    public function testStore(): void
    {
        $data = ['name' => 'https://vk.com'];
        $this->post(route('urls.store'), ['url' => $data]);
        $this->assertDatabaseHas('urls', $data);
    }
}
