<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class LoginAdminTest extends TestCase
{
    use RefreshDatabase;

    public function testAdminEmailEmpty(){
        User::create([
            'name'=>'test',
            'email'=>'test@example.com',
            'password'=>bcrypt('password123')
        ]);

        $response=$this->get('/admin/login');
        $response->assertStatus(200);

        $response=$this->post('/admin/login', [
            'email'=>'',
            'password'=>'password123',
            '_token' => csrf_token(),
        ]);
        $response->assertSessionHasErrors(['email'=>'メールアドレスを入力してください']);
    }

    public function testAdminPasswordEmpty(){
        User::create([
            'name'=>'test',
            'email'=>'test@example.com',
            'password'=>bcrypt('password123')
        ]);

        $response=$this->get('/admin/login');
        $response->assertStatus(200);

        $response=$this->post('/admin/login', [
            'email'=>'test@example.com',
            'password'=>'',
            '_token' => csrf_token(),
        ]);
        $response->assertSessionHasErrors(['password'=>'パスワードを入力してください']);
    }

    public function testAdminLoginDifferent(){
        User::create([
            'name'=>'test',
            'email'=>'test@example.com',
            'password'=>bcrypt('password123'),
            '_token' => csrf_token(),
        ]);

        $response=$this->get('/admin/login');
        $response->assertStatus(200);

        $response=$this->post('/admin/login', [
            'email'=>'testtest@example.com',
            'password'=>'password123',
            '_token' => csrf_token(),
        ]);
        $response->assertSessionHasErrors(['email'=>'ログイン情報が登録されていません']);
    }
}
