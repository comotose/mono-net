<?php

use App\Models\Post;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('reactable');
            $table->string('kind', 32);
            $table->timestamps();

            $table->unique(['user_id', 'reactable_type', 'reactable_id'], 'reactions_unique_user_target');
        });

        if (! Schema::hasTable('likes')) {
            return;
        }

        DB::table('likes')
            ->orderBy('id')
            ->chunkById(100, function ($likes): void {
                $payload = [];

                foreach ($likes as $like) {
                    $payload[] = [
                        'user_id' => $like->user_id,
                        'reactable_type' => Post::class,
                        'reactable_id' => $like->post_id,
                        'kind' => 'heart',
                        'created_at' => $like->created_at,
                        'updated_at' => $like->updated_at,
                    ];
                }

                if ($payload !== []) {
                    DB::table('reactions')->insert($payload);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};
