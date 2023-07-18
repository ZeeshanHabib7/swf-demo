<?php

namespace Tests;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function getUserToken(){
        $user = User::factory()->create([
            'email' => 'johndoe@example.com',
            'password' => bcrypt('password123'),
        ]);
        $loginData = [
            'email' => 'johndoe@example.com',
            'password' => 'password123',
        ];
        $response = $this->postJson('/api/login', $loginData);
        return $response->json()['data']['token'];
    }
}
