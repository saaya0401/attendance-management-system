<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function testEmailEmpty(){
        User::create([
            'name'=>'test',
            'email'=>'test@example.com',
            'password'=>bcrypt('password123')
        ]);

        $response=$this->get('/login');
        $response->assertStatus(200);

        $response=$this->post('/login', [
            'email'=>'',
            'password'=>'password123',
            '_token' => csrf_token(),
        ]);
        $response->assertSessionHasErrors(['email'=>'メールアドレスを入力してください']);
    }

    public function testPasswordEmpty(){
        User::create([
            'name'=>'test',
            'email'=>'test@example.com',
            'password'=>bcrypt('password123')
        ]);

        $response=$this->get('/login');
        $response->assertStatus(200);

        $response=$this->post('/login', [
            'email'=>'test@example.com',
            'password'=>'',
            '_token' => csrf_token(),
        ]);
        $response->assertSessionHasErrors(['password'=>'パスワードを入力してください']);
    }

    public function testLoginDifferent(){
        User::create([
            'name'=>'test',
            'email'=>'test@example.com',
            'password'=>bcrypt('password123')
        ]);

        $response=$this->get('/login');
        $response->assertStatus(200);

        $response=$this->post('/login', [
            'email'=>'testtest@example.com',
            'password'=>'password123',
            '_token' => csrf_token(),
        ]);
        $response->assertSessionHasErrors(['email'=>'ログイン情報が登録されていません']);
    }
}
