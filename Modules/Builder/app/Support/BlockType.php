<?php

namespace Modules\Builder\Support;

/**
 * The section-type registry — the single source of truth shared by the admin
 * builder (renders a settings form per type) and the public renderer (reads the
 * same field keys). Each type declares editable `fields`; a `repeater` field
 * holds a list of rows described by `subfields`. `group` buckets the type in the
 * "add section" picker.
 *
 * Field types: text · textarea · richtext · image · url · icon · number ·
 * select (with `options`) · toggle · repeater (with `subfields`).
 */
class BlockType
{
    /** @return array<string, array{label:string, icon:string, group:string, fields:array}> */
    public static function all(): array
    {
        return [
            // ---------------------------------------------------------- Basic
            'hero' => [
                'label' => 'Hero', 'icon' => '🎯', 'group' => 'Basic',
                'fields' => [
                    ['key' => 'layout', 'label' => 'Layout', 'type' => 'select', 'options' => ['center' => 'Centered', 'left' => 'Text left + image']],
                    ['key' => 'eyebrow', 'label' => 'Eyebrow', 'type' => 'text'],
                    ['key' => 'headline', 'label' => 'Headline', 'type' => 'text'],
                    ['key' => 'subheadline', 'label' => 'Sub-headline', 'type' => 'textarea'],
                    ['key' => 'image', 'label' => 'Side image (for "text left" layout)', 'type' => 'image'],
                    ['key' => 'cta_label', 'label' => 'Primary button label', 'type' => 'text'],
                    ['key' => 'cta_url', 'label' => 'Primary button URL', 'type' => 'url'],
                    ['key' => 'cta2_label', 'label' => 'Secondary button label', 'type' => 'text'],
                    ['key' => 'cta2_url', 'label' => 'Secondary button URL', 'type' => 'url'],
                ],
            ],
            'heading' => [
                'label' => 'Section Heading', 'icon' => '🔠', 'group' => 'Basic',
                'fields' => [
                    ['key' => 'eyebrow', 'label' => 'Eyebrow', 'type' => 'text'],
                    ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                    ['key' => 'subheading', 'label' => 'Sub-heading', 'type' => 'textarea'],
                ],
            ],
            'richtext' => [
                'label' => 'Rich Text', 'icon' => '📝', 'group' => 'Basic',
                'fields' => [
                    ['key' => 'heading', 'label' => 'Heading (optional)', 'type' => 'text'],
                    ['key' => 'body', 'label' => 'Body (HTML allowed)', 'type' => 'richtext'],
                ],
            ],
            'image_text' => [
                'label' => 'Image + Text', 'icon' => '🖼️', 'group' => 'Basic',
                'fields' => [
                    ['key' => 'image', 'label' => 'Image', 'type' => 'image'],
                    ['key' => 'image_side', 'label' => 'Image side', 'type' => 'select', 'options' => ['left' => 'Left', 'right' => 'Right']],
                    ['key' => 'eyebrow', 'label' => 'Eyebrow', 'type' => 'text'],
                    ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                    ['key' => 'body', 'label' => 'Body (HTML allowed)', 'type' => 'richtext'],
                    ['key' => 'cta_label', 'label' => 'Button label', 'type' => 'text'],
                    ['key' => 'cta_url', 'label' => 'Button URL', 'type' => 'url'],
                ],
            ],
            'columns' => [
                'label' => 'Columns', 'icon' => '▦', 'group' => 'Basic',
                'fields' => [
                    ['key' => 'eyebrow', 'label' => 'Eyebrow', 'type' => 'text'],
                    ['key' => 'heading', 'label' => 'Heading (optional)', 'type' => 'text'],
                    ['key' => 'columns', 'label' => 'Number of columns', 'type' => 'select', 'options' => ['2' => '2', '3' => '3', '4' => '4']],
                    ['key' => 'items', 'label' => 'Column Content — one row per column', 'type' => 'repeater', 'sync_with' => 'columns', 'subfields' => [
                        ['key' => 'image', 'label' => 'Image (optional)', 'type' => 'image'],
                        ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                        ['key' => 'text', 'label' => 'Text', 'type' => 'textarea'],
                        ['key' => 'link_label', 'label' => 'Link label (optional)', 'type' => 'text'],
                        ['key' => 'link_url', 'label' => 'Link URL', 'type' => 'url'],
                    ]],
                ],
            ],

            // --------------------------------------------------------- Content
            'features' => [
                'label' => 'Features', 'icon' => '✨', 'group' => 'Content',
                'fields' => [
                    ['key' => 'eyebrow', 'label' => 'Eyebrow', 'type' => 'text'],
                    ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                    ['key' => 'columns', 'label' => 'Columns', 'type' => 'select', 'options' => ['2' => '2', '3' => '3', '4' => '4']],
                    ['key' => 'items', 'label' => 'Feature cards', 'type' => 'repeater', 'subfields' => [
                        ['key' => 'icon', 'label' => 'Icon (emoji)', 'type' => 'icon'],
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
                        ['key' => 'text', 'label' => 'Text', 'type' => 'textarea'],
                    ]],
                ],
            ],
            'services' => [
                'label' => 'Services', 'icon' => '🧩', 'group' => 'Content',
                'fields' => [
                    ['key' => 'eyebrow', 'label' => 'Eyebrow', 'type' => 'text'],
                    ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                    ['key' => 'columns', 'label' => 'Columns', 'type' => 'select', 'options' => ['2' => '2', '3' => '3', '4' => '4']],
                    ['key' => 'items', 'label' => 'Services', 'type' => 'repeater', 'subfields' => [
                        ['key' => 'icon', 'label' => 'Icon (emoji)', 'type' => 'icon'],
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
                        ['key' => 'text', 'label' => 'Text', 'type' => 'textarea'],
                        ['key' => 'link_url', 'label' => 'Link URL', 'type' => 'url'],
                    ]],
                ],
            ],
            'stats' => [
                'label' => 'Stats / Counters', 'icon' => '📊', 'group' => 'Content',
                'fields' => [
                    ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                    ['key' => 'items', 'label' => 'Stats', 'type' => 'repeater', 'subfields' => [
                        ['key' => 'value', 'label' => 'Value', 'type' => 'text'],
                        ['key' => 'label', 'label' => 'Label', 'type' => 'text'],
                    ]],
                ],
            ],
            'steps' => [
                'label' => 'Steps / Timeline', 'icon' => '🪜', 'group' => 'Content',
                'fields' => [
                    ['key' => 'eyebrow', 'label' => 'Eyebrow', 'type' => 'text'],
                    ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                    ['key' => 'items', 'label' => 'Steps', 'type' => 'repeater', 'subfields' => [
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
                        ['key' => 'text', 'label' => 'Text', 'type' => 'textarea'],
                    ]],
                ],
            ],

            // ----------------------------------------------------- Social proof
            'testimonials' => [
                'label' => 'Testimonials', 'icon' => '💬', 'group' => 'Social proof',
                'fields' => [
                    ['key' => 'eyebrow', 'label' => 'Eyebrow', 'type' => 'text'],
                    ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                    ['key' => 'subheading', 'label' => 'Sub-heading', 'type' => 'text'],
                ],
            ],
            'logos' => [
                'label' => 'Logos / Partners', 'icon' => '🏷️', 'group' => 'Social proof',
                'fields' => [
                    ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                    ['key' => 'items', 'label' => 'Logos', 'type' => 'repeater', 'subfields' => [
                        ['key' => 'image', 'label' => 'Logo image', 'type' => 'image'],
                        ['key' => 'url', 'label' => 'Link URL', 'type' => 'url'],
                    ]],
                ],
            ],
            'team' => [
                'label' => 'Team', 'icon' => '👥', 'group' => 'Social proof',
                'fields' => [
                    ['key' => 'eyebrow', 'label' => 'Eyebrow', 'type' => 'text'],
                    ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                    ['key' => 'items', 'label' => 'Members', 'type' => 'repeater', 'subfields' => [
                        ['key' => 'photo', 'label' => 'Photo', 'type' => 'image'],
                        ['key' => 'name', 'label' => 'Name', 'type' => 'text'],
                        ['key' => 'role', 'label' => 'Role', 'type' => 'text'],
                        ['key' => 'link_url', 'label' => 'Link URL', 'type' => 'url'],
                    ]],
                ],
            ],

            // -------------------------------------------------------- Commerce
            'pricing' => [
                'label' => 'Pricing', 'icon' => '💲', 'group' => 'Commerce',
                'fields' => [
                    ['key' => 'eyebrow', 'label' => 'Eyebrow', 'type' => 'text'],
                    ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                    ['key' => 'items', 'label' => 'Plans', 'type' => 'repeater', 'subfields' => [
                        ['key' => 'name', 'label' => 'Plan name', 'type' => 'text'],
                        ['key' => 'price', 'label' => 'Price', 'type' => 'text'],
                        ['key' => 'period', 'label' => 'Period (e.g. /mo)', 'type' => 'text'],
                        ['key' => 'features', 'label' => 'Features (one per line)', 'type' => 'textarea'],
                        ['key' => 'cta_label', 'label' => 'Button label', 'type' => 'text'],
                        ['key' => 'cta_url', 'label' => 'Button URL', 'type' => 'url'],
                        ['key' => 'featured', 'label' => 'Highlighted', 'type' => 'toggle'],
                    ]],
                ],
            ],

            // ------------------------------------------------------- Interactive
            'faq' => [
                'label' => 'FAQ / Accordion', 'icon' => '❓', 'group' => 'Interactive',
                'fields' => [
                    ['key' => 'eyebrow', 'label' => 'Eyebrow', 'type' => 'text'],
                    ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                    ['key' => 'items', 'label' => 'Questions', 'type' => 'repeater', 'subfields' => [
                        ['key' => 'question', 'label' => 'Question', 'type' => 'text'],
                        ['key' => 'answer', 'label' => 'Answer', 'type' => 'textarea'],
                    ]],
                ],
            ],
            'tabs' => [
                'label' => 'Tabs', 'icon' => '🗂️', 'group' => 'Interactive',
                'fields' => [
                    ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                    ['key' => 'items', 'label' => 'Tabs', 'type' => 'repeater', 'subfields' => [
                        ['key' => 'title', 'label' => 'Tab title', 'type' => 'text'],
                        ['key' => 'content', 'label' => 'Tab content (HTML allowed)', 'type' => 'richtext'],
                    ]],
                ],
            ],

            // ------------------------------------------------------------ Media
            'gallery' => [
                'label' => 'Gallery', 'icon' => '📸', 'group' => 'Media',
                'fields' => [
                    ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                    ['key' => 'columns', 'label' => 'Columns', 'type' => 'select', 'options' => ['2' => '2', '3' => '3', '4' => '4']],
                    ['key' => 'images', 'label' => 'Images', 'type' => 'repeater', 'subfields' => [
                        ['key' => 'image', 'label' => 'Image', 'type' => 'image'],
                        ['key' => 'caption', 'label' => 'Caption', 'type' => 'text'],
                    ]],
                ],
            ],
            'slider' => [
                'label' => 'Slider / Carousel', 'icon' => '🎞️', 'group' => 'Media',
                'fields' => [
                    ['key' => 'heading', 'label' => 'Heading (optional)', 'type' => 'text'],
                ],
            ],
            'video' => [
                'label' => 'Video', 'icon' => '🎬', 'group' => 'Media',
                'fields' => [
                    ['key' => 'eyebrow', 'label' => 'Eyebrow', 'type' => 'text'],
                    ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                    ['key' => 'subheading', 'label' => 'Sub-heading', 'type' => 'text'],
                    ['key' => 'video_url', 'label' => 'Video URL (YouTube / Vimeo / .mp4)', 'type' => 'url'],
                ],
            ],

            // ------------------------------------------------------- Call to action
            'cta' => [
                'label' => 'Call to Action', 'icon' => '📣', 'group' => 'Call to action',
                'fields' => [
                    ['key' => 'headline', 'label' => 'Headline', 'type' => 'text'],
                    ['key' => 'subtext', 'label' => 'Sub-text', 'type' => 'textarea'],
                    ['key' => 'button_label', 'label' => 'Primary button label', 'type' => 'text'],
                    ['key' => 'button_url', 'label' => 'Primary button URL', 'type' => 'url'],
                    ['key' => 'button2_label', 'label' => 'Secondary button label', 'type' => 'text'],
                    ['key' => 'button2_url', 'label' => 'Secondary button URL', 'type' => 'url'],
                ],
            ],
            'banner' => [
                'label' => 'Banner strip', 'icon' => '🎗️', 'group' => 'Call to action',
                'fields' => [
                    ['key' => 'text', 'label' => 'Text', 'type' => 'text'],
                    ['key' => 'link_label', 'label' => 'Link label', 'type' => 'text'],
                    ['key' => 'link_url', 'label' => 'Link URL', 'type' => 'url'],
                ],
            ],
            'buttons' => [
                'label' => 'Buttons', 'icon' => '🔘', 'group' => 'Call to action',
                'fields' => [
                    ['key' => 'items', 'label' => 'Buttons', 'type' => 'repeater', 'subfields' => [
                        ['key' => 'label', 'label' => 'Label', 'type' => 'text'],
                        ['key' => 'url', 'label' => 'URL', 'type' => 'url'],
                        ['key' => 'style', 'label' => 'Style', 'type' => 'select', 'options' => ['primary' => 'Primary', 'ghost' => 'Outline']],
                    ]],
                ],
            ],

            // ------------------------------------------------------------ Forms
            'contact' => [
                'label' => 'Contact', 'icon' => '✉️', 'group' => 'Forms',
                'fields' => [
                    ['key' => 'eyebrow', 'label' => 'Eyebrow', 'type' => 'text'],
                    ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                    ['key' => 'subheading', 'label' => 'Sub-heading', 'type' => 'textarea'],
                    ['key' => 'address', 'label' => 'Address', 'type' => 'text'],
                    ['key' => 'phone', 'label' => 'Phone', 'type' => 'text'],
                    ['key' => 'email', 'label' => 'Email', 'type' => 'text'],
                ],
            ],
            'newsletter' => [
                'label' => 'Newsletter', 'icon' => '📬', 'group' => 'Forms',
                'fields' => [
                    ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                    ['key' => 'subtext', 'label' => 'Sub-text', 'type' => 'textarea'],
                    ['key' => 'button_label', 'label' => 'Button label', 'type' => 'text'],
                ],
            ],
            'map' => [
                'label' => 'Map', 'icon' => '🗺️', 'group' => 'Forms',
                'fields' => [
                    ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                    ['key' => 'embed_url', 'label' => 'Google Maps embed URL', 'type' => 'url'],
                ],
            ],

            // ----------------------------------------------------------- Layout
            'spacer' => [
                'label' => 'Spacer / Divider', 'icon' => '➖', 'group' => 'Layout',
                'fields' => [
                    ['key' => 'height', 'label' => 'Height', 'type' => 'select', 'options' => ['sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large']],
                    ['key' => 'divider', 'label' => 'Show divider line', 'type' => 'toggle'],
                ],
            ],
            'html' => [
                'label' => 'Custom HTML', 'icon' => '⟨⟩', 'group' => 'Layout',
                'fields' => [
                    ['key' => 'code', 'label' => 'HTML code', 'type' => 'textarea'],
                ],
            ],
        ];
    }

    public static function keys(): array
    {
        return array_keys(static::all());
    }

    /** Types grouped by their `group` for the add-section picker. */
    public static function grouped(): array
    {
        $groups = [];
        foreach (static::all() as $key => $t) {
            $groups[$t['group']][$key] = $t;
        }

        return $groups;
    }

    public static function label(string $type): string
    {
        return static::all()[$type]['label'] ?? ucfirst($type);
    }

    public static function icon(string $type): string
    {
        return static::all()[$type]['icon'] ?? '⬛';
    }

    public static function fields(string $type): array
    {
        return static::all()[$type]['fields'] ?? [];
    }

    public static function exists(string $type): bool
    {
        return isset(static::all()[$type]);
    }
}
