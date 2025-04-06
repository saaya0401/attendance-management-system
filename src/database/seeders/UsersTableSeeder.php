<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users=[
            [
                'name'=>'admin',
                'email'=>'admin@example.com',
                'password'=>'adminadmin',
                'role'=>'admin'
            ],
            [
                'name'=>'saaya',
                'email'=>'saaya@example.com',
                'email_verified_at'=>Carbon::now(),
                'password'=>'saayakoba',
                'role'=>'staff'
            ],
            [
                'name'=>'koharu',
                'email'=>'koharu@example.com',
                'email_verified_at'=>Carbon::now(),
                'password'=>'koharukoba',
                'role'=>'staff'
            ],
            [
                'name'=>'hiyori',
                'email'=>'hiyori@example.com',
                'email_verified_at'=>Carbon::now(),
                'password'=>'hiyorikoba',
                'role'=>'staff'
            ],
            [
                'name'=>'yasu',
                'email'=>'yasu@example.com',
                'email_verified_at'=>Carbon::now(),
                'password'=>'yasukoba',
                'role'=>'staff'
            ],
        ];
        foreach($users as $user){
            User::create($user);
        }
    }
}
