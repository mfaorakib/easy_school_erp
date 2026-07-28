<?php

namespace Modules\Builder\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Builder\Models\CmsMenu;
use Modules\Builder\Models\CmsPage;
use Modules\Builder\Models\SiteSetting;
use Modules\Builder\Models\Testimonial;

class BuilderDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- Branding / settings ---
        SiteSetting::current()->update([
            'site_name'       => 'EasySchool',
            'tagline'         => 'Nurturing curious minds for a brighter tomorrow.',
            'primary_color'   => '#4f46e5',
            'secondary_color' => '#0ea5e9',
            'phone'           => '+880 1700-000000',
            'email'           => 'hello@easyschool.test',
            'address'         => '12 Learning Avenue, Dhaka 1207',
            'facebook'        => 'https://facebook.com',
            'twitter'         => 'https://x.com',
            'youtube'         => 'https://youtube.com',
            'footer_text'     => 'A modern school committed to excellence in learning, character and community.',
        ]);

        $home = CmsPage::firstOrCreate(
            ['slug' => 'home'],
            ['title' => 'Home', 'is_home' => true, 'is_published' => true, 'meta_description' => 'Welcome to EasySchool'],
        );

        if ($home->blocks()->count() === 0) {
            $this->fill($home, [
                ['banner', [
                    'text' => 'Admissions for the 2026 session are now open!', 'link_label' => 'Apply now', 'link_url' => '/admission/apply',
                ], ['bg_type' => 'color', 'bg_color' => '#0f172a', 'pad_y' => 'sm', 'align' => 'center', 'text_theme' => 'light']],

                ['hero', [
                    'layout' => 'center', 'eyebrow' => 'Welcome to EasySchool',
                    'headline' => 'Where learning feels like belonging',
                    'subheadline' => 'A nurturing environment, dedicated teachers and a modern curriculum that prepares every student for the future.',
                    'cta_label' => 'Apply for Admission', 'cta_url' => '/admission/apply',
                    'cta2_label' => 'Take a Tour', 'cta2_url' => '/p/contact',
                ], ['bg_type' => 'gradient', 'pad_y' => 'xl', 'align' => 'center', 'text_theme' => 'light']],

                ['features', [
                    'eyebrow' => 'Why families choose us', 'heading' => 'An education built around every child', 'columns' => '3',
                    'items' => [
                        ['icon' => '📚', 'title' => 'Modern Curriculum', 'text' => 'A balanced, future-ready curriculum blending academics, arts and technology.'],
                        ['icon' => '👩‍🏫', 'title' => 'Expert Teachers', 'text' => 'Passionate, qualified educators who mentor every student.'],
                        ['icon' => '🔬', 'title' => 'Hands-on Labs', 'text' => 'Well-equipped science, computer and language labs.'],
                        ['icon' => '⚽', 'title' => 'Sports & Arts', 'text' => 'A vibrant co-curricular life that builds character.'],
                        ['icon' => '🌍', 'title' => 'Global Outlook', 'text' => 'Programs that broaden horizons and celebrate diversity.'],
                        ['icon' => '🛡️', 'title' => 'Safe Campus', 'text' => 'A secure, caring campus where every student thrives.'],
                    ],
                ], ['pad_y' => 'lg']],

                ['stats', [
                    'heading' => 'Trusted by our community',
                    'items' => [
                        ['value' => '2,400+', 'label' => 'Students'],
                        ['value' => '150+', 'label' => 'Teachers'],
                        ['value' => '98%', 'label' => 'Pass Rate'],
                        ['value' => '25', 'label' => 'Years of Excellence'],
                    ],
                ], ['soft' => true, 'pad_y' => 'lg']],

                ['steps', [
                    'eyebrow' => 'Admissions', 'heading' => 'Joining us is simple',
                    'items' => [
                        ['title' => 'Apply Online', 'text' => 'Complete the short application form.'],
                        ['title' => 'Visit Campus', 'text' => 'Meet our team and tour the facilities.'],
                        ['title' => 'Enroll', 'text' => 'Reserve your child’s seat for the new session.'],
                    ],
                ], ['pad_y' => 'lg']],

                ['testimonials', [
                    'eyebrow' => 'Testimonials', 'heading' => 'Loved by parents and students',
                ], ['soft' => true, 'pad_y' => 'lg']],

                ['faq', [
                    'eyebrow' => 'FAQ', 'heading' => 'Questions parents ask',
                    'items' => [
                        ['question' => 'What are the admission requirements?', 'answer' => 'A completed application, the child’s birth certificate and previous school records where applicable.'],
                        ['question' => 'Do you offer transport?', 'answer' => 'Yes — safe school transport covers all major areas of the city.'],
                        ['question' => 'What is the student-teacher ratio?', 'answer' => 'We keep classes small, with an average of 20 students per teacher.'],
                    ],
                ], ['pad_y' => 'lg']],

                ['cta', [
                    'headline' => 'Give your child a head start',
                    'subtext' => 'Admissions for the new academic year are now open. Book a campus tour today.',
                    'button_label' => 'Contact Admissions', 'button_url' => '/p/contact',
                ], ['bg_type' => 'gradient', 'pad_y' => 'lg', 'align' => 'center', 'text_theme' => 'light']],
            ]);
        }

        // --- About page ---
        $about = CmsPage::firstOrCreate(['slug' => 'about'], ['title' => 'About Us', 'is_published' => true]);
        if ($about->blocks()->count() === 0) {
            $this->fill($about, [
                ['hero', ['layout' => 'center', 'headline' => 'About EasySchool', 'subheadline' => 'Twenty-five years of shaping confident, compassionate learners.'],
                    ['bg_type' => 'gradient', 'pad_y' => 'xl', 'align' => 'center', 'text_theme' => 'light']],
                ['richtext', ['heading' => 'Our Story', 'body' => '<p>Founded on the belief that every child deserves an education that inspires, EasySchool has grown into a community of curious learners and dedicated teachers.</p><p>We combine a rigorous academic program with a rich co-curricular life, so students graduate not only knowledgeable, but confident, kind and ready for the world.</p>'],
                    ['width' => 'narrow', 'pad_y' => 'lg']],
                ['team', ['eyebrow' => 'Leadership', 'heading' => 'Meet our leadership', 'items' => [
                    ['name' => 'Dr. Rahim Uddin', 'role' => 'Principal'],
                    ['name' => 'Fatema Karim', 'role' => 'Head of Academics'],
                    ['name' => 'Sunil Islam', 'role' => 'Head of Student Life'],
                    ['name' => 'Ayesha Noor', 'role' => 'Admissions Lead'],
                ]], ['soft' => true, 'pad_y' => 'lg']],
            ]);
        }

        // --- Admissions page ---
        $admissions = CmsPage::firstOrCreate(['slug' => 'admissions'], ['title' => 'Admissions', 'is_published' => true]);
        if ($admissions->blocks()->count() === 0) {
            $this->fill($admissions, [
                ['hero', ['layout' => 'center', 'eyebrow' => 'Admissions Open', 'headline' => 'Begin your child’s journey', 'subheadline' => 'A few simple steps to join the EasySchool family.', 'cta_label' => 'Apply Now', 'cta_url' => '/admission/apply', 'cta2_label' => 'Check Application Status', 'cta2_url' => '/admission/status'],
                    ['bg_type' => 'gradient', 'pad_y' => 'xl', 'align' => 'center', 'text_theme' => 'light']],
                ['steps', ['heading' => 'How to apply', 'items' => [
                    ['title' => 'Enquire', 'text' => 'Reach out and tell us about your child.'],
                    ['title' => 'Assessment', 'text' => 'A friendly age-appropriate assessment.'],
                    ['title' => 'Enroll', 'text' => 'Complete enrolment and welcome aboard.'],
                ]], ['pad_y' => 'lg']],
                ['cta', ['headline' => 'Ready to apply?', 'subtext' => 'Our admissions team is here to help.', 'button_label' => 'Contact Admissions', 'button_url' => '/p/contact'],
                    ['bg_type' => 'gradient', 'align' => 'center', 'text_theme' => 'light', 'pad_y' => 'lg']],
            ]);
        }

        // --- Contact page ---
        $contact = CmsPage::firstOrCreate(['slug' => 'contact'], ['title' => 'Contact', 'is_published' => true]);
        if ($contact->blocks()->count() === 0) {
            $this->fill($contact, [
                ['contact', [
                    'eyebrow' => 'Contact', 'heading' => 'Get in touch', 'subheading' => 'We would love to hear from you — send us a message and we will respond shortly.',
                    'address' => '12 Learning Avenue, Dhaka 1207', 'phone' => '+880 1700-000000', 'email' => 'hello@easyschool.test',
                ], ['pad_y' => 'lg']],
            ]);
        }

        // --- Menus ---
        $header = CmsMenu::firstOrCreate(['location' => 'header'], ['title' => 'Main Menu']);
        if ($header->items()->count() === 0) {
            $header->items()->create(['label' => 'Home', 'page_id' => $home->id, 'position' => 1]);
            $header->items()->create(['label' => 'About', 'page_id' => $about->id, 'position' => 2]);
            $header->items()->create(['label' => 'Admissions', 'page_id' => $admissions->id, 'position' => 3]);
            $header->items()->create(['label' => 'Contact', 'page_id' => $contact->id, 'position' => 4]);
        }

        $footer = CmsMenu::firstOrCreate(['location' => 'footer'], ['title' => 'Footer Menu']);
        if ($footer->items()->count() === 0) {
            $footer->items()->create(['label' => 'Home', 'page_id' => $home->id, 'position' => 1]);
            $footer->items()->create(['label' => 'About Us', 'page_id' => $about->id, 'position' => 2]);
            $footer->items()->create(['label' => 'Admissions', 'page_id' => $admissions->id, 'position' => 3]);
            $footer->items()->create(['label' => 'Contact', 'page_id' => $contact->id, 'position' => 4]);
        }

        // --- Testimonials ---
        if (Testimonial::count() === 0) {
            $data = [
                ['name' => 'Nusrat Jahan', 'designation' => 'Parent', 'organization' => 'Grade 5', 'quote' => 'The teachers truly care. My daughter looks forward to school every single day.', 'rating' => 5],
                ['name' => 'Arif Hossain', 'designation' => 'Alumnus', 'organization' => 'Class of 2018', 'quote' => 'EasySchool gave me the confidence and foundation that shaped my university years.', 'rating' => 5],
                ['name' => 'Maria Gomes', 'designation' => 'Parent', 'organization' => 'Grade 2', 'quote' => 'A safe, warm and inspiring campus. We could not have chosen a better school.', 'rating' => 5],
            ];
            foreach ($data as $i => $t) {
                Testimonial::create($t + ['position' => $i + 1, 'is_active' => true]);
            }
        }
    }

    /** Create ordered blocks: each entry is [type, data, settings]. */
    private function fill(CmsPage $page, array $blocks): void
    {
        foreach ($blocks as $i => [$type, $data, $settings]) {
            $page->blocks()->create([
                'type'       => $type,
                'position'   => $i + 1,
                'data'       => $data,
                'settings'   => $settings,
                'is_visible' => true,
            ]);
        }
    }
}
