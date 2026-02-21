# Symkit Error Bundle

[![CI](https://github.com/symkit/error-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/symkit/error-bundle/actions)
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
```

- **enabled** : when `false`, the bundle does not override error pages (Symfony defaults are used) and does not register the Twig global.
- **website_name** : name displayed on error pages. Exposed in Twig as the global `symkit_error_website_name` (prefixed to avoid collisions with other bundles).

## Included Templates

| Template | HTTP Code | Description |
|---|---|---|
| `error404.html.twig` | 404 | Page not found |
| `error403.html.twig` | 403 | Forbidden access |
| `error401.html.twig` | 401 | Unauthorized |
| `error429.html.twig` | 429 | Too many requests |
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

The base layout exposes these Twig blocks: `error_title`, `cursor_color`, `glow_effect`, `terminal_card`, `error_content`, `footer_link`. It also uses the Twig global `symkit_error_website_name` for the site name.

## Contributing

```bash
make install    # Install dependencies
make quality    # Run full quality pipeline (CS, PHPStan, Deptrac, tests, Infection)
```

Never commit code that fails `make quality`.

## License

MIT
