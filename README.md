# Get It Framed NI — WordPress theme

The approved HTML design prototype, converted into a WordPress theme. Same
design, same colours, same typography — but the content is now real WordPress
content, so it can be edited, and the links go somewhere.

**Built for:** Smile Creative → Get It Framed NI (Darren Cumberland), Portglenone, Co. Antrim.

---

## What is in here

```
getitframed/           the theme
  style.css            the prototype's CSS, plus the WordPress additions
  functions.php        setup, assets, housekeeping
  inc/
    helpers.php        link/image/breadcrumb helpers
    post-types.php     Services, Gallery, Enquiries
    customizer.php     business details + homepage wording
    meta-boxes.php     service card colour and strapline
    contact-form.php   the enquiry form and its handler
    seo.php            meta description, Open Graph, LocalBusiness schema
  template-parts/
    location.php       the "Visit the Studio" block
  assets/
    fonts/             Inter + Libre Baskerville, self-hosted
    img/               the 13 photographs from the prototype
    js/site.js         menu, gallery filter, lightbox
    css/editor.css     makes the back-end editor resemble the front end
gif-seed.php           one-off script that creates the 12 services and the pages
```

## No page builder, no premium plugins

Nothing here needs a licence key, a subscription, or a plugin that can lapse.
The design is in the theme; the content is in WordPress. That is deliberate —
a nulled or expired builder plugin is the single most common way a small
business site gets compromised.

Content types:

| Type | Used for | Where it appears |
|---|---|---|
| **Services** | one entry per service | homepage grid, menu dropdown, footer, `/services/` |
| **Gallery** | one entry per photograph | `/gallery/`, with category filter buttons |
| **Enquiries** | contact form submissions | admin only, never public |
| **Pages** | About, Trade, Contact | menu, footer |

Add a service and it appears in the grid, the dropdown and the footer at once.
Leave a page as a draft and it disappears from the menu instead of becoming a
dead link.

## What was fixed on the way across

The prototype was a design mock-up, and it had the gaps you would expect of one.
All of these are resolved in this build:

- **30 dead links on the homepage alone.** Every service in the dropdown, every
  "More" button on the cards, and Trade all pointed at `#`. Links are now
  generated from real content, so a dead one cannot be produced.
- **The main call to action did nothing.** "Get in Touch" appeared three times
  pointing at `#contact`, but the section was called `#location`. Both ids now
  exist, and where a Contact page is published the button goes there instead.
- **There was no way to make an enquiry.** Contact was `mailto:` and `tel:`
  only. There is now a proper form.
- **No meta descriptions, no Open Graph tags, no structured data.** All present,
  including LocalBusiness markup built from the address in the Customizer.
- **The logo and hero image were base64-encoded into the HTML**, adding ~120 KB
  to every single page load and preventing any caching. They are real files now.
- **Fonts were pulled from Google on every page load.** Self-hosted, Latin
  subsets only, which also stops a visitor's IP being handed to a third party.
- **Hero text ran off the edge on phones.** `.hero` is also `.container`, and its
  `padding: 3.5rem 0` was silently wiping the container's 5% side padding.

## Still outstanding — content, not code

- **The gallery and prints imagery in the prototype was stock, hotlinked live
  from Unsplash.** None of it has been carried over. A framing and photography
  business cannot show other people's photographs. Real photos of Darren's own
  work are needed before launch.
- **Eleven of the twelve services have a summary but no full page copy.** They
  are published with the summary and a line inviting a call; the moment real
  copy arrives it goes in the editor and the page is complete.
- **Trade page** is a draft awaiting content, so it is not in the menu.
- **Commercial Printing** has no photograph. The prototype reused the laser
  engraving image for it, which shows the wrong thing.

## Email — read this before launch

`getitframedni.co.uk` currently has **no MX record and no SPF record**, and the
business address is a `gmail.com` one. Any mail the site sends from the domain
will fail authentication at the receiving end and land in spam, or be rejected.

The form therefore **saves every submission as an Enquiry before it attempts to
send**, and records whether the email actually went out. Nothing is lost either
way. But before go-live, configure authenticated SMTP (a Gmail app password is
enough) so enquiries reliably arrive.

## Installing

1. Copy `getitframed/` into `wp-content/themes/` and activate it.
2. Optional, on a fresh install only: put `gif-seed.php` in the WordPress root,
   run `php gif-seed.php`, then **delete it**. It creates the twelve services,
   imports the images, and sets the pages and permalinks.
3. Settings → Permalinks → Save, to flush the rewrite rules.
4. Appearance → Customise → Get It Framed, to check the contact details.

Requires PHP 7.4+ and WordPress 6.4+.

## Moving it to the live server

Build on staging, then migrate the whole thing. Do **not** copy the files and
edit the database by hand.

WordPress stores its own URL inside serialised PHP in `wp_options` and
`wp_postmeta`. A plain SQL find-and-replace corrupts the length prefixes on
every one of those values, and the damage is silent — the site looks fine until
settings start disappearing. Use one of:

- **All-in-One WP Migration** — export on staging, import on live, done.
- **WP-CLI** — `wp search-replace 'https://staging.example' 'https://getitframedni.co.uk' --all-tables --precise` — this is serialisation-aware.

Afterwards: Settings → Permalinks → Save, check the map embed still loads, and
confirm the contact form sends from the live domain.

---

Built by Smile Creative.
