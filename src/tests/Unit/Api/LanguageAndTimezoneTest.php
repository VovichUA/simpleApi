<?php

namespace tests\Unit\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class LanguageAndTimezoneTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    /** @test */
    public function authenticated_user_can_update_language_and_timezone(): void
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $data = [
            'language' => 'uk',
            'timezone' => 'Europe/Kiev',
        ];

        $response = $this->json('PUT', '/api/settings', $data);

        $response->assertStatus(200);
        $this->assertEquals($data['language'], $user->fresh()->language);
        $this->assertEquals($data['timezone'], $user->fresh()->timezone);
    }

    /** @test */
    public function unauthenticated_user_cannot_update_language_and_timezone(): void
    {
        $data = [
            'language' => 'uk',
            'timezone' => 'Europe/Kiev',
        ];

        $response = $this->json('PUT', '/api/settings', $data);

        $response->assertStatus(401);
        $this->assertNotEquals($data['language'], User::first()->language);
        $this->assertNotEquals($data['timezone'], User::first()->timezone);
    }

    /** @test */
    public function language_is_required_to_update_settings(): void
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $data = [
            'timezone' => 'Europe/Kiev',
        ];

        $response = $this->json('PUT', '/api/settings', $data);

        $response->assertStatus(422);
        $this->assertNotEquals($data['timezone'], $user->fresh()->timezone);
    }

    /** @test */
    public function timezone_is_required_to_update_settings(): void
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $data = [
            'language' => 'uk',
        ];

        $response = $this->json('PUT', '/api/settings', $data);

        $response->assertStatus(422);
        $this->assertNotEquals($data['language'], $user->fresh()->language);
    }
}
