<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AcademicCore\Database\Seeders\AcademicCoreDatabaseSeeder;
use Modules\Access\Database\Seeders\AccessDatabaseSeeder;
use Modules\Admission\Database\Seeders\AdmissionDatabaseSeeder;
use Modules\GuardianPortal\Database\Seeders\GuardianPortalDatabaseSeeder;
use Modules\Accounting\Database\Seeders\AccountingDatabaseSeeder;
use Modules\Builder\Database\Seeders\BuilderDatabaseSeeder;
use Modules\Chat\Database\Seeders\ChatDatabaseSeeder;
use Modules\Foundation\Database\Seeders\FoundationDatabaseSeeder;
use Modules\FrontOffice\Database\Seeders\FrontOfficeDatabaseSeeder;
use Modules\Examination\Database\Seeders\ExaminationDatabaseSeeder;
use Modules\Fees\Database\Seeders\FeesDatabaseSeeder;
use Modules\HumanResource\Database\Seeders\HumanResourceDatabaseSeeder;
use Modules\Behaviour\Database\Seeders\BehaviourDatabaseSeeder;
use Modules\Communication\Database\Seeders\CommunicationDatabaseSeeder;
use Modules\Documents\Database\Seeders\DocumentsDatabaseSeeder;
use Modules\Dormitory\Database\Seeders\DormitoryDatabaseSeeder;
use Modules\DownloadCenter\Database\Seeders\DownloadCenterDatabaseSeeder;
use Modules\Homework\Database\Seeders\HomeworkDatabaseSeeder;
use Modules\Inventory\Database\Seeders\InventoryDatabaseSeeder;
use Modules\Lesson\Database\Seeders\LessonDatabaseSeeder;
use Modules\Leave\Database\Seeders\LeaveDatabaseSeeder;
use Modules\Library\Database\Seeders\LibraryDatabaseSeeder;
use Modules\OnlineExam\Database\Seeders\OnlineExamDatabaseSeeder;
use Modules\Payroll\Database\Seeders\PayrollDatabaseSeeder;
use Modules\Settings\Database\Seeders\SettingsDatabaseSeeder;
use Modules\Timetable\Database\Seeders\TimetableDatabaseSeeder;
use Modules\Transport\Database\Seeders\TransportDatabaseSeeder;
use Modules\Wallet\Database\Seeders\WalletDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            FoundationDatabaseSeeder::class,      // academic year, lookups, settings, flags
            AccessDatabaseSeeder::class,          // roles + super admin
            HumanResourceDatabaseSeeder::class,   // demo teachers (staff)
            AcademicCoreDatabaseSeeder::class,    // starter classes/sections/subjects
            FeesDatabaseSeeder::class,            // starter fee group/type/master/discount
            ExaminationDatabaseSeeder::class,     // grade scale + starter exam
            LibraryDatabaseSeeder::class,         // book categories + starter books
            InventoryDatabaseSeeder::class,       // item categories/stores/suppliers/items
            TransportDatabaseSeeder::class,       // routes + vehicles + a route-vehicle link
            CommunicationDatabaseSeeder::class,   // demo notices + event
            HomeworkDatabaseSeeder::class,        // a demo homework
            DormitoryDatabaseSeeder::class,       // hostels + room types + rooms
            BehaviourDatabaseSeeder::class,       // behaviour types (+/- points)
            WalletDatabaseSeeder::class,          // admin wallet + opening balance
            LessonDatabaseSeeder::class,          // a lesson with topics
            DownloadCenterDatabaseSeeder::class,  // content types + a shared document
            OnlineExamDatabaseSeeder::class,      // question bank + a published online exam
            ChatDatabaseSeeder::class,            // a direct exchange + a demo group
            BuilderDatabaseSeeder::class,         // public site: home page + blocks + menus + testimonials
            AccountingDatabaseSeeder::class,      // account heads + bank accounts + demo income/expense
            PayrollDatabaseSeeder::class,         // a salary template + assignment + a demo payslip
            TimetableDatabaseSeeder::class,       // class periods + rooms + a starter routine
            FrontOfficeDatabaseSeeder::class,     // enquiry + visitor + postal + complaint + call log
            DocumentsDatabaseSeeder::class,       // default ID card + certificate templates
            SettingsDatabaseSeeder::class,        // default settings + payment methods + a holiday
            LeaveDatabaseSeeder::class,           // leave types + shifts + a demo application
            AdmissionDatabaseSeeder::class,        // id-format setting + demo pending applications
            GuardianPortalDatabaseSeeder::class,   // confirms a demo admission + fee assignment/discount/payment for the portal
        ]);
    }
}
