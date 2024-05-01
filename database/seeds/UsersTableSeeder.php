<?php

namespace Database\Seeders;

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    private function getUsers()
    {
        return [
            [
                'email' => 'kuen.do@praetoriusbelykh.de',
                'name' => 'kuendo',
                'password' =>  bcrypt('FvB86MmNsmwmPzuDqNxNp7ZHSR2MmLsu'),
//                'api_token' => hash('sha256', Str::random(60)),
            ],
            [
                'email' => 'mohamad.denno@dotkomm.de',
                'name' => 'mohamaddeno',
                'password' =>  bcrypt('su8DGfd8umTunwhevLcLB6p72ZxkEJjt'),
//                'api_token' => hash('sha256', Str::random(60)),
            ],
            [
                'email' => 'stefan.radermacher@dotkomm.de',
                'name' => 'stefanradermacher',
                'password' =>  bcrypt('7bMU5nasPgvjWE28M6k8XenFeuyLn9dp'),
//                'api_token' => hash('sha256', Str::random(60)),
            ],
            [
                'email' => 'dustin@gas.com',
                'name' => 'dustinlichey',
                'password' =>  bcrypt('9bDK8nasPevjWE28M6k4XanFeugLn9vp'),
//                'api_token' => hash('sha256', Str::random(60)),
            ],
        ];
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->getUsers() as $userData) {
            if (! User::where('email', $userData['email'])->first()) {
//                $token = Str::random(60);
//                $userData['api_token'] = hash('sha256', $token);
//                $userData['api_key'] = $token;
                User::create($userData);
            }
        }
    }
}
