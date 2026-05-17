## Commands

```bash
composer install          # Install PHP dependencies
composer lint             # Run PHP_CodeSniffer (WordPress coding standards + PHP compatibility)
vendor/bin/phpcs          # Run PHPCS directly
vendor/bin/phpcbf         # Auto-fix fixable PHPCS violations
```

No JavaScript build process exists. CSS and JS are served as static files.

### Semantic Markup

Templates use microformats2 classes (`h-card`, `h-entry`, `p-name`, `e-content`, etc.) and schema.org JSON-LD patterns. Preserve these when modifying templates.

### Theme Requirements

- WordPress 6.0+ / PHP 8.1+ (set in `style.css` header, `phpcs.xml`, and `composer.json`)
- Text domain: `asdo-theme`
