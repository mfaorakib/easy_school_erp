<?php

namespace Modules\Library\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Library\Models\Book;
use Modules\Library\Models\BookCategory;

class LibraryDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $general = BookCategory::firstOrCreate(['name' => 'General']);
        BookCategory::firstOrCreate(['name' => 'Science']);

        $books = [
            ['title' => 'Introduction to Algebra', 'author' => 'H. Rahman', 'quantity' => 5],
            ['title' => 'World History', 'author' => 'A. Karim', 'quantity' => 3],
        ];

        foreach ($books as $b) {
            Book::firstOrCreate(
                ['title' => $b['title']],
                ['author' => $b['author'], 'book_category_id' => $general->id, 'quantity' => $b['quantity'], 'available' => $b['quantity']],
            );
        }
    }
}
