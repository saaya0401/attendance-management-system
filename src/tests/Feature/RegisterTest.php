<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function testNameEmpty(){
        $response=$this->get('/register');
        $response->assertStatus(200);

        $response=$this->post('/register', [
            'name'=>'',
            'email'=>'test@example.com',
            'password'=>'password123',
            'password_confirmation'=>'password123',
            '_token' => csrf_token(),
        ]);
        $response->assertSessionHasErrors(['name'=>'お名前を入力してください']);
    }

    public function testEmailEmpty(){
        $response=$this->get('/register');
        $response->assertStatus(200);

        $response=$this->post('/register', [
            'name'=>'test',
            'email'=>'',
            'password'=>'password123',
            'password_confirmation'=>'password123',
            '_token' => csrf_token(),
        ]);
        $response->assertSessionHasErrors(['email'=>'メールアドレスを入力してください']);
    }

    public function testPasswordMin(){
        $response=$this->get('/register');
        $response->assertStatus(200);

        $response=$this->post('/register', [
            'name'=>'test',
            'email'=>'test@example.com',
            'password'=>'passwor',
            'password_confirmation'=>'passwor',
            '_token' => csrf_token(),
        ]);
        $response->assertSessionHasErrors(['password'=>'パスワードは8文字以上で入力してください']);
    }

    public function testPasswordDifferent(){
        $response=$this->get('/register');
        $response->assertStatus(200);

        $response=$this->post('/register', [
            'name'=>'test',
            'email'=>'test@example.com',
            'password'=>'password123',
            'password_confirmation'=>'password122',
            '_token' => csrf_token(),
        ]);
        $response->assertSessionHasErrors(['password_confirmation'=>'パスワードと一致しません']);
    }

    public function testPasswordEmpty(){
        $response=$this->get('/register');
        $response->assertStatus(200);

        $response=$this->post('/register', [
            'name'=>'test',
            'email'=>'test@example.com',
            'password'=>'',
            'password_confirmation'=>'password122',
            '_token' => csrf_token(),
        ]);
        $response->assertSessionHasErrors(['password'=>'パスワードを入力してください']);
    }

    public function testRegister(){
        $response=$this->get('/register');
        $response->assertStatus(200);

        $response=$this->post('/register', [
            'name'=>'test',
            'email'=>'test@example.com',
            'password'=>'password123',
            'password_confirmation'=>'password123',
            '_token' => csrf_token(),
        ]);
        $this->assertDatabaseHas('users', [
            'name'=>'test',
            'email'=>'test@example.com'
        ]);

        $user=User::where('email', 'test@example.com')->first();
        $response->assertRedirect('/email/verify/' . $user->id);
    }
}
