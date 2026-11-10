# CMS Orbit starter for Symfony

A Symfony website that reads its pages from [CMS Orbit](https://bifrostorbit.com/products/cms-orbit/), the headless CMS in Bifröst Orbit, with the PHP SDK `bifrost-orbit/cms-sdk`. It is the finished project from the [Symfony quickstart](https://docs.bifrostorbit.com/cms/developers/quickstart-symfony/): every published page at its own address, Twig templates for the blocks editors place, drafts in the live preview, and a signed webhook that clears only the cached pages that changed.

[![Open in GitHub Codespaces](https://github.com/codespaces/badge.svg)](https://codespaces.new/bifrost-orbit/cms-starter-symfony?quickstart=1)

## Set up

1. **Create your copy.** Select **Use this template** on GitHub, or run `gh repo create my-website --template bifrost-orbit/cms-starter-symfony --private --clone`. Then run `composer install`.
2. **Add your settings.** Copy `.env.example` to `.env.local` and fill in the API address, keys and region from **Configuration › API keys** and **Configuration › Environments** in CMS Orbit. `.env` holds only defaults; Git leaves `.env.local` out.
3. **Run it.** Run `symfony serve`, or `php -S localhost:8000 -t public`, and open `http://localhost:8000`.

## What's inside

| File | What it does |
| --- | --- |
| `config/services.yaml` | The CMS Orbit client, with its settings from the environment |
| `config/packages/cache.yaml` | A cache pool with tags of its own for CMS answers |
| `src/Controller/PageController.php` | One route for every page: redirects, not found, gone, caching and drafts |
| `src/Twig/CmsExtension.php` | `cms_head`, `cms_blocks` and `cms_bridge` for templates, and which template draws which CMS component |
| `templates/page.html.twig`, `templates/blocks/` | The layout, and the templates: `hero`, `action`, `featureGrid` and `featureItem` |
| `src/Controller/CmsWebhookController.php` | The signed webhook at `/cms-webhook` that clears cached pages when editors publish |

## Make it yours

- Write your templates in `templates/blocks/` and list them in `TEMPLATES` in `src/Twig/CmsExtension.php`, using the component keys from your own space. **Configuration › Components** lists them.
- For the live preview, serve the website over `https` and set **Preview URL** in the space's **Settings**. For refresh on publish, add a webhook to `https://your-website/cms-webhook` and put its secret in `CMS_WEBHOOK_SECRET`. The [quickstart](https://docs.bifrostorbit.com/cms/developers/quickstart-symfony/) explains both.
- In production, set `APP_ENV=prod`, a random `APP_SECRET` and the `CMS_` settings as environment variables on the server.

Needs PHP 8.4 or later, Composer and `bifrost-orbit/cms-sdk` 0.2.

## Licence

MIT. See [LICENSE](LICENSE).
