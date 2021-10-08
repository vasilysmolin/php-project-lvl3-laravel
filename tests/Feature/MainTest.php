<?php

namespace Tests\Feature;

use Tests\TestCase;

class MainTest extends TestCase
{
    public function testIndex(): void
    {
        $response = $this->get(route('main'));
        $response->assertOk();
    }
}
