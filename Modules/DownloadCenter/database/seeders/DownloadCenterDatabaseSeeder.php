<?php

namespace Modules\DownloadCenter\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\DownloadCenter\Models\ContentType;
use Modules\DownloadCenter\Models\DownloadContent;

class DownloadCenterDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $types = [];
        foreach (['Assignment', 'Syllabus', 'Study Material', 'Other'] as $name) {
            $types[$name] = ContentType::firstOrCreate(['name' => $name]);
        }

        DownloadContent::firstOrCreate(
            ['title' => 'Class 1 Syllabus 2026'],
            [
                'content_type_id' => $types['Syllabus']->id,
                'description'     => 'Full-year syllabus for Class 1.',
                'audiences'       => ['student', 'parent'],
                'external_url'    => 'https://example.com/syllabus.pdf',
                'publish_date'    => now()->toDateString(),
                'is_published'    => true,
            ],
        );
    }
}
