<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            MediaSeeder::class,
        ]);

        $primaryUser = User::updateOrCreate(
            ['email' => 'pengguna@flipplay.test'],
            [
                'name' => 'Pengguna FlipPlay',
                'password' => 'password',
            ]
        );

        User::updateOrCreate(
            ['email' => 'demo@flipplay.test'],
            [
                'name' => 'Demo User',
                'password' => 'password',
            ]
        );

        // Seed initial collection matching Modul 3 (media 1 & 3)
        $mediaIds = Media::whereIn('title', ['Jujutsu Kaisen', 'Blue Lock'])->pluck('id')->all();
        if (! empty($mediaIds)) {
            $primaryUser->media()->syncWithoutDetaching($mediaIds);
        }
    }
}
