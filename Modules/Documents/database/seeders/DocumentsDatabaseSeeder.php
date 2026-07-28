<?php

namespace Modules\Documents\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Documents\Models\CertificateTemplate;
use Modules\Documents\Models\IdCardTemplate;

class DocumentsDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        IdCardTemplate::firstOrCreate(
            ['name' => 'Student Card (Default)'],
            [
                'holder_type'      => 'student',
                'background_color' => '#4f46e5',
                'text_color'       => '#ffffff',
                'fields'           => ['roll', 'blood_group', 'phone', 'session'],
                'footer_text'      => 'If found, please return to the school office.',
                'signature_label'  => 'Principal',
                'orientation'      => 'portrait',
            ],
        );

        IdCardTemplate::firstOrCreate(
            ['name' => 'Staff Card (Default)'],
            [
                'holder_type'      => 'staff',
                'background_color' => '#0ea5e9',
                'text_color'       => '#ffffff',
                'fields'           => ['phone', 'department'],
                'signature_label'  => 'Principal',
                'orientation'      => 'portrait',
            ],
        );

        CertificateTemplate::firstOrCreate(
            ['name' => 'Character Certificate'],
            [
                'holder_type'           => 'student',
                'heading'               => 'Certificate of Character',
                'orientation'           => 'landscape',
                'signature_left_label'  => 'Class Teacher',
                'signature_right_label' => 'Principal',
                'body' => 'This is to certify that <span class="holder">{{name}}</span>, '
                    .'Roll No. {{roll}} of Class {{class}} ({{section}}), a student of {{school_name}} '
                    .'during the session {{session}}, bears a good moral character to the best of our knowledge.'
                    .'<br><br>We wish every success in life.',
            ],
        );

        CertificateTemplate::firstOrCreate(
            ['name' => 'Transfer Certificate'],
            [
                'holder_type'           => 'student',
                'document_prefix'       => 'TC',
                'heading'               => 'Transfer Certificate',
                'orientation'           => 'landscape',
                'signature_left_label'  => 'Class Teacher',
                'signature_right_label' => 'Principal',
                'body' => 'TC No: <strong>{{document_no}}</strong><br><br>'
                    .'This is to certify that <span class="holder">{{name}}</span>, '
                    .'Admission No. {{admission_no}}, Roll No. {{roll}} of Class {{class}} ({{section}}), '
                    .'was a bona fide student of {{school_name}} during the session {{session}}.'
                    .'<br><br>This Transfer Certificate is issued on {{date}} at the request of the guardian.',
            ],
        );

        CertificateTemplate::firstOrCreate(
            ['name' => 'Experience Certificate'],
            [
                'holder_type'           => 'staff',
                'document_prefix'       => 'EXP',
                'heading'               => 'Certificate of Experience',
                'orientation'           => 'landscape',
                'signature_left_label'  => 'HR / Admin',
                'signature_right_label' => 'Principal',
                'body' => 'Ref No: <strong>{{document_no}}</strong><br><br>'
                    .'This is to certify that <span class="holder">{{name}}</span> '
                    .'(Staff ID: {{staff_no}}) served {{school_name}} as {{designation}} '
                    .'from {{joining_date}} to {{leaving_date}}, a period of {{duration}}.'
                    .'<br><br>During this time, we found them sincere, hardworking, and dedicated to their duties. '
                    .'We wish them success in all future endeavors.',
            ],
        );

        CertificateTemplate::firstOrCreate(
            ['name' => 'Appointment Letter'],
            [
                'holder_type'           => 'staff',
                'document_prefix'       => 'APT',
                'heading'               => 'Letter of Appointment',
                'orientation'           => 'portrait',
                'signature_left_label'  => 'HR / Admin',
                'signature_right_label' => 'Principal',
                'body' => 'Ref No: <strong>{{document_no}}</strong><br><br>'
                    .'Dear <span class="holder">{{name}}</span>,<br><br>'
                    .'We are pleased to appoint you as <strong>{{designation}}</strong> at {{school_name}}, '
                    .'effective from <strong>{{joining_date}}</strong>. Your Staff ID is {{staff_no}}.'
                    .'<br><br>We look forward to a successful association and wish you the very best in your new role.'
                    .'<br><br>Date of Issue: {{date}}',
            ],
        );
    }
}
