<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = array(
            'name' => "Shubhansh Gupta",
            'email' => "shubhanshgupta731@gmail.com",
            'Username' => "Shubhansh18g",
            'password' => "Shubhansh@18",
            'mobile' => "9672121355",
            'address' => "Badwani Plaza, Indore",
            'is_vendor' => true
        );
        User::create($admin);
    }
}
