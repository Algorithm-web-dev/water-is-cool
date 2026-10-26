# Building the page in Bricks

Everything goes into a single Bricks **Code** element on a new WordPress page. The site's own header and footer stay as they are.

| File | Where it goes |
|---|---|
| `1-php-html.php` | Code element → **PHP & HTML** panel |
| `2-css.css` | Code element → **CSS** panel |
| `3-javascript.js` | Code element → **JavaScript** panel |
| `4-site-wide-menu-css.css` | Bricks → Settings → Custom code → **Custom CSS** |
| `images/` | WordPress **Media Library** |

## 1. Upload the images
1. In WordPress go to **Media → Add New** and upload all 10 files from `images/` (they all start with `wasc-`).
2. Open one of them and copy its **File URL**, e.g. `https://topcoolers.co.za/wp-content/uploads/2026/10/wasc-technician.jpg`.
3. The folder part (`/wp-content/uploads/2026/10/`) goes into `$wasc_img_folder` at the very top of `1-php-html.php`. If you upload in November it will be `/2026/11/`.

## 2. Create the page
1. **Pages → Add New**, title `Water@School is Cool`, and set the slug (permalink) to `wateratschool`.
2. Click **Edit with Bricks**.

## 3. Add the Code element
1. Add a **Code** element. Put it straight on the page (not inside a Section) if you can.
   If Bricks puts it inside a Section/Container: give the Section **padding 0**, and the Container **width 100%**, **max-width none**, **padding 0**. Otherwise the coloured backgrounds won't run edge to edge.
2. Paste the three files into the matching panels (PHP & HTML, CSS, JavaScript).
3. Check the two settings at the top of the PHP & HTML panel:
   - `$wasc_img_folder`: the uploads folder from step 1.
   - `$wasc_to`: the email address that receives the enquiries. It's set to `sales@topvending.co.za`, the same inbox as the Enquire Now form.
4. Turn on **Execute code**. The form and images need PHP.
   - If the toggle is greyed out, enable it under **Bricks → Settings → Custom code → Code execution** (administrators).
   - Newer Bricks versions also ask you to **sign** the code. Use the sign button on the element, or **Bricks → Settings → Custom code → Code signatures**. Sign again after every edit.
5. Save and view the page.

## 4. Add the menu tab
1. **Appearance → Menus**, add the new page to the main menu.
2. Set its **Navigation Label** to: `Water<span class="wasc-nav-at">@</span>School is Cool`
3. Under **Screen Options** (top right), tick **CSS Classes**, then give the menu item the class `menu-wasc`.
4. Paste `4-site-wide-menu-css.css` into **Bricks → Settings → Custom code → Custom CSS**.

## 5. Test
- Submit the form once and check the email arrives at sales@topvending.co.za. It goes through WP Mail SMTP like the other forms, and shows in **WP Mail Logging → Email Log** with the subject *Water@School is Cool: Free Assessment request from [school]*. These enquiries arrive by email only; they don't appear under **Bricks → Form submissions**.
- Click every **Book A Free Assessment** link (green strip, hero button): they should jump to the form.
- Check the page on a phone.

## Notes
- The green offer strip disappears by itself after **30 November 2026**.
- The Terms & Conditions block is part of the page. The site footer supplies the © copyright bar, so it isn't included here.
- `wateratschooliscool.co.za` should 301-redirect to `https://topcoolers.co.za/wateratschool/` (see `../campaign-domain-redirect/.htaccess`).
- These files are generated from `../index.html` by `build_bricks.py` (`python3 build_bricks.py ..`). Edit `index.html` and re-run it rather than editing the files by hand.
