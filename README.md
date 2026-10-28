# Water@School is Cool

Landing page for the Water@School is Cool schools campaign, a new tab on topcoolers.co.za. Built from *Water at School is Cool – Landing Page Final* (desktop and phone mockups).

It runs as a standalone site on Vercel at **wateratschooliscool.co.za** (the address on the banner and QR code). Its menu links back to topcoolers.co.za, and the Water@School is Cool tab on topcoolers.co.za links here.

- `index.html`: the page (HTML, CSS and a little JS). Work Sans from Google Fonts.
- `assets/`: logos, product images, technician photo and client logos.
- `api/enquire.js`: Vercel function that delivers "Book A Free Assessment" requests: to a Google Sheet (which also emails sales@topvending.co.za), and/or by SMTP email.
- `google-apps-script/leads.gs`: the Google Apps Script behind that Sheet.
- `bricks/`, `campaign-domain-redirect/`: the earlier WordPress/Bricks and redirect approach. Not used by the Vercel site (see `.vercelignore`).

## Deploying on Vercel

1. In Vercel, **Add New → Project** and import `Algorithm-web-dev/water-is-cool`. Framework preset: **Other**. No build command or output directory is needed.
2. Production branch: set it to the branch this work is on, or merge it into `main` first.
3. **Leads.** Set up at least one route in **Settings → Environment Variables** (Production), then redeploy. With both, the Sheet keeps a log and the email is a second copy.

   **Google Sheet (no mailbox password needed):**
   1. Create a Google Sheet (e.g. *Water@School is Cool – Leads*) in a Google account you control, then **Extensions → Apps Script**.
   2. Replace the code with `google-apps-script/leads.gs`, and set `SECRET` to a long random string. Save.
   3. **Deploy → New deployment →** type **Web app**, *Execute as:* **Me**, *Who has access:* **Anyone**. Authorise it, then copy the **Web app URL**.
   4. In Vercel: `LEADS_WEBHOOK_URL` = the Web app URL, `LEADS_WEBHOOK_SECRET` = the same secret.

   Each request becomes a row on the **Leads** tab and is emailed to sales@topvending.co.za from that Google account.

   **SMTP email (e.g. the topvending.webpage@gmail.com account with an app password):**

   | Name | Value |
   |---|---|
   | `SMTP_HOST` | `smtp.gmail.com` |
   | `SMTP_PORT` | `465` |
   | `SMTP_USER` | the Gmail address |
   | `SMTP_PASS` | a 16-character Google app password |
   | `MAIL_FROM` | optional, e.g. `Top Cooling <topvending.webpage@gmail.com>` |
   | `MAIL_TO` | optional, defaults to `sales@topvending.co.za` |

4. **Settings → Domains**: add `wateratschooliscool.co.za` and `www.wateratschooliscool.co.za`. Vercel shows the DNS records to create.
5. At **Afrihost** (domain DNS), create the records Vercel shows: normally an **A** record for `@` → `76.76.21.21` and a **CNAME** for `www` → the value Vercel gives. Don't change MX/TXT records (email). Vercel issues the SSL certificate automatically once DNS is live.
6. On topcoolers.co.za, point the **Water@School is Cool** menu item at `https://wateratschooliscool.co.za/`.

## Checks before going live

- **Enquiry form:** submit it once on the live site and confirm the email reaches sales@topvending.co.za (subject: *Water@School is Cool: Free Assessment request from [school]*), and that it appears in the Sheet if you use it. If a route fails, the reason is in Vercel → Logs (`Enquiry not sent via …`); the visitor only sees an error if every route failed.
- **Offer strip:** the green "Install Now, Pay Nothing Until 2027!" strip removes itself automatically after 30 November 2026 (SAST).
- **Main menu links:** the logo goes to https://topcoolers.co.za/; Why Us, Our Clients and Our Range go to `#why-us`, `#clients` and `#range` on the topcoolers.co.za homepage. Update them if those sections are renamed.
- **Images:** the assets were cut from the mockup PDF. Replace them with the original files if you have them for sharper results.
