# Water@School is Cool

Landing page for the Water@School is Cool schools campaign, a new tab on topcoolers.co.za. Built from *Water at School is Cool – Landing Page Final* (desktop and phone mockups).

It runs as a standalone site on Vercel at **wateratschooliscool.co.za** (the address on the banner and QR code). Its menu links back to topcoolers.co.za, and the Water@School is Cool tab on topcoolers.co.za links here.

- `index.html`: the page (HTML, CSS and a little JS). Work Sans from Google Fonts.
- `assets/`: logos, product images, technician photo and client logos.
- `api/enquire.js`: Vercel function that emails "Book A Free Assessment" requests to sales@topvending.co.za over SMTP.
- `bricks/`, `campaign-domain-redirect/`: the earlier WordPress/Bricks and redirect approach. Not used by the Vercel site (see `.vercelignore`).

## Deploying on Vercel

1. In Vercel, **Add New → Project** and import `Algorithm-web-dev/water-is-cool`. Framework preset: **Other**. No build command or output directory is needed.
2. Production branch: set it to the branch this work is on, or merge it into `main` first.
3. **Settings → Environment Variables** (Production), using the same SMTP account as WP Mail SMTP on topcoolers.co.za:

   | Name | Value |
   |---|---|
   | `SMTP_HOST` | SMTP server, e.g. `smtp.example.co.za` |
   | `SMTP_PORT` | `587` (or `465`) |
   | `SMTP_USER` | SMTP username / mailbox |
   | `SMTP_PASS` | SMTP password |
   | `MAIL_FROM` | optional, sender address (defaults to `SMTP_USER`) |
   | `MAIL_TO` | optional, defaults to `sales@topvending.co.za` |
   | `SMTP_SECURE` | optional, `true` for port 465 (set automatically for 465) |

   Redeploy after adding them.
4. **Settings → Domains**: add `wateratschooliscool.co.za` and `www.wateratschooliscool.co.za`. Vercel shows the DNS records to create.
5. At **Afrihost** (domain DNS), create the records Vercel shows: normally an **A** record for `@` → `76.76.21.21` and a **CNAME** for `www` → the value Vercel gives. Don't change MX/TXT records (email). Vercel issues the SSL certificate automatically once DNS is live.
6. On topcoolers.co.za, point the **Water@School is Cool** menu item at `https://wateratschooliscool.co.za/`.

## Checks before going live

- **Enquiry form:** submit it once on the live site and confirm the email reaches sales@topvending.co.za (subject: *Water@School is Cool: Free Assessment request from [school]*). If it fails, the visitor sees an error with the phone number, and the reason is in Vercel → Logs.
- **Offer strip:** the green "Install Now, Pay Nothing Until 2027!" strip removes itself automatically after 30 November 2026 (SAST).
- **Main menu links:** Why Us / Our Clients / Our Range must point at the real topcoolers.co.za pages.
- **Images:** the assets were cut from the mockup PDF. Replace them with the original files if you have them for sharper results.
