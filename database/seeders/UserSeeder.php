<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
	public function run(): void
	{
		User::create([
			'name' => 'Akmal Rahim',
			'email' => 'akmalrahim376@gmail.com',
			'email_verified_at' => now(),
			'password' => Hash::make('password'),
			'roles' => [UserRole::Admin->value],
			'is_active' => true,
		]);
	}
}
