<?php

namespace Tests\Feature\API;

use App\Mail\UserRegistered;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test successful user registration.
     *
     * @return void
     */
    public function testSuccessfulRegistration(): void
    {
        Mail::fake();

        $name = $this->faker->name;
        $email = $this->faker->unique()->safeEmail;
        $password = Str::random(10);

        $response = $this->postJson('/api/register', [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success' => [
                    'token',
                    'name',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'name' => $name,
            'email' => $email,
        ]);

        $user = User::where('email', $email)->first();

        $this->assertTrue(Hash::check($password, $user->password));

        Mail::assertSent(UserRegistered::class, function ($mail) use ($user) {
            return $mail->user->id === $user->id;
        });
    }

    /**
     * Test user registration with missing fields.
     *
     * @return void
     */
    public function testRegistrationWithMissingFields(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /**
     * Test user registration with password and password_confirmation not matching.
     *
     * @return void
     */
    public function testRegistrationWithMismatchingPasswords(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => Str::random(10),
            'password_confirmation' => Str::random(10),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test user registration with an existing email address.
     *
     * @return void
     */
    public function testRegistrationWithExistingEmail(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/register', [
            'name' => $this->faker->name,
            'email' => $user->email,
            'password' => Str::random(10),
            'password_confirmation' => Str::random(10),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
