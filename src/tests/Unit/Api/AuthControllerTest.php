<?php

namespace Tests\Unit\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Test a successful login
     *
     * @return void
     */
    public function testSuccessfulLogin(): void
    {
        // Create a user with a hashed password
        $password = $this->faker->password();
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        // Make a login request with the user's credentials
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        // Assert that the response has a successful status code and contains a token
        $response->assertStatus(200);
        $response->assertJsonStructure(['token']);
    }

    /**
     * Test an unsuccessful login with invalid credentials
     *
     * @return void
     */
    public function testUnsuccessfulLoginInvalidCredentials(): void
    {
        // Make a login request with invalid credentials
        $response = $this->postJson('/api/login', [
            'email' => $this->faker->safeEmail,
            'password' => $this->faker->password(),
        ]);

        // Assert that the response has a 401 status code and contains an error message
        $response->assertStatus(401);
        $response->assertJson(['error' => 'Invalid credentials']);
    }

    /**
     * Test an unsuccessful login with missing credentials
     *
     * @return void
     */
    public function testUnsuccessfulLoginMissingCredentials(): void
    {
        // Make a login request with missing credentials
        $response = $this->postJson('/api/login', []);

        // Assert that the response has a 422 status code and contains a validation error message
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email', 'password']);
    }
}
