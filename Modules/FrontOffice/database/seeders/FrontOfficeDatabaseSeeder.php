<?php

namespace Modules\FrontOffice\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\FrontOffice\Models\AdmissionEnquiry;
use Modules\FrontOffice\Models\Complaint;
use Modules\FrontOffice\Models\ComplaintType;
use Modules\FrontOffice\Models\PhoneCallLog;
use Modules\FrontOffice\Models\PostalRecord;
use Modules\FrontOffice\Models\Visitor;

class FrontOfficeDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Academic', 'Facilities', 'Discipline', 'Other'] as $name) {
            ComplaintType::firstOrCreate(['name' => $name]);
        }

        AdmissionEnquiry::firstOrCreate(
            ['name' => 'Kamal Hasan', 'phone' => '01711000000'],
            ['email' => 'kamal@example.com', 'source' => 'Walk-in', 'no_of_child' => 1,
             'enquiry_date' => now()->toDateString(), 'next_follow_up_date' => now()->addDays(3)->toDateString(),
             'status' => 'active', 'description' => 'Enquiry for Class 1 admission.'],
        );

        Visitor::firstOrCreate(
            ['name' => 'Rahima Begum', 'visit_date' => now()->toDateString()],
            ['phone' => '01722000000', 'purpose' => 'Meet class teacher', 'to_meet' => 'Class 1 Teacher',
             'no_of_person' => 1, 'in_time' => '10:15'],
        );

        PostalRecord::firstOrCreate(
            ['title' => 'Board circular 2026', 'type' => PostalRecord::TYPE_RECEIVE],
            ['party' => 'Education Board', 'reference_no' => 'BRD-2026-14', 'postal_date' => now()->toDateString()],
        );

        Complaint::firstOrCreate(
            ['complainant_name' => 'Sabbir Ahmed', 'complaint_date' => now()->toDateString()],
            ['complaint_type_id' => ComplaintType::where('name', 'Facilities')->value('id'),
             'phone' => '01733000000', 'source' => 'Phone', 'description' => 'Classroom fan not working.',
             'status' => 'open'],
        );

        PhoneCallLog::firstOrCreate(
            ['name' => 'Nadia Islam', 'phone' => '01744000000', 'call_date' => now()->toDateString()],
            ['call_type' => 'incoming', 'description' => 'Asked about exam schedule.', 'call_duration' => '5 min'],
        );
    }
}
