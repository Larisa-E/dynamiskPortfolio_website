# Dynamisk Portfolio Website (Static Version)

See also: [Dynamic app guide](README.md).

This is the static publishing version of the portfolio, prepared for GitHub Pages.

It is generated from the dynamic project and exported into the `docs/` folder, so the site can be hosted without PHP or a database.

Live website: https://larisa-e.github.io/dynamiskPortfolio_website/

## Demo

![Static portfolio demo](docs/assets/gifs/demo-portfolio.gif)

## Project Presentation

This static portfolio is designed as a fast, accessible, and easy-to-share public showcase.

- It presents projects, profile content, and contact information in plain static pages.
- It is optimized for simple hosting and reliable deployment through GitHub Pages.
- It keeps the visual identity of the main portfolio while removing server-side dependencies.

In short: this version is for publishing, while the dynamic version is for editing and content management.

Static output folder: `docs/`

## Generate/Refresh Static Site

Run:

```bash
php scripts/export_static.php
```

This updates the static files in `docs/`.

## Deploy to GitHub Pages

Set GitHub Pages to:
- Branch: `main`
- Folder: `/docs`

Steps:
1. Open repository Settings.
2. Open Pages.
3. Choose `Deploy from a branch`.
4. Select `main` and `/docs`.
5. Save.
