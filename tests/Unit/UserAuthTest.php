<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UserAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user registration with valid data.
     *
     * @return void
     */
    public function testUserRegistrationWithValidData()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password123',
        ];


        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'token',
                    'name',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
        ]);
    }

    /**
     * Test user registration with an existing email.
     *
     * @return void
     */
    public function testUserRegistrationWithExistingEmail()
    {
        User::factory()->create([
            'email' => 'existinguser@example.com',
        ]);

        $userData = [
            'name' => 'John Doe',
            'email' => 'existinguser@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);
        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'data' => [],
            ]);
    }

    /**
     * Test user registration with missing required fields.
     *
     * @return void
     */
    public function testUserRegistrationWithMissingRequiredFields()
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'data' => [],
        ]);
    }

        /**
     * Test user login with valid credentials.
     *
     * @return void
     */
    public function testUserLoginWithValidCredentials()
    {
        $user = User::factory()->create([
            'email' => 'johndoe@example.com',
            'password' => bcrypt('password123'),
        ]);

        $loginData = [
            'email' => 'johndoe@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'token',
                    'name',
                ],
            ]);

        $this->assertAuthenticated();
    }

    /**
     * Test user login with invalid credentials.
     *
     * @return void
     */
    public function testUserLoginWithInvalidCredentials()
    {
        $loginData = [
            'email' => 'invaliduser@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(401)
        ->assertJsonStructure([
            'message',
            'data' => [
            ],
        ]);

        $this->assertGuest();
    }

}
