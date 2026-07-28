<?php

namespace Modules\Builder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Builder\Services\BuilderService;

/** A typed content section. `data` holds content fields; `settings` holds style. */
class CmsBlock extends Model
{
    use SoftDeletes;

    protected $fillable = ['page_id', 'type', 'position', 'data', 'settings', 'is_visible'];

    protected $casts = [
        'data'       => 'array',
        'settings'   => 'array',
        'is_visible' => 'boolean',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(CmsPage::class, 'page_id');
    }

    /** Read a content field out of the JSON `data` with a fallback. */
    public function get(string $key, mixed $default = null): mixed
    {
        return data_get($this->data, $key, $default);
    }

    /** Read a style setting out of the JSON `settings` with a fallback. */
    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * Resolve this section's presentation for the public renderer: a wrapper
     * class list, an inline style string, the inner container width class,
     * alignment and an optional anchor id.
     *
     * @return array{classes:string, style:string, width:string, align:string, anchor:?string}
     */
    public function sectionStyle(): array
    {
        $s = $this->settings ?? [];

        $bgType = $s['bg_type'] ?? 'none';
        $padY   = $s['pad_y'] ?? 'lg';
        $width  = $s['width'] ?? 'boxed';
        $align  = $s['align'] ?? 'left';
        $theme  = $s['text_theme'] ?? 'auto';

        $classes = ['cms-section', 'pad-'.$padY, 'align-'.$align];
        $styles  = [];

        if ($bgType === 'color') {
            $styles[] = 'background:'.(($s['bg_color'] ?? '') ?: '#ffffff');
        } elseif ($bgType === 'gradient') {
            $c1 = ($s['bg_color'] ?? '') ?: 'var(--primary)';
            $c2 = ($s['bg_color2'] ?? '') ?: 'var(--secondary)';
            $styles[] = "background:linear-gradient(135deg,{$c1},{$c2})";
        } elseif ($bgType === 'image' && ! empty($s['bg_image'])) {
            $url = BuilderService::media($s['bg_image']);
            $styles[] = "background-image:url('{$url}')";
            $classes[] = 'has-bg-image';
            $styles[] = '--overlay:'.(isset($s['overlay']) ? (float) $s['overlay'] : 0.45);
        }

        if (! empty($s['text_color'])) {
            // A custom color wins outright — exposed as a CSS variable so only
            // typography rules that opt in (via var(--section-text, fallback))
            // are affected; buttons/badges/eyebrows keep their brand colors.
            $styles[] = '--section-text:'.$s['text_color'];
        } elseif ($theme === 'light' || (($bgType === 'gradient' || $bgType === 'image') && $theme === 'auto')) {
            // Light (white) text by default over gradient / image backgrounds.
            $classes[] = 'on-dark';
        } elseif ($theme === 'dark') {
            $classes[] = 'on-light';
        }

        if (! empty($s['soft'])) {
            $classes[] = 'is-soft';
        }

        return [
            'classes' => implode(' ', $classes),
            'style'   => implode(';', $styles),
            'width'   => $width,
            'align'   => $align,
            'anchor'  => $s['anchor'] ?? null,
        ];
    }
}
