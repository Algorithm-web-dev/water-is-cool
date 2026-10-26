<?php
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
<?php if ( time() < $wasc_offer_ends ) : ?>
<div class="wasc-offer-strip" id="wasc-offer-strip">
  <strong>Install Now, Pay Nothing Until 2027!</strong> <span class="wasc-offer-ends">Offer ends 30 November.</span><br>
  <a href="#book-assessment">Book A Free Assessment</a>
</div>
<?php endif; ?>

  <section class="wasc-hero">
    <div class="wasc-hero-grid">
      <div class="wasc-hero-logo">
        <img src="<?= $u ?>wasc-water-at-school-is-cool-logo.png" alt="Water@School is Cool" width="548" height="355">
        <p class="wasc-tagline">Changing the way children think about water</p>
      </div>
      <div class="wasc-hero-copy">
        <h1>Helping Children Create Healthy Drinking Habits</h1>
        <p class="wasc-full-copy">A Top Coolers initiative helping schools create daily healthy hydration habits, through premium hydration solutions that support wellbeing and sustainability.</p>
        <p class="wasc-short-copy">A Top Coolers initiative helping schools build healthy hydration habits.</p>
        <p class="wasc-cta-label">Bring Cool Water To Your School</p>
        <a class="wasc-btn-primary" href="#book-assessment">Book A Free Assessment</a>
      </div>
    </div>
  </section>

  <section class="wasc-section wasc-grey wasc-three-e">
    <div class="wasc-container">
      <h2 class="wasc-section-title">The 3 E’s Of Water@School is Cool</h2>
      <div class="wasc-e-cards">
        <article class="wasc-e-card wasc-c1" style="--accent: var(--navy)">
          <div class="wasc-num">01</div>
          <div class="wasc-e-img"><img src="<?= $u ?>wasc-wally-the-waterman.png" alt="Wally the Waterman" width="231" height="288"></div>
          <h3>EDUCATE</h3>
          <p class="wasc-e-sub">Healthy Habits Start Early</p>
          <p class="wasc-e-text">Teaching learners why drinking water matters, with Wally the Waterman showing them that water is cool.</p>
        </article>
        <article class="wasc-e-card wasc-c2" style="--accent: var(--mid-blue)">
          <div class="wasc-num">02</div>
          <div class="wasc-e-img"><img src="<?= $u ?>wasc-waterlogic-cooler.png" alt="Waterlogic water cooler" width="93" height="263"></div>
          <h3>EMPOWER</h3>
          <p class="wasc-e-sub">Waterlogic Water Coolers</p>
          <p class="wasc-e-text">Brand the water cooler with your school logo, so it becomes a hydration station learners own.</p>
        </article>
        <article class="wasc-e-card wasc-c3" style="--accent: var(--sky)">
          <div class="wasc-num">03</div>
          <div class="wasc-e-img"><img src="<?= $u ?>wasc-reusable-bottle.png" alt="Reusable water bottle" width="96" height="284"></div>
          <h3>ELIMINATE</h3>
          <p class="wasc-e-sub">Single-Use Plastic</p>
          <p class="wasc-e-text">A reusable bottle for every learner, cutting single-use plastic and keeping them drinking water all day.</p>
        </article>
      </div>
    </div>
  </section>

  <section class="wasc-section wasc-benefits">
    <div class="wasc-container">
      <h2 class="wasc-section-title">Benefits Of Drinking Water At School</h2>
      <p class="wasc-section-sub">Easy access to clean water supports learners all through the school day, in the classroom and on the sports field.</p>
      <div class="wasc-benefit-cards">
        <div class="wasc-benefit">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
          <h3>Better Concentration</h3>
          <p>Well-hydrated learners stay alert, focused and energised in class.</p>
        </div>
        <div class="wasc-benefit">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
          <h3>Healthier Learners</h3>
          <p>Water close by keeps growing bodies hydrated, and a hygienic cooler means fewer shared taps and bottles.</p>
        </div>
        <div class="wasc-benefit">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 3h14l-1.6 17.2a1 1 0 0 1-1 .8H7.6a1 1 0 0 1-1-.8z"/><path d="m2 2 20 20"/></svg>
          <h3>Fewer Sugary Drinks</h3>
          <p>When cool water is on hand, learners reach for it instead of fizzy and sugary drinks.</p>
        </div>
        <div class="wasc-benefit">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 20h10"/><path d="M10 20c5.5-2.5.8-6.4 3-10"/><path d="M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4 0 5.5.8z"/><path d="M14.1 6a7 7 0 0 0-1.1 4c1.9-.1 3.3-.6 4.3-1.4 1-1 1.6-2.3 1.7-4.6-2.7.1-4 1-4.9 2z"/></svg>
          <h3>Healthy Habits For Life</h3>
          <p>Habits built at school stay with children as they grow, making water their first choice.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="wasc-section wasc-grey wasc-how">
    <div class="wasc-container">
      <h2 class="wasc-section-title">How It Works</h2>
      <p class="wasc-section-sub">One team from start to finish, with no third parties involved.</p>
      <div class="wasc-how-grid">
        <div class="wasc-how-photo">
          <img src="<?= $u ?>wasc-technician.jpg" alt="A Top Coolers technician installing a plumbed-in water cooler" width="672" height="468">
        </div>
        <div class="wasc-steps">
          <div class="wasc-step">
            <div class="wasc-n">1</div>
            <div>
              <h3>Assessment</h3>
              <p>We visit your school to find the best places for your water coolers and check what’s needed for installation.</p>
            </div>
          </div>
          <div class="wasc-step">
            <div class="wasc-n">2</div>
            <div>
              <h3>Installation</h3>
              <p>Our own technicians install and connect your plumbed-in coolers, with minimal disruption to the school day.</p>
            </div>
          </div>
          <div class="wasc-step">
            <div class="wasc-n">3</div>
            <div>
              <h3>Ongoing Servicing</h3>
              <p>Regular servicing and maintenance by our team keep your coolers clean, safe and running.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="wasc-section wasc-trusted">
    <div class="wasc-container">
      <h2 class="wasc-section-title">Trusted By South African Businesses And Schools</h2>
      <p class="wasc-section-sub">Over 500 companies rely on us for their workplace hydration needs.</p>
      <div class="wasc-logos">
        <div class="wasc-logo-card wasc-fnb"><img src="<?= $u ?>wasc-client-fnb.png" alt="FNB" width="174" height="100"></div>
        <div class="wasc-logo-card wasc-rewardsco"><img src="<?= $u ?>wasc-client-rewardsco.png" alt="Rewardsco" width="132" height="132"></div>
        <div class="wasc-logo-card wasc-toyota"><img src="<?= $u ?>wasc-client-toyota.png" alt="Toyota" width="150" height="105"></div>
        <div class="wasc-logo-card wasc-talksure"><img src="<?= $u ?>wasc-client-talksure.png" alt="Talksure" width="119" height="121"></div>
        <div class="wasc-logo-card wasc-tsebo"><img src="<?= $u ?>wasc-client-tsebo.png" alt="Tsebo" width="322" height="64"></div>
      </div>
      <figure class="wasc-quote" style="margin-left:0;margin-right:0">
        <svg viewBox="0 0 40 34" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3C7 4 3 9 3 17v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2H3"/><path d="M36 3c-7 1-11 6-11 14v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2H25"/></svg>
        <blockquote>We have found their service to be professional, reliable, and consistently of a high standard.</blockquote>
        <figcaption class="wasc-attr"><strong>Redhill School, Sandton</strong><span class="wasc-sep"> · </span><span class="wasc-since">Top Coolers client since 2016</span></figcaption>
      </figure>
    </div>
  </section>

  <section class="wasc-section wasc-grey wasc-enquire" id="book-assessment">
    <div class="wasc-container">
      <h2 class="wasc-section-title">Book A Free Assessment</h2>
      <p class="wasc-tc-note">T&amp;C apply</p>
      <div class="wasc-enquire-grid">
        <div class="wasc-features">
          <div class="wasc-feature">
            <h3><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="m8.5 12 2.5 2.5 4.5-5"/></svg>Service &amp; Maintenance Included</h3>
            <p>Enjoy hassle-free hydration with full service and maintenance included, ensuring your mains-fed cooler runs smoothly.</p>
          </div>
          <div class="wasc-feature">
            <h3><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="m8.5 12 2.5 2.5 4.5-5"/></svg>Dedicated Customer Support</h3>
            <p>Our team is ready to assist with any issues. From troubleshooting to routine maintenance we’ll keep your cooler performing at its best.</p>
          </div>
          <div class="wasc-feature">
            <h3><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="m8.5 12 2.5 2.5 4.5-5"/></svg>Water Dispensers For Schools</h3>
            <p>Premium water dispensers for schools — available in freestanding and countertop models.</p>
          </div>
          <div class="wasc-feature">
            <h3><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="m8.5 12 2.5 2.5 4.5-5"/></svg>Hydrate Now</h3>
            <p>Enjoy premium mains-fed coolers with hassle-free rental and full service included. Fill out the form for your free quote and start saving today.</p>
          </div>
        </div>

        <div class="wasc-form-col">
          <?php if ( $wasc_sent ) : ?>
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
          <?php endif; ?>
          <p class="wasc-call-us">Call us now<br><a href="tel:0861100185">086 110 0185</a></p>
        </div>
      </div>
    </div>
  </section>

  <div class="wasc-terms">
    <h2>Terms &amp; Conditions</h2>
    <p>Applies to new rental agreements only, premises must be located within <a href="https://topvending.co.za/">Top Vending</a>’s serviceable area.<br>Standard <a href="https://topvending.co.za/">Top Vending</a> Rental Agreement terms &amp; conditions apply.</p>
  </div>
</div>
