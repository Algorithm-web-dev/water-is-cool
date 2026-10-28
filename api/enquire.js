// Vercel serverless function: delivers "Book A Free Assessment" requests.
// Two delivery routes, configured with Vercel environment variables (Project > Settings > Environment Variables).
// Set up either one, or both: the request succeeds when at least one route delivers it.
//
// 1. Google Sheet (google-apps-script/leads.gs): adds a row to the leads sheet and emails the sales inbox.
//   LEADS_WEBHOOK_URL      the Apps Script web app URL
//   LEADS_WEBHOOK_SECRET   the same secret as SECRET in leads.gs
//
// 2. SMTP email:
//   SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS   e.g. Gmail: smtp.gmail.com, 465, the address, an app password
//   SMTP_SECURE   optional, "true" for port 465 (default: true when SMTP_PORT is 465)
//   MAIL_FROM     optional, sender address (default: SMTP_USER)
//   MAIL_TO       optional, recipient (default: sales@topvending.co.za)
const nodemailer = require('nodemailer');

const LEARNERS = ['0-300', '301-700', '701-1 200', '1 200+'];
const FIELDS = {
  first_name: 'First Name',
  last_name: 'Last Name',
  email: 'Email',
  school_name: 'School Name',
  number_of_learners: 'Number of Learners',
  phone: 'Phone Number',
  postal_code: 'Postal Code',
};

function clean(value) {
  return String(value == null ? '' : value).replace(/[\r\n]+/g, ' ').trim().slice(0, 200);
}

function reply(req, res, status, error) {
  // fetch() from the page asks for JSON; a plain form post (no JavaScript) gets sent back to the page
  if ((req.headers.accept || '').includes('application/json')) {
    return res.status(status).json(error ? { ok: false, error } : { ok: true });
  }
  const query = error ? 'error=' + encodeURIComponent(error) : 'sent=1';
  res.statusCode = 303;
  res.setHeader('Location', '/?' + query + '#book-assessment');
  return res.end();
}

module.exports = async function handler(req, res) {
  if (req.method !== 'POST') {
    res.setHeader('Allow', 'POST');
    return res.status(405).json({ ok: false, error: 'Method not allowed.' });
  }

  const body = req.body && typeof req.body === 'object' ? req.body : {};
  if (body.wasc_website) return reply(req, res, 200); // spam bot filled the hidden field: pretend it worked

  const v = {};
  for (const key of Object.keys(FIELDS)) v[key] = clean(body[key]);

  if (Object.values(v).some((x) => !x)) return reply(req, res, 400, 'Please fill in all the fields.');
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.email)) return reply(req, res, 400, 'Please enter a valid email address.');
  if (!LEARNERS.includes(v.number_of_learners)) return reply(req, res, 400, 'Please choose the number of learners.');

  const routes = [];
  if (process.env.LEADS_WEBHOOK_URL) routes.push(['Google Sheet', sendToSheet]);
  if (process.env.SMTP_HOST && process.env.SMTP_USER && process.env.SMTP_PASS) routes.push(['SMTP', sendEmail]);
  const failMessage = 'Sorry, something went wrong sending your request. Please call us on 086 110 0185.';
  if (!routes.length) {
    console.error('Enquiry not sent: set LEADS_WEBHOOK_URL, or SMTP_HOST, SMTP_USER and SMTP_PASS, in Vercel.');
    return reply(req, res, 500, failMessage);
  }

  const results = await Promise.allSettled(routes.map(([, send]) => send(v)));
  results.forEach((r, i) => {
    if (r.status === 'rejected') console.error(`Enquiry not sent via ${routes[i][0]}:`, r.reason && r.reason.message);
  });
  if (!results.some((r) => r.status === 'fulfilled')) return reply(req, res, 502, failMessage);
  return reply(req, res, 200);
};

async function sendToSheet(v) {
  const response = await fetch(process.env.LEADS_WEBHOOK_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ secret: process.env.LEADS_WEBHOOK_SECRET || '', ...v }),
    redirect: 'follow',
  });
  const text = await response.text();
  let data = {};
  try { data = JSON.parse(text); } catch (e) { /* Apps Script returned HTML: usually a deployment or access problem */ }
  if (!response.ok || !data.ok) {
    throw new Error(`webhook answered ${response.status}: ${(data.error || text).slice(0, 200)}`);
  }
}

async function sendEmail(v) {
  const { SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS, SMTP_SECURE, MAIL_FROM, MAIL_TO } = process.env;
  const port = Number(SMTP_PORT) || 587;
  const transporter = nodemailer.createTransport({
    host: SMTP_HOST,
    port,
    secure: SMTP_SECURE ? SMTP_SECURE === 'true' : port === 465,
    auth: { user: SMTP_USER, pass: SMTP_PASS },
  });
  const lines = Object.entries(FIELDS).map(([key, label]) => `${label}: ${v[key]}`);
  await transporter.sendMail({
    from: MAIL_FROM || SMTP_USER,
    to: MAIL_TO || 'sales@topvending.co.za',
    replyTo: { name: `${v.first_name} ${v.last_name}`, address: v.email },
    subject: `Water@School is Cool: Free Assessment request from ${v.school_name}`,
    text: 'New Book A Free Assessment request from the Water@School is Cool page:\n\n' + lines.join('\n'),
  });
}
