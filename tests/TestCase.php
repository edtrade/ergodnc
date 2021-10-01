<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    //override
    public function actingAs($user,$abilities=['*'])
    {
        Sanctum::actingAs($user,$abilities);

        return $this;
    }
}
