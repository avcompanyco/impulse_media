<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Obtener los IDs de las categorías Movies y Series
        $moviesCategory = DB::table('categories')->where('name', 'Movies')->first();
        $seriesCategory = DB::table('categories')->where('name', 'Series')->first();

        $genres = [
            'Sci-Fi',
            'Adventure',
            'Comedy',
            'Drama',
            'Fantasy',
            'Horror',
            'Mystery',
            'Romance',
            'Thriller',
            'War',
            'Western',
            'Animation',
            'Biography',
            'Documentary',
            'Family',
            'History',
            'Music',
            'News',
            'Reality-TV',
        ];

        if ($moviesCategory) {
            foreach ($genres as $genre) {
                // Verificar si ya existe para evitar duplicados
                $exists = DB::table('subcategories')
                    ->where('category_id', $moviesCategory->id)
                    ->where('name', $genre)
                    ->exists();

                if (!$exists) {
                    DB::table('subcategories')->insert([
                        'name' => $genre,
                        'category_id' => $moviesCategory->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        if ($seriesCategory) {
            foreach ($genres as $genre) {
                // Verificar si ya existe para evitar duplicados
                $exists = DB::table('subcategories')
                    ->where('category_id', $seriesCategory->id)
                    ->where('name', $genre)
                    ->exists();

                if (!$exists) {
                    DB::table('subcategories')->insert([
                        'name' => $genre,
                        'category_id' => $seriesCategory->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $moviesCategory = DB::table('categories')->where('name', 'Movies')->first();
        $seriesCategory = DB::table('categories')->where('name', 'Series')->first();

        $categoryIds = [];
        if ($moviesCategory) {
            $categoryIds[] = $moviesCategory->id;
        }
        if ($seriesCategory) {
            $categoryIds[] = $seriesCategory->id;
        }

        if (!empty($categoryIds)) {
            DB::table('subcategories')->whereIn('category_id', $categoryIds)->delete();
        }
    }
};
