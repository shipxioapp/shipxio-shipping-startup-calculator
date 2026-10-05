# Shipxio Shipping Startup Calculator

A WordPress calculator for people planning to start a shipping company. It covers Business Registration, Logo Design, Brand Identity, Domain Name, Website, Web Hosting, Business Email, and Shipping Software.

One-time JMD startup costs, monthly USD costs, and yearly USD costs are shown separately. There is no currency conversion. With all items selected: **J$148,000**, **US$60/month**, and **US$183.88/year**.

## Requirements and installation

WordPress **6.3+** and PHP **8.1+**. Install the release ZIP through **Plugins > Add New > Upload Plugin**, then activate it. Calculations run locally in the browser and require no account, external service, or API.

## Shortcodes

| Shortcode | Displays |
| --- | --- |
| `[shipxio_shipping_startup_calculator]` | Calculator with its title and description |
| `[shipxio_shipping_startup_calculator show_intro="false"]` | The same calculator without its title and description |

Use normal WordPress content, a Gutenberg Shortcode block, or Elementor's standard **Shortcode** widget. Elementor is optional. Multiple calculators work independently.

## Settings and appearance

Open **Settings > Shipxio Shipping Startup Calculator** to edit the eight item names and descriptions. Other item properties remain fixed. The settings page includes both shortcode examples and copy buttons.

The theme or Elementor container controls outer width and spacing. Frontend CSS consumes existing `--mpx-*` website tokens with built-in fallbacks and does not enqueue a token stylesheet. Logo Design and Brand Identity retain their Go Ship example boards.

## Releases and updates

This directory is the production repository root. The release scripts and VS Code task live in the outer development project, matching the existing operator workflow. Open the outer project as the VS Code workspace and run **Tasks: Run Task > Release - Shipxio Shipping Startup Calculator**. Enter an `X.Y.Z` version. The task calls `release-shipping-startup-calculator.ps1`, which preserves existing changelog entries, shows pending changes, and requires confirmation before committing pre-existing work. `-Notes` supplies release notes; `-Yes` permits non-interactive confirmation.

`release-plugin.ps1` owns version synchronization, validation, the `Release X.Y.Z` commit, annotated `vX.Y.Z` tag, and atomic push of `main` plus the tag. A failed atomic push preserves the prepared local commit/tag for retry with the same engine command.

GitHub Actions builds stable and versioned ZIPs, verifies them, publishes a validated draft release, retains three release objects without deleting Git tags, and updates the manifest on `main`. Published releases remain immutable; workflow reruns can retry metadata work.

WordPress checks the public GitHub manifest and downloads only this repository's release ZIPs. The GitHub repository is the authoritative project URL for the plugin and updater; no separate marketing page is required. It uses its normal update timing, a short failure throttle, and the installed local mark. No GitHub authentication is required. Settings survive deactivation and are removed only on plugin deletion.

Release preparation stops before commits or pushes if repository identity metadata is missing or inconsistent.

## Verification

Source and isolated fixtures have been checked. Live WordPress activation, authenticated settings saves, Gutenberg, and Elementor acceptance have not been verified. No live-tested WordPress version is declared yet.

## License

GPLv2 or later. See [LICENSE](LICENSE).
