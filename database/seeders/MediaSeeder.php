<?php

namespace Database\Seeders;

use App\Models\Media;
use Illuminate\Database\Seeder;

class MediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'title' => 'Jujutsu Kaisen',
                'type' => 'Anime',
                'genre' => 'Action, Supernatural',
                'description' => 'Anime action dan supernatural.',
                'episodes' => 24,
                'format' => null,
            ],
            [
                'title' => 'Your Name',
                'type' => 'Anime',
                'genre' => 'Drama, Romance',
                'description' => 'Anime drama dan romance.',
                'episodes' => 1,
                'format' => null,
            ],
            [
                'title' => 'Blue Lock',
                'type' => 'Manga',
                'genre' => 'Sports, Psychological',
                'description' => 'Manga bertema sepak bola.',
                'episodes' => null,
                'format' => 'Digital',
            ],
            [
                'title' => 'The Beginning After The End',
                'type' => 'Light Novel',
                'genre' => 'Fantasy, Adventure',
                'description' => 'Novel fantasi petualangan.',
                'episodes' => null,
                'format' => 'Digital',
            ],
        ];

        foreach ($items as $item) {
            Media::updateOrCreate(['title' => $item['title']], $item);
        }
    }
}
