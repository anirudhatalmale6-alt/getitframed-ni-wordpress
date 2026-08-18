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

`getitframedni.co.uk` has **no MX record and no SPF record**, and the business
address is a `gmail.com` one. Anything the site sends as that domain fails
authentication at the far end.

It is worse than it sounds if the *admin* address belongs to a domain that is
properly protected. `keygrowth.co.uk` publishes `p=reject`, and its SPF lists
only `185.109.170.140`, so mail sent as that address from the client's server
(`49.13.168.158`) is **refused outright** — not junked, refused. The better the
mail authentication, the harder this bites, so the sites where it bites are the
ones already secured. `smilecreative.agency` is on `p=none`, so the same mail
lands in spam instead of vanishing.

Do **not** fix this by adding the web server to the SPF record: that authorises
someone else's entire server to send as the client, permanently.

The fix is `mu-plugins/smile-smtp.php`, which authenticates against the real
mailbox. Add to `wp-config.php`, or to a small file of its own in `mu-plugins/`:

```php
define( 'SMILE_SMTP_HOST', 'mail.smilecreative.agency' );
define( 'SMILE_SMTP_PORT', 587 );          // 587 STARTTLS, 465 implicit TLS
define( 'SMILE_SMTP_USER', 'wpadmin@smilecreative.agency' );
define( 'SMILE_SMTP_PASS', '…' );
define( 'SMILE_SMTP_NAME', 'Get It Framed NI' );
```

It rewrites the From to the mailbox being authenticated, because authenticating
is not sufficient on its own — DMARC also wants the visible From domain to match
the signed domain. `Reply-To` is left alone, so replying to an enquiry still
reaches the customer.

Regardless, the form **stores every submission as an Enquiry before it tries to
send**, and records whether the mail went out. Nothing is lost either way.

---

# Runbook

Everything below assumes the hosting this was actually deployed to: **CWP
(CentOS Web Panel), no shell access, and FTP that logs in but cannot transfer**
because the passive data ports are firewalled. So the file manager in the panel
is the delivery mechanism, and two one-shot PHP runners do the work.

Both runners are guarded by a one-time token, print a plain-text report of
exactly what they did, and **delete themselves** afterwards.

## 1. Deploying onto a fresh WordPress — `gif-deploy.php`

1. Set `GIF_DEPLOY_TOKEN` in the file to 20+ random characters.
2. Build a zip containing `wp-content/themes/getitframed/`,
   `wp-content/mu-plugins/*.php`, `gif-seed.php` and `gif-deploy.php`.
3. Upload it into the WordPress folder in the file manager, and extract it there.
   It only adds files.
4. Visit `/gif-deploy.php?k=THE_TOKEN`.

It activates the theme, seeds the twelve services, the pages and the images,
clears the WordPress sample content, holds `blog_public` at 0 so a staging
address cannot be indexed, writes the rewrite rules, and reports.

Expect: 12 services, 3 pages published, Trade left as a draft, 14 media items.

## 2. Going live at the domain root — `gif-golive.php`

1. Archive the old site first — move `index.html` and `assets/` into e.g.
   `old-site-2025/`. Moved, not deleted.
2. Set `GIF_GOLIVE_TOKEN`, and check `GIF_LIVE_URL` is the address you want.
3. Upload into the WordPress folder, beside `wp-load.php`.
4. Visit `/wp/gif-golive.php?k=THE_TOKEN`.

**WordPress is not moved.** It stays in `/wp/`; only the address changes, via a
small front controller at the root. See "Why the site is not moved" below.

It also turns indexing back on, forces https, blocks the version disclosure,
removes `readme.html` / `license.txt` / the leftover seeder, backs up any
existing `.htaccess` to `.htaccess.before-golive`, and prints the undo steps.

### Undo

Put `index.html` and `assets/` back, delete the root `index.php`, restore
`.htaccess.before-golive`, and set `home` and `siteurl` back to the `/wp` address.

## Why the site is not moved

WordPress stores its own URL in the database, so copying files to the root gives
an unstyled page with every link pointing back at `/wp/`. The usual repair is a
find-and-replace across the database — and URLs live inside serialised PHP,
where a blind replace breaks the `s:NN:` length prefixes and the damage is
silent.

Changing `home` while leaving `siteurl` avoids all of it. Page and menu links
are generated at render time so they follow on their own, and the only stored
URLs are asset paths under `/wp/wp-content/`, which keep working untouched.

## Things that fail silently — each one has a guard

- **`index.html` left at the root.** Apache serves it ahead of `index.php`, so
  the old site keeps answering and the change looks like it did nothing.
- **`WP_HOME` / `WP_SITEURL` in `wp-config.php`.** They override the database.
  `update_option()` still writes the row and still reads it back, so a report
  built from `get_option()` claims success while the site stays put.
- **`blog_public` left at 0.** The site launches invisible to search engines
  with nothing on screen to show it.
- **`save_mod_rewrite_rules()`** writes nothing when `got_mod_rewrite()` is
  false and returns without complaint. Both runners read `.htaccess` back
  afterwards instead of trusting the call.
- **`switch_theme()`** does not load the new theme's `functions.php` in the same
  request, so on a fresh install the theme's functions do not exist yet and its
  `add_image_size()` calls never register — the crops are silently never made.
- **`set_permalink_structure()`** calls `WP_Rewrite::init()`, which wipes the
  permastructs `register_post_type()` added. Set the structure **before**
  registering, or the site comes up with the pages working and every custom post
  type 404ing. Count the CPT rules, not the total.

## Installing by hand, without the runners

1. Copy `getitframed/` into `wp-content/themes/` and activate it.
2. On a fresh install only, with shell access: put `gif-seed.php` in the
   WordPress root, run `php gif-seed.php`, then **delete it**.
3. Settings → Permalinks → Save.
4. Appearance → Customise → Get It Framed, to check the contact details.

Requires PHP 7.4+ and WordPress 6.4+. No PHP 8-only syntax is used, so it runs
on the client's PHP 7.4 — though 7.4 has been end-of-life since November 2022
and should be raised.

---

Built by Smile Creative.
