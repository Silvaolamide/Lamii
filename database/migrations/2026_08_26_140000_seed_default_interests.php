<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $interests = [
            'Technology', 'Business', 'AI', 'Music', 'Movies',
            'Photography', 'Sports', 'Gaming', 'Fashion', 'Travel',
            'Fitness', 'Food', 'Art', 'Books', 'Networking',
        ];

        $now = now();

        foreach ($interests as $name) {
            DB::table('interests')->updateOrInsert(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'slug' => Str::slug($name), 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    public function down(): void
    {
        DB::table('interests')
            ->whereIn('slug', [
                'technology', 'business', 'ai', 'music', 'movies',
                'photography', 'sports', 'gaming', 'fashion', 'travel',
                'fitness', 'food', 'art', 'books', 'networking',
            ])
            ->delete();
    }
};
