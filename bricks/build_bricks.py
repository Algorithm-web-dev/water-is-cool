import re, os, shutil, sys
repo = sys.argv[1]
src = open(os.path.join(repo, 'index.html'), encoding='utf-8').read()
out = os.path.join(repo, 'bricks'); os.makedirs(os.path.join(out, 'images'), exist_ok=True)

# ---------- images: copy with a wasc- prefix so they never clash in the media library ----------
for f in os.listdir(os.path.join(repo, 'assets')):
    if f == 'top-coolers-logo.png':
        continue  # the site header already has the logo
    shutil.copy(os.path.join(repo, 'assets', f), os.path.join(out, 'images', 'wasc-' + f))

DROP = ('site-header', 'header-inner', 'brand', 'main-nav', 'btn-enquire', 'menu-toggle', 'copyright', 'nav-link')

# ---------- CSS ----------
css = re.search(r'<style>(.*?)</style>', src, re.S).group(1)
classes = set()
for m in re.finditer(r'class="([^"]+)"', src):
    classes.update(m.group(1).split())
classes -= set(DROP) | {'active', 'at'}
cls_re = re.compile(r'\.(' + '|'.join(sorted(map(re.escape, classes), key=len, reverse=True)) + r')(?![\w-])')

def conv_rules(block):
    res = []
    for m in re.finditer(r'([^{}]+)\{([^{}]*)\}', block):
        sel, body = m.group(1).strip(), m.group(2)
        sel = re.sub(r'/\*.*?\*/', '', sel, flags=re.S).strip()
        if any(d in sel for d in DROP) or sel in ('html', 'body'):
            continue
        if sel == ':root':
            sel = '.wasc'
        elif sel == '*':
            sel = '.wasc, .wasc *, .wasc *::before, .wasc *::after'
        else:
            parts = []
            for s in sel.split(','):
                s = cls_re.sub(lambda mm: '.wasc-' + mm.group(1), s.strip())
                parts.append('.wasc ' + s)
            sel = ', '.join(parts)
        body = re.sub(r'/\*.*?\*/', '', body)
        body = re.sub(r'\s+', ' ', body).strip()
        res.append((sel, body))
    return res

def fmt(rules, ind=''):
    return '\n'.join(f'{ind}{s} {{ {b} }}' for s, b in rules if b)

pieces, pos = [], 0
for m in re.finditer(r'@media([^{]+)\{((?:[^{}]*\{[^{}]*\})*)\s*\}', css):
    pieces.append(fmt(conv_rules(css[pos:m.start()])))
    pieces.append('@media' + m.group(1).rstrip() + ' {\n' + fmt(conv_rules(m.group(2)), '  ') + '\n}')
    pos = m.end()
pieces.append(fmt(conv_rules(css[pos:])))
base = """@import url('https://fonts.googleapis.com/css2?family=Work+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&display=swap');

/* Water@School is Cool landing page. Everything is scoped to .wasc so it can't affect the rest of the site. */
.wasc { font-family: 'Work Sans', Arial, sans-serif; font-size: 15px; line-height: 1.8; color: #363636; background: #fff; -webkit-font-smoothing: antialiased; width: 100%; }
/* Neutralise theme typography inside the page */
.wasc h1, .wasc h2, .wasc h3, .wasc p, .wasc blockquote, .wasc figure { font-family: inherit; text-transform: none; letter-spacing: normal; padding: 0; border: 0; }
.wasc h1::before, .wasc h2::before, .wasc h3::before, .wasc blockquote::before, .wasc blockquote::after { content: none; }
.wasc blockquote { background: none; quotes: none; }
.wasc .wasc-terms h2 { color: #fff; }
.wasc .wasc-quote blockquote { color: #363636; }
.wasc a:hover { opacity: 1; }
.wasc #book-assessment { scroll-margin-top: 110px; }
.wasc .wasc-form-error { grid-column: 1 / -1; margin: 0; color: #b3261e; font-weight: 500; text-align: center; }
.wasc .wasc-hp { position: absolute !important; left: -9999px !important; width: 1px; height: 1px; overflow: hidden; }
.wasc .wasc-thanks { background: #fff; border-radius: 14px; padding: 36px 28px; text-align: center; }
.wasc .wasc-thanks h3 { font-size: 22px; font-weight: 700; color: #1C618E; line-height: 1.3; }
.wasc .wasc-thanks p { margin-top: 10px; }
"""
css_out = base + '\n' + '\n\n'.join(p for p in pieces if p.strip()) + '\n'
# the short-copy rule must not be overridden by the theme; and the phone rule below re-shows it
open(os.path.join(out, '2-css.css'), 'w', encoding='utf-8').write(css_out)

# ---------- HTML / PHP ----------
body = src[src.index('<div class="offer-strip"'):src.index('<div class="copyright">')]
body = body.replace('<main>\n', '').replace('</main>\n', '').replace('<footer>\n', '')
body = re.sub(r'\s*<!--.*?-->', '', body, flags=re.S)
body = body.rstrip()
assert body.endswith('</div>')
def rename(m):
    toks = [t if t in DROP else 'wasc-' + t for t in m.group(1).split()]
    return 'class="' + ' '.join(toks) + '"'
body = re.sub(r'class="([^"]+)"', rename, body)
body = body.replace('id="offer-strip"', 'id="wasc-offer-strip"')
body = re.sub(r'src="assets/([^"]+)"', r'src="<?= $u ?>wasc-\1"', body)

# offer strip: server-side expiry
body = body.replace('<div class="wasc-offer-strip" id="wasc-offer-strip">',
                    '<?php if ( time() < $wasc_offer_ends ) : ?>\n<div class="wasc-offer-strip" id="wasc-offer-strip">', 1)
i = body.index('id="wasc-offer-strip"'); j = body.index('</div>', i) + len('</div>')
body = body[:j] + '\n<?php endif; ?>' + body[j:]

# replace the static form with the working PHP form
fs = body.index('<form'); fe = body.index('</form>') + len('</form>')
form = '''<?php if ( $wasc_sent ) : ?>
          <div class="wasc-thanks" role="status">
            <h3>Thank you!</h3>
            <p>Our team will be in touch to book your free assessment.</p>
          </div>
          <?php else : ?>
          <form class="wasc-enquiry-form" method="post" action="#book-assessment">
            <?php if ( $wasc_error ) : ?><p class="wasc-form-error" role="alert"><?= esc_html( $wasc_error ) ?></p><?php endif; ?>
            <input type="hidden" name="wasc_enquiry" value="1">
            <div class="wasc-hp" aria-hidden="true"><label>Leave this empty <input type="text" name="wasc_website" tabindex="-1" autocomplete="off"></label></div>
            <input type="text" name="first_name" value="<?= esc_attr( $v['first_name'] ) ?>" placeholder="First Name *" aria-label="First Name" autocomplete="given-name" required>
            <input type="text" name="last_name" value="<?= esc_attr( $v['last_name'] ) ?>" placeholder="Last Name *" aria-label="Last Name" autocomplete="family-name" required>
            <input class="wasc-full" type="email" name="email" value="<?= esc_attr( $v['email'] ) ?>" placeholder="Your Email *" aria-label="Your Email" autocomplete="email" required>
            <input class="wasc-full" type="text" name="school_name" value="<?= esc_attr( $v['school_name'] ) ?>" placeholder="School Name *" aria-label="School Name" autocomplete="organization" required>
            <select class="wasc-full" name="number_of_learners" aria-label="Number of Learners" required>
              <option value="" <?= $v['number_of_learners'] === '' ? 'selected' : '' ?> disabled>Number of Learners *</option>
              <?php foreach ( $wasc_learners as $opt ) : ?>
              <option value="<?= esc_attr( $opt ) ?>" <?= selected( $v['number_of_learners'], $opt, false ) ?>><?= esc_html( $opt ) ?></option>
              <?php endforeach; ?>
            </select>
            <input type="tel" name="phone" value="<?= esc_attr( $v['phone'] ) ?>" placeholder="Phone Number *" aria-label="Phone Number" autocomplete="tel" required>
            <input type="text" name="postal_code" value="<?= esc_attr( $v['postal_code'] ) ?>" placeholder="Postal Code *" aria-label="Postal Code" autocomplete="postal-code" inputmode="numeric" required>
            <button class="wasc-btn-submit" type="submit">Submit</button>
          </form>
          <?php endif; ?>'''
body = body[:fs] + form + body[fe:]

php = r'''<?php
/* ---------- SETTINGS: check these two before publishing ---------- */
// Folder the wasc-*.png / .jpg images were uploaded to (Media Library puts them in /uploads/YEAR/MONTH/).
$wasc_img_folder = '/wp-content/uploads/2026/10/';
// Who receives the "Book A Free Assessment" enquiries (default: the site admin email).
$wasc_to = get_option( 'admin_email' );
/* ----------------------------------------------------------------- */

$u               = esc_url( $wasc_img_folder );
$wasc_offer_ends = strtotime( '2026-12-01 00:00:00 +02:00' ); // green offer strip disappears after 30 November 2026
$wasc_learners   = array( '0-300', '301-700', '701-1 200', '1 200+' );
$wasc_fields     = array(
	'first_name'         => 'First Name',
	'last_name'          => 'Last Name',
	'email'              => 'Email',
	'school_name'        => 'School Name',
	'number_of_learners' => 'Number of Learners',
	'phone'              => 'Phone Number',
	'postal_code'        => 'Postal Code',
);
$v          = array_fill_keys( array_keys( $wasc_fields ), '' );
$wasc_sent  = false;
$wasc_error = '';

if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['wasc_enquiry'] ) ) {
	foreach ( $wasc_fields as $key => $label ) {
		$v[ $key ] = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
	}
	if ( ! empty( $_POST['wasc_website'] ) ) {
		$wasc_sent = true; // spam bot filled the hidden field: pretend it worked, send nothing
	} elseif ( in_array( '', $v, true ) ) {
		$wasc_error = 'Please fill in all the fields.';
	} elseif ( ! is_email( $v['email'] ) ) {
		$wasc_error = 'Please enter a valid email address.';
	} elseif ( ! in_array( $v['number_of_learners'], $wasc_learners, true ) ) {
		$wasc_error = 'Please choose the number of learners.';
	} elseif ( ! empty( $GLOBALS['wasc_mail_done'] ) ) {
		$wasc_sent = true; // element rendered twice in one request: only send once
	} else {
		$lines = array();
		foreach ( $wasc_fields as $key => $label ) {
			$lines[] = $label . ': ' . $v[ $key ];
		}
		$wasc_sent = wp_mail(
			$wasc_to,
			'Water@School is Cool: Free Assessment request from ' . $v['school_name'],
			"New Book A Free Assessment request from the Water@School is Cool page:\n\n" . implode( "\n", $lines ),
			array( 'Reply-To: ' . $v['first_name'] . ' ' . $v['last_name'] . ' <' . $v['email'] . '>' )
		);
		$GLOBALS['wasc_mail_done'] = $wasc_sent;
		if ( ! $wasc_sent ) {
			$wasc_error = 'Sorry, something went wrong sending your request. Please call us on 086 110 0185.';
		}
	}
}
?>
<div class="wasc">
'''
html_out = php + body + '\n</div>\n'
open(os.path.join(out, '1-php-html.php'), 'w', encoding='utf-8').write(html_out)

js = """// Hides the green offer strip after 30 November 2026 (SA time), even if the page is served from cache.
document.addEventListener('DOMContentLoaded', function () {
  if (Date.now() >= Date.parse('2026-12-01T00:00:00+02:00')) {
    var strip = document.getElementById('wasc-offer-strip');
    if (strip) strip.remove();
  }
});
"""
open(os.path.join(out, '3-javascript.js'), 'w', encoding='utf-8').write(js)
print('ok')
