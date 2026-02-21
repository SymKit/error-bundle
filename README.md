# Symkit Error Bundle

[![CI](https://github.com/symkit/error-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/symkit/error-bundle/actions)
[![Latest Version](https://img.shields.io/packagist/v/symkit/error-bundle.svg)](https://packagist.org/packages/symkit/error-bundle)
[![PHPStan Level 9](https://img.shields.io/badge/PHPStan-level%209-brightgreen.svg)](https://phpstan.org/)

Premium error page templates for Symfony applications. Overrides default Symfony error pages with a modern terminal-themed design.

## Installation

```bash
composer require symkit/error-bundle
```

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
    website_name: 'Your Brand' # default: 'Symkit'
    home_path: '/'             # default: '/' — URL or path for "Back to homepage" and footer links
```

- **enabled** : when `false`, the bundle does not override error pages (Symfony defaults are used) and does not register the Twig globals.
- **website_name** : name displayed on error pages. Exposed in Twig as the global `symkit_error_website_name` (prefixed to avoid collisions with other bundles).
- **home_path** : link target for the homepage (e.g. `/` or `/app`). Exposed in Twig as the global `symkit_error_home_path`.

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

## Internationalization

The bundle ships with French and English translations. The locale is determined by the application's current locale (`app.request.locale`).

To override translations, create your own XLIFF file using the `SymkitErrorBundle` domain.

## Customization

Override any template in your application by creating the corresponding file:

```
templates/bundles/TwigBundle/Exception/error404.html.twig
```

The base layout exposes these Twig blocks: `error_title`, `cursor_color`, `glow_effect`, `terminal_card`, `error_content`, `footer_link`. It uses the Twig globals `symkit_error_website_name` (site name) and `symkit_error_home_path` (homepage link).

## Contributing

```bash
make install       # Install dependencies
make install-hooks # Optional: install git hook that strips Co-authored-by from commits
make quality       # Run full quality pipeline (CS, PHPStan, Deptrac, tests, Infection)
```

Never commit code that fails `make quality`.

## License

MIT
