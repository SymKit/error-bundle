# Symkit Error Bundle

[![CI](https://github.com/symkit/error-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/symkit/error-bundle/actions)
[![Latest Version](https://img.shields.io/packagist/v/symkit/error-bundle.svg)](https://packagist.org/packages/symkit/error-bundle)
[![PHPStan Level 9](https://img.shields.io/badge/PHPStan-level%209-brightgreen.svg)](https://phpstan.org/)

Premium error page templates for Symfony applications. Overrides default Symfony error pages with a modern terminal-themed design.

## Installation

```bash
composer require symkit/error-bundle twig/intl-extra
```

The error templates use Twig Intl filters (`format_datetime`, etc.). With Twig enabled, you must register the Intl extension, for example in `config/packages/twig.yaml`:

```yaml
twig:
    extra:
        intl: true
```

(or register `Twig\Extra\Intl\IntlExtension` as a service tagged `twig.extension`). If the bundle is enabled and Twig is present but the extension is missing, the container build fails with a message describing these steps.

If not using Symfony Flex, register the bundle manually in `config/bundles.php`:

```php
return [
    // ...
    Symkit\ErrorBundle\SymkitErrorBundle::class => ['all' => true],
];
```

## Configuration

```yaml
# config/packages/symkit_error.yaml
symkit_error:
    enabled: true              # default: true — set to false to use Symfony default error pages
    website_name: 'Your Brand' # default: 'Symkit' — trimmed, max 200 characters; empty becomes default
    home_path: '/'             # default: '/' — see "Home path (security)" below
```

- **enabled** : when `false`, the bundle does not override error pages (Symfony defaults are used) and does not register the Twig globals.
- **website_name** : name displayed on error pages. Exposed in Twig as the global `symkit_error_website_name`. Values longer than 200 characters or blank (after trim) fall back to `Symkit`.
- **home_path** : target for “Back to homepage” and brand/footer links. Exposed as `symkit_error_home_path`. See below.

### Home path (security)

`home_path` must be a **safe app-relative path** so links cannot be turned into `javascript:`, `data:`, protocol-relative (`//…`), or absolute URLs. Allowed shape:

- Starts with exactly one `/` (not `//`)
- Path segments: letters (including Unicode), digits, `_`, `-`, `.`, `~`
- Optional `?query` and `#fragment` (query/hash are not validated as strictly as the path; keep them trusted)
- Maximum length 2048 characters

Invalid or unsafe values are **silently replaced with** `/`. For an external homepage URL, override the templates and build the link yourself (e.g. with a parameter you control).

## Included Templates

| Template | HTTP Code | Description |
|---|---|---|
| `error404.html.twig` | 404 | Page not found |
| `error403.html.twig` | 403 | Forbidden access |
| `error401.html.twig` | 401 | Unauthorized |
| `error429.html.twig` | 429 | Too many requests |
| `error503.html.twig` | 503 | Service unavailable / maintenance |
| `error.html.twig` | 5xx | Generic server error |
| `base_error.html.twig` | — | Base layout for all error pages |

All these pages set `<meta name="robots" content="noindex, follow">` where applicable.

## Internationalization

The bundle ships with French and English translations. The locale is determined by the application's current locale (`app.request.locale`).

To override translations, create your own XLIFF file using the `SymkitErrorBundle` domain.

## Customization

Override any template in your application by creating the corresponding file:

```
templates/bundles/TwigBundle/Exception/error404.html.twig
```

The base layout exposes these Twig blocks: `error_title`, `meta_robots`, `cursor_color`, `glow_effect`, `terminal_card`, `error_content`, `footer_link`. It uses the Twig globals `symkit_error_website_name` and `symkit_error_home_path`. The main landmark is `<main id="main-content" class="error-container">`. Decorative SVGs use `aria-hidden="true"` and `focusable="false"`.

## Contributing

```bash
make install   # Install dependencies
make ci        # Full pipeline: audit, quality (CS, PHPStan, Deptrac, PHPUnit, Infection)
```

Never commit code that fails `make ci`.

## License

MIT
