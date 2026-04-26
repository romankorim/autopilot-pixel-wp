# autopilot-pixel-wp

Auto-installed Meta Pixel + Conversions API plugin for WordPress / WooCommerce. Hosted by [iteractive.ai Autopilot](https://app.iteractive.ai).

## What this is

When an Autopilot customer connects their WordPress site (via WP Application Password authorize flow), the Autopilot backend installs this plugin via the WP REST `POST /wp/v2/plugins` endpoint and sets the `autopilot_pixel_id` option. The customer never touches code or plugin settings.

## What it does

- Fires Meta Pixel `PageView` on every public page load.
- Forwards WooCommerce `order_status_completed` events to `https://app.iteractive.ai/functions/v1/wp-conversion`, which signs and posts to Meta's Conversions API server-side. Solves the iOS-14 attribution problem.

## Why a separate repo

WordPress.org plugin directory has a long, opinionated review process — comparable to the Shopify App Store. Hosting our own plugin zip at a CDN URL we control sidesteps that entirely. WP's `POST /wp/v2/plugins` endpoint accepts a `source` URL and downloads + activates the zip directly.

## Releases

Push a tag `v1.x.y`. GitHub Actions builds `autopilot-pixel-vX.Y.Z.zip` and `autopilot-pixel-latest.zip`, attaches them to the release, and they become reachable at:

- `https://github.com/romankorim/autopilot-pixel-wp/releases/download/vX.Y.Z/autopilot-pixel-vX.Y.Z.zip`
- `https://github.com/romankorim/autopilot-pixel-wp/releases/latest/download/autopilot-pixel-latest.zip` ← the URL the Autopilot backend uses

(Optional later: copy to `cdn.iteractive.ai/wp-plugins/...` for vanity + faster downloads.)

## Local dev

```
php -l autopilot-pixel.php
```

That's it — single PHP file, no build step.

## License

GPLv2-or-later (required for WP plugins).
