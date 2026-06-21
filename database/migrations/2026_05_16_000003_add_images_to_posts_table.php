<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->json('images')->nullable()->after('image');
        });

        DB::table('posts')
            ->whereNotNull('image')
            ->orderBy('id')
            ->chunkById(100, function ($posts) {
                foreach ($posts as $post) {
                    DB::table('posts')
                        ->where('id', $post->id)
                        ->update(['images' => json_encode([$post->image])]);
                }
            });
    }

    public function down(): void
    {
        DB::table('posts')
            ->whereNotNull('images')
            ->orderBy('id')
            ->chunkById(100, function ($posts) {
                foreach ($posts as $post) {
                    $images = json_decode($post->images, true);
                    DB::table('posts')
                        ->where('id', $post->id)
                        ->update(['image' => is_array($images) ? ($images[0] ?? null) : null]);
                }
            });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('images');
        });
    }
};
