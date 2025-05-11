<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;

class EmailVerifyTest extends TestCase
{
    use RefreshDatabase;

    public function testSendVerifyEmail(){
        Notification::fake();
        $response=$this->post('/register', [
            'name'=>'test',
            'email'=>'test@example.com',
            'password'=>'password123',
            'password_confirmation'=>'password123'
        ]);
        $user=User::where('email', 'test@example.com')->first();
        $response->assertRedirect('/email/verify/' . $user->id);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function testEmailVerificationRedirection(){
        Notification::fake();
        $response=$this->post('/register', [
            'name'=>'test',
            'email'=>'test@example.com',
            'password'=>'password123',
            'password_confirmation'=>'password123'
        ]);
        $user=User::where('email', 'test@example.com')->first();
        $response->assertRedirect('/email/verify/' . $user->id);

        $response=$this->get('/email/verify/' . $user->id);
        $response->assertSee('認証はこちらから');
        $mailhogResponse = Http::get('http://mailhog:8025');
        $this->assertTrue($mailhogResponse->successful());
    }

    public function testEmailVerificationComplete(){
        Notification::fake();
        $response=$this->post('/register', [
            'name'=>'test',
            'email'=>'test@example.com',
            'password'=>'password123',
            'password_confirmation'=>'password123'
        ]);
        $user=User::where('email', 'test@example.com')->first();
        $response->assertRedirect('/email/verify/' . $user->id);

        Notification::assertSentTo($user, VerifyEmail::class);

        $verificationUrl=URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            ['id'=>$user->id, 'hash'=>sha1($user->email)]
        );
        $response=$this->get($verificationUrl);
        $response->assertRedirect('/attendance');

        $response=$this->get('/attendance');
        $response->assertStatus(200);
    }
}
