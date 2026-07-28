# Website Builder (Frontend-from-Backend) — Business-Logic Spec

> The reference system manages its public website through a scattered set of
> FrontSettings controllers (HomePage, Pages, HeaderMenuManager, HomeSlider,
> SpeechSlider, Testimonials) plus a PageBuilder/OptionBuilder. EasySchool
> consolidates this into one **Builder** module: a block-based page builder,
> menus, sliders, testimonials and site settings — all driving a single, modern
> **public theme** rendered at the site root.

## What it does

The admin composes the public website entirely from the backend. A **page** is an
ordered list of typed **content blocks**; each block's fields live as JSON so new
block types are pure additions. Menus, sliders, testimonials and branding settings
feed the same public theme. The site root `/` renders the home page; `/p/{slug}`
renders any other published page. Editing anything in the admin is reflected on the
public site immediately.

## Entities & tables

| # | Table | Model | Purpose |
|---|---|---|---|
| 1 | `site_settings` | `SiteSetting` | Single row: name, tagline, logo, primary/secondary colors, contact, social, footer. `SiteSetting::current()`. |
| 2 | `cms_pages` | `CmsPage` | A page: title, unique slug, is_home, is_published, meta. |
| 3 | `cms_blocks` | `CmsBlock` | A typed block on a page: type, position, `data` JSON, is_visible. |
| 4 | `cms_menus` | `CmsMenu` | A menu bound to a location (header/footer). |
| 5 | `cms_menu_items` | `CmsMenuItem` | An entry linking to a page or url, with `parent_id` nesting + position. |
| 6 | `sliders` | `Slider` | Hero/carousel slides. |
| 7 | `testimonials` | `Testimonial` | Reviews with rating. |

None are academic-year scoped — a public website is timeless.

## Block types (the page-builder registry)

`Modules\Builder\Support\BlockType` is the single source of truth shared by the
admin block editor (renders a form per type) and the public renderer (reads the
same fields): **slider, hero, features, stats, richtext, cta, testimonials,
gallery**. Each type declares editable fields; `repeater` fields hold a list of
rows (feature cards, stats, gallery images). A `slider`/`testimonials` block pulls
live rows from the sliders/testimonials tables so those CRUD screens surface on the
page.

## Rendering rules

- Home = the single published page with `is_home = true` (enforced by
  `markHome`, which unsets the flag on every other page). No home page → a
  branded fallback invites the admin to build one.
- Only `is_published` pages and `is_visible` blocks render.
- Media values resolve through `BuilderService::media()`: an absolute URL is used
  as-is; anything else is a `public`-disk path — so an image field accepts either
  an upload or an external URL.
- The theme is brand-aware: `primary_color`/`secondary_color` feed CSS custom
  properties, so changing them in settings restyles the whole site (gradients,
  buttons, accents). RTL/locale come from the app locale (`dir`, `lang`).

## Divergence from the reference (deliberate)

| Reference | EasySchool | Why |
|---|---|---|
| Fixed page templates (home/about/contact) + separate controllers each | One block-based page model; any page = ordered blocks | A real page builder; new sections are data, not new tables/controllers. |
| Menu manager with `type`/`element_id`/`theme` columns | `page_id` OR `url` + `parent_id` | Simpler, explicit link resolution via `href()`. |
| Many front-settings tables (home_page_settings, about_pages, course_pages…) | `site_settings` singleton + CMS pages | One settings row; page content lives in blocks. |
| Theme baked into legacy blade set | Single self-contained modern theme driven by brand colors | Beautiful, dependency-free (no build step), color-themeable. |

## Service surface (`BuilderService`)

Public: `settings`, `homePage`, `pageBySlug`, `menu(location)`, `activeSliders`,
`activeTestimonials`, `media`. Editing: `addBlock`, `updateBlock`, `moveBlock`,
`uniqueSlug`, `markHome`.
