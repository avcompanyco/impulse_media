<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Category;
use App\Models\Subcategory;


class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sci-Fi Category
        $category = Category::create([
            'name' => 'Sci-Fi',
            'image' => '',
        ]);

        $subcategories = [
            'Space Opera',
            'Cyberpunk',
            'Dystopian',
            'Time Travel',
            'Alien Invasion',
        ];

        foreach ($subcategories as $subcategory) {
            Subcategory::create([
                'name' => $subcategory,
                'category_id' => $category->id,
            ]);
        }

        // Adventure Category
        $category = Category::create([
            'name' => 'Adventure',
            'image' => '',
        ]);

        $subcategories = [
            'Treasure Hunt',
            'Survival',
            'Exploration',
            'Quest',
            'Jungle Adventure',
        ];

        foreach ($subcategories as $subcategory) {
            Subcategory::create([
                'name' => $subcategory,
                'category_id' => $category->id,
            ]);
        }

        // Comedy Category
        $category = Category::create([
            'name' => 'Comedy',
            'image' => '',
        ]);

        $subcategories = [
            'Romantic Comedy',
            'Dark Comedy',
            'Slapstick',
            'Parody',
            'Sitcom',
        ];

        foreach ($subcategories as $subcategory) {
            Subcategory::create([
                'name' => $subcategory,
                'category_id' => $category->id,
            ]);
        }

        // Drama Category
        $category = Category::create([
            'name' => 'Drama',
            'image' => '',
        ]);

        $subcategories = [
            'Family Drama',
            'Legal Drama',
            'Medical Drama',
            'Crime Drama',
            'Historical Drama',
        ];

        foreach ($subcategories as $subcategory) {
            Subcategory::create([
                'name' => $subcategory,
                'category_id' => $category->id,
            ]);
        }

        // Fantasy Category
        $category = Category::create([
            'name' => 'Fantasy',
            'image' => '',
        ]);

        $subcategories = [
            'High Fantasy',
            'Urban Fantasy',
            'Dark Fantasy',
            'Sword and Sorcery',
            'Mythology',
        ];

        foreach ($subcategories as $subcategory) {
            Subcategory::create([
                'name' => $subcategory,
                'category_id' => $category->id,
            ]);
        }

        // Horror Category
        $category = Category::create([
            'name' => 'Horror',
            'image' => '',
        ]);

        $subcategories = [
            'Psychological Horror',
            'Supernatural Horror',
            'Slasher',
            'Zombie',
            'Gothic Horror',
        ];

        foreach ($subcategories as $subcategory) {
            Subcategory::create([
                'name' => $subcategory,
                'category_id' => $category->id,
            ]);
        }

        // Mystery Category
        $category = Category::create([
            'name' => 'Mystery',
            'image' => '',
        ]);

        $subcategories = [
            'Detective',
            'Whodunit',
            'Police Procedural',
            'Cozy Mystery',
            'Noir',
        ];

        foreach ($subcategories as $subcategory) {
            Subcategory::create([
                'name' => $subcategory,
                'category_id' => $category->id,
            ]);
        }

        // Romance Category
        $category = Category::create([
            'name' => 'Romance',
            'image' => '',
        ]);

        $subcategories = [
            'Contemporary Romance',
            'Historical Romance',
            'Paranormal Romance',
            'Teen Romance',
            'Workplace Romance',
        ];

        foreach ($subcategories as $subcategory) {
            Subcategory::create([
                'name' => $subcategory,
                'category_id' => $category->id,
            ]);
        }

        // Thriller Category
        $category = Category::create([
            'name' => 'Thriller',
            'image' => '',
        ]);

        $subcategories = [
            'Spy Thriller',
            'Political Thriller',
            'Techno Thriller',
            'Psychological Thriller',
            'Action Thriller',
        ];

        foreach ($subcategories as $subcategory) {
            Subcategory::create([
                'name' => $subcategory,
                'category_id' => $category->id,
            ]);
        }

        // War Category
        $category = Category::create([
            'name' => 'War',
            'image' => '',
        ]);

        $subcategories = [
            'World War II',
            'Vietnam War',
            'Civil War',
            'Modern Warfare',
            'Anti-War',
        ];

        foreach ($subcategories as $subcategory) {
            Subcategory::create([
                'name' => $subcategory,
                'category_id' => $category->id,
            ]);
        }

        // Western Category
        $category = Category::create([
            'name' => 'Western',
            'image' => '',
        ]);

        $subcategories = [
            'Classic Western',
            'Spaghetti Western',
            'Revisionist Western',
            'Modern Western',
            'Comedy Western',
        ];

        foreach ($subcategories as $subcategory) {
            Subcategory::create([
                'name' => $subcategory,
                'category_id' => $category->id,
            ]);
        }

        // Animation Category
        $category = Category::create([
            'name' => 'Animation',
            'image' => '',
        ]);

        $subcategories = [
            '2D Animation',
            '3D Animation',
            'Stop Motion',
            'Anime',
            'Computer Animation',
        ];

        foreach ($subcategories as $subcategory) {
            Subcategory::create([
                'name' => $subcategory,
                'category_id' => $category->id,
            ]);
        }

        // Biography Category
        $category = Category::create([
            'name' => 'Biography',
            'image' => '',
        ]);

        $subcategories = [
            'Historical Figures',
            'Musicians',
            'Athletes',
            'Politicians',
            'Artists',
        ];

        foreach ($subcategories as $subcategory) {
            Subcategory::create([
                'name' => $subcategory,
                'category_id' => $category->id,
            ]);
        }

        // Documentary Category
        $category = Category::create([
            'name' => 'Documentary',
            'image' => '',
        ]);

        $subcategories = [
            'Nature Documentary',
            'Historical Documentary',
            'True Crime',
            'Science Documentary',
            'Social Issues',
        ];

        foreach ($subcategories as $subcategory) {
            Subcategory::create([
                'name' => $subcategory,
                'category_id' => $category->id,
            ]);
        }

        // Family Category
        $category = Category::create([
            'name' => 'Family',
            'image' => '',
        ]);

        $subcategories = [
            'Family Adventure',
            'Family Comedy',
            'Coming of Age',
            'Children\'s Movies',
            'Holiday Movies',
        ];

        foreach ($subcategories as $subcategory) {
            Subcategory::create([
                'name' => $subcategory,
                'category_id' => $category->id,
            ]);
        }

        // History Category
        $category = Category::create([
            'name' => 'History',
            'image' => '',
        ]);

        $subcategories = [
            'Ancient History',
            'Medieval History',
            'Renaissance',
            'Industrial Revolution',
            'Modern History',
        ];

        foreach ($subcategories as $subcategory) {
            Subcategory::create([
                'name' => $subcategory,
                'category_id' => $category->id,
            ]);
        }

        // Music Category
        $category = Category::create([
            'name' => 'Music',
            'image' => '',
        ]);

        $subcategories = [
            'Musical',
            'Concert Film',
            'Music Documentary',
            'Music Biography',
            'Dance Film',
        ];

        foreach ($subcategories as $subcategory) {
            Subcategory::create([
                'name' => $subcategory,
                'category_id' => $category->id,
            ]);
        }

        // News Category
        $category = Category::create([
            'name' => 'News',
            'image' => '',
        ]);

        $subcategories = [
            'Breaking News',
            'Political News',
            'Sports News',
            'Entertainment News',
            'International News',
        ];

        foreach ($subcategories as $subcategory) {
            Subcategory::create([
                'name' => $subcategory,
                'category_id' => $category->id,
            ]);
        }

        // Reality-TV Category
        $category = Category::create([
            'name' => 'Reality-TV',
            'image' => '',
        ]);

        $subcategories = [
            'Competition Reality',
            'Dating Shows',
            'Lifestyle Reality',
            'Talent Shows',
            'Survival Reality',
        ];

        foreach ($subcategories as $subcategory) {
            Subcategory::create([
                'name' => $subcategory,
                'category_id' => $category->id,
            ]);
        }
    }
}
