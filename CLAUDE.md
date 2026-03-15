# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
composer install          # Install PHP dependencies
composer lint             # Run PHP_CodeSniffer (WordPress coding standards + PHP compatibility)
vendor/bin/phpcs          # Run PHPCS directly
vendor/bin/phpcbf         # Auto-fix fixable PHPCS violations
```

No JavaScript build process exists. CSS and JS are served as static files.

## Architecture

This is a single-author WordPress blog theme ("Andrew Shell's Weblog") focused on IndieWeb integration, microformats (h-card, h-entry), and schema.org structured data.

### Post Types & Content Routing

All posts use the "essays" category and render via `template-parts/content-essay.php` (BlogPosting schema).

### Key Files

- **`functions.php`** — Theme setup, asset enqueuing, analytics, category auto-creation, RSS rewrite rules, and helper functions
- **`front-page.php`** — Homepage with bio and recent posts feed (uses `asdo_recent_content()` which filters to current month if enough posts exist)
- **`page-essays.php`** / **`page-search.php`** — Custom page templates (assigned by slug)

### Semantic Markup

Templates use microformats2 classes (`h-card`, `h-entry`, `p-name`, `e-content`, etc.) and schema.org JSON-LD patterns. Preserve these when modifying templates.

### Theme Requirements

- WordPress 6.0+ / PHP 8.1+ (set in `style.css` header, `phpcs.xml`, and `composer.json`)
- Text domain: `asdo-theme`

### Static Assets

- `/css/normalize.css` — CSS reset (v8.0.1)
- `/css/prism-tomorrow.css` — Syntax highlighting for code blocks
- `/style.css` — Main stylesheet using CSS custom properties (design tokens for colors, spacing, typography)
- `/img/` — Profile pictures
