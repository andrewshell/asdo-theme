## Commands

```bash
composer install          # Install PHP dependencies
composer lint             # Run PHP_CodeSniffer (WordPress coding standards + PHP compatibility)
vendor/bin/phpcs          # Run PHPCS directly
vendor/bin/phpcbf         # Auto-fix fixable PHPCS violations
```

No JavaScript build process exists. CSS and JS are served as static files.

### Semantic Markup

Templates use microformats2 classes (`h-card`, `h-entry`, `p-name`, `e-content`, etc.) for IndieWeb (webmention, ActivityPub, mf2 parsers). Preserve these when modifying templates. **Do not reintroduce inline schema.org Microdata** (`itemscope`/`itemtype`/`itemprop`) — it was deliberately removed in favor of JSON-LD.

### Structured Data (JSON-LD)

All schema.org structured data is JSON-LD, generated centrally by `asdo_jsonld()` in `functions.php` (hooked to `wp_head`) — never inline in templates. It emits a single `@graph`: `WebSite` + `SearchAction`, a `Person`, `ProfilePage` on author archives, and `BlogPosting`/`Article` on singular views.

The `Person` is profile-driven, not hardcoded:

- `asdo_site_author_id()` resolves the canonical author (post author on singular, queried user on author archives, else first publishing user).
- `asdo_person_data()` builds one normalized identity record from the WP user profile. **`bio.php` reads the same record** — keep both sourced from it so the visible h-card and JSON-LD cannot drift.
- Identity fields come from the wp-admin user profile: built-in fields (name, first/last, website, avatar, bio), social URLs via the `user_contactmethods` filter, and custom Job Title / Locality / Region fields (`asdo_user_profile_fields()` + save handler).
- The Person `@id` is anchored to the WordPress author URL (the ActivityPub plugin's actor `url`); `sameAs` always includes `?author=ID` (the actor's stable `id`) to bridge the schema.org and fediverse identities.

### Theme Requirements

- WordPress 6.0+ / PHP 8.1+ (set in `style.css` header, `phpcs.xml`, and `composer.json`)
- Text domain: `asdo-theme`
