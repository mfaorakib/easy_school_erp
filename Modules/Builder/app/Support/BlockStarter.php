<?php

namespace Modules\Builder\Support;

/**
 * Starter content + default style for a freshly-added section, so a new block
 * looks good in the live preview immediately instead of rendering empty.
 */
class BlockStarter
{
    public static function data(string $type): array
    {
        return match ($type) {
            'hero' => [
                'layout' => 'center',
                'eyebrow' => 'Welcome',
                'headline' => 'A headline that sells your school',
                'subheadline' => 'Describe what makes your institution special in one or two short sentences.',
                'cta_label' => 'Get Started',
                'cta_url' => '#',
                'cta2_label' => 'Learn More',
                'cta2_url' => '#',
            ],
            'heading' => [
                'eyebrow' => 'Section',
                'heading' => 'A section heading',
                'subheading' => 'A short supporting line that introduces what follows.',
            ],
            'richtext' => [
                'heading' => 'About us',
                'body' => '<p>Write your story here. You can use <strong>bold</strong>, <em>italic</em> and lists to shape the content.</p>',
            ],
            'image_text' => [
                'image_side' => 'left',
                'eyebrow' => 'Why us',
                'heading' => 'Tell your story beside an image',
                'body' => '<p>Pair a striking image with a paragraph that explains a key benefit or value.</p>',
                'cta_label' => 'Read more',
                'cta_url' => '#',
            ],
            'features' => [
                'eyebrow' => 'Features',
                'heading' => 'Everything you need',
                'columns' => '3',
                'items' => [
                    ['icon' => '🎓', 'title' => 'Quality Education', 'text' => 'A curriculum built for real outcomes.'],
                    ['icon' => '👩‍🏫', 'title' => 'Expert Teachers', 'text' => 'Experienced, caring educators.'],
                    ['icon' => '🏆', 'title' => 'Proven Results', 'text' => 'A track record of achievement.'],
                ],
            ],
            'columns' => [
                'eyebrow' => 'Explore',
                'heading' => 'Split your content into columns',
                'columns' => '3',
                'items' => [
                    ['image' => '', 'heading' => 'Column One', 'text' => 'Describe the first item here — add an image and a link if you like.', 'link_label' => 'Learn more', 'link_url' => '#'],
                    ['image' => '', 'heading' => 'Column Two', 'text' => 'Describe the second item here.', 'link_label' => 'Learn more', 'link_url' => '#'],
                    ['image' => '', 'heading' => 'Column Three', 'text' => 'Describe the third item here.', 'link_label' => 'Learn more', 'link_url' => '#'],
                ],
            ],
            'services' => [
                'eyebrow' => 'What we offer',
                'heading' => 'Our services',
                'columns' => '3',
                'items' => [
                    ['icon' => '📚', 'title' => 'Academics', 'text' => 'Rich academic programs.', 'link_url' => '#'],
                    ['icon' => '🎨', 'title' => 'Arts', 'text' => 'Creative development.', 'link_url' => '#'],
                    ['icon' => '⚽', 'title' => 'Sports', 'text' => 'Healthy body, healthy mind.', 'link_url' => '#'],
                ],
            ],
            'stats' => [
                'heading' => 'By the numbers',
                'items' => [
                    ['value' => '2,400+', 'label' => 'Students'],
                    ['value' => '150+', 'label' => 'Teachers'],
                    ['value' => '98%', 'label' => 'Pass Rate'],
                    ['value' => '25', 'label' => 'Years'],
                ],
            ],
            'steps' => [
                'eyebrow' => 'How it works',
                'heading' => 'Three simple steps',
                'items' => [
                    ['title' => 'Apply', 'text' => 'Submit the online application.'],
                    ['title' => 'Interview', 'text' => 'Meet our admissions team.'],
                    ['title' => 'Enroll', 'text' => 'Reserve your seat.'],
                ],
            ],
            'testimonials' => [
                'eyebrow' => 'Testimonials',
                'heading' => 'What people say',
                'subheading' => 'Real words from our community.',
            ],
            'logos' => [
                'heading' => 'Trusted by',
                'items' => [
                    ['image' => '', 'url' => '#'],
                    ['image' => '', 'url' => '#'],
                    ['image' => '', 'url' => '#'],
                    ['image' => '', 'url' => '#'],
                ],
            ],
            'team' => [
                'eyebrow' => 'Our people',
                'heading' => 'Meet the team',
                'items' => [
                    ['photo' => '', 'name' => 'Jane Doe', 'role' => 'Principal', 'link_url' => '#'],
                    ['photo' => '', 'name' => 'John Smith', 'role' => 'Head of Academics', 'link_url' => '#'],
                    ['photo' => '', 'name' => 'Mary Roe', 'role' => 'Coordinator', 'link_url' => '#'],
                ],
            ],
            'pricing' => [
                'eyebrow' => 'Plans',
                'heading' => 'Simple pricing',
                'items' => [
                    ['name' => 'Basic', 'price' => '$20', 'period' => '/mo', 'features' => "Feature one\nFeature two\nFeature three", 'cta_label' => 'Choose', 'cta_url' => '#', 'featured' => false],
                    ['name' => 'Standard', 'price' => '$40', 'period' => '/mo', 'features' => "Everything in Basic\nMore features\nPriority support", 'cta_label' => 'Choose', 'cta_url' => '#', 'featured' => true],
                    ['name' => 'Premium', 'price' => '$80', 'period' => '/mo', 'features' => "Everything in Standard\nAll features\nDedicated support", 'cta_label' => 'Choose', 'cta_url' => '#', 'featured' => false],
                ],
            ],
            'faq' => [
                'eyebrow' => 'FAQ',
                'heading' => 'Frequently asked questions',
                'items' => [
                    ['question' => 'How do I apply?', 'answer' => 'Use the online application form on the admissions page.'],
                    ['question' => 'What are the fees?', 'answer' => 'Fees vary by grade; contact the office for details.'],
                ],
            ],
            'tabs' => [
                'heading' => 'Explore',
                'items' => [
                    ['title' => 'Overview', 'content' => '<p>An overview of this topic.</p>'],
                    ['title' => 'Details', 'content' => '<p>More detailed information here.</p>'],
                ],
            ],
            'gallery' => [
                'heading' => 'Gallery',
                'columns' => '3',
                'images' => [
                    ['image' => '', 'caption' => ''],
                    ['image' => '', 'caption' => ''],
                    ['image' => '', 'caption' => ''],
                ],
            ],
            'slider' => ['heading' => ''],
            'video' => [
                'eyebrow' => 'Watch',
                'heading' => 'See us in action',
                'subheading' => 'A short introduction video.',
                'video_url' => '',
            ],
            'cta' => [
                'headline' => 'Ready to get started?',
                'subtext' => 'Join our community today.',
                'button_label' => 'Apply Now',
                'button_url' => '#',
                'button2_label' => 'Contact Us',
                'button2_url' => '#',
            ],
            'banner' => [
                'text' => 'Admissions for the new session are now open!',
                'link_label' => 'Apply',
                'link_url' => '#',
            ],
            'buttons' => [
                'items' => [
                    ['label' => 'Primary', 'url' => '#', 'style' => 'primary'],
                    ['label' => 'Secondary', 'url' => '#', 'style' => 'ghost'],
                ],
            ],
            'contact' => [
                'eyebrow' => 'Contact',
                'heading' => 'Get in touch',
                'subheading' => 'Send us a message and we will get back to you.',
                'address' => '123 School Road, City',
                'phone' => '+1 234 567 890',
                'email' => 'info@school.test',
            ],
            'newsletter' => [
                'heading' => 'Stay in the loop',
                'subtext' => 'Subscribe for news and updates.',
                'button_label' => 'Subscribe',
            ],
            'map' => [
                'heading' => 'Find us',
                'embed_url' => '',
            ],
            'spacer' => ['height' => 'md', 'divider' => false],
            'html' => ['code' => '<!-- Your custom HTML here -->'],
            default => [],
        };
    }

    public static function settings(string $type): array
    {
        return match ($type) {
            'hero' => ['bg_type' => 'gradient', 'pad_y' => 'xl', 'align' => 'center', 'text_theme' => 'light'],
            'cta' => ['bg_type' => 'gradient', 'pad_y' => 'lg', 'align' => 'center', 'text_theme' => 'light'],
            'banner' => ['bg_type' => 'color', 'bg_color' => '#0f172a', 'pad_y' => 'sm', 'align' => 'center', 'text_theme' => 'light'],
            'stats', 'testimonials', 'logos', 'pricing', 'faq' => ['bg_type' => 'none', 'pad_y' => 'lg', 'soft' => true],
            'heading' => ['bg_type' => 'none', 'pad_y' => 'md', 'align' => 'center'],
            'features', 'services', 'columns', 'steps', 'team', 'gallery', 'video', 'tabs', 'contact', 'newsletter', 'map' => ['bg_type' => 'none', 'pad_y' => 'lg'],
            'spacer' => ['bg_type' => 'none', 'pad_y' => 'none'],
            default => ['bg_type' => 'none', 'pad_y' => 'lg'],
        };
    }
}
