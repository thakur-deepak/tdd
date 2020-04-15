<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Faker\Factory as Faker;

class UserRegisterTest extends TestCase
{


    use RefreshDatabase, WithFaker;
    private $user_data;


    /**
     * @test
     */
    public function register_route_with_correct_data(){
        $this->user = [
            'name'         => $this->faker->name,
            'email'         => $this->faker->unique()->safeEmail,
            'password'      => 'secret',
        ];

        $response = $this->json('POST', route('user.store'), $this->user);
        $response->assertStatus(201);


    }
}
