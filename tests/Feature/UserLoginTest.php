<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\User;
use Laravel\Sanctum\Sanctum;

class UserLoginTest extends TestCase
{
    use RefreshDatabase;

    private $user_data;

    public function setUp(): void
    {
        parent::setup();
        $this->user = factory(\App\User::class)->create(['email_verified_at'=>Carbon::now()]);
        $this->user_data['email'] = $this->user->email;
        $this->user_data['password'] = 'secret';
        $this->user_data['device_name'] = 'mobile';
    }

    /**
     * @test
     */
    public function a_user_can_login_with_correct_data()
    {
        $response = $this->json('POST', route('user.login'), $this->user_data);
        $response->assertStatus(200);
        $response->assertOk();
    }

    /**
     * @test
     */
    public function if_user_email_format_is_incorrect()
    {
        $this->user_data['email'] = 'test';
        $response = $this->json('POST', route('user.login'), $this->user_data);
        $response->assertStatus(422)->assertSee('The given data was invalid.');
    }

    /**
     * @test
     */
    public function if_user_credentials_are_incorrect()
    {
        $this->user_data['password'] = 'nosecret';
        $response = $this->json('POST', route('user.login'), $this->user_data);
        $response->assertStatus(422)->assertSee('The provided password is incorrect.');
    }


}
