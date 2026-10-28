/**
 * Water@School is Cool: lead collector.
 * Receives "Book A Free Assessment" requests from the Vercel site (api/enquire.js),
 * adds each one as a row in this Google Sheet, emails it to the sales inbox,
 * and sends the person who filled in the form a confirmation email.
 *
 * Setup: paste into Extensions > Apps Script of a Google Sheet, set SECRET,
 * then Deploy > New deployment > Web app (Execute as: Me, Who has access: Anyone).
 * After any change: Deploy > Manage deployments > edit > Version: New version > Deploy.
 */

const SECRET = 'PASTE-THE-SAME-SECRET-AS-LEADS_WEBHOOK_SECRET-IN-VERCEL';
const NOTIFY_EMAIL = 'sales@topvending.co.za';
const SHEET_NAME = 'Leads';
const PHONE = '086 110 0185';
const OFFER_ENDS = new Date('2026-12-01T00:00:00+02:00'); // offer line leaves the confirmation email after 30 November 2026

const FIELDS = [
  ['first_name', 'First Name'],
  ['last_name', 'Last Name'],
  ['email', 'Email'],
  ['school_name', 'School Name'],
  ['number_of_learners', 'Number of Learners'],
  ['phone', 'Phone Number'],
  ['postal_code', 'Postal Code'],
];

function doPost(e) {
  let data;
  try {
    data = JSON.parse(e.postData.contents);
  } catch (err) {
    return reply({ ok: false, error: 'Invalid request' });
  }
  if (!SECRET || data.secret !== SECRET) return reply({ ok: false, error: 'Wrong secret' });

  const value = (key) => String(data[key] == null ? '' : data[key]).slice(0, 200);
  // The leading ' makes Sheets store every value as plain text: keeps the 0 in phone numbers and postal codes,
  // and stops a value like "=HYPERLINK(...)" from being treated as a formula. Sheets doesn't show the '.
  const asText = (key) => "'" + value(key);

  // 1. Save the lead. This is the record, so it happens first and must succeed.
  const lock = LockService.getScriptLock();
  lock.waitLock(10000);
  try {
    const book = SpreadsheetApp.getActiveSpreadsheet();
    const sheet = book.getSheetByName(SHEET_NAME) || book.insertSheet(SHEET_NAME);
    if (sheet.getLastRow() === 0) {
      sheet.appendRow(['Received'].concat(FIELDS.map((f) => f[1])));
      sheet.setFrozenRows(1);
      sheet.getRange(1, 1, 1, FIELDS.length + 1).setFontWeight('bold');
    }
    sheet.appendRow([new Date()].concat(FIELDS.map((f) => asText(f[0]))));
  } finally {
    lock.releaseLock();
  }

  // 2. Tell sales. 3. Confirm to the school. A failed email is logged (Apps Script > Executions) but the lead is already saved.
  try {
    const lines = FIELDS.map((f) => f[1] + ': ' + value(f[0]));
    MailApp.sendEmail({
      to: NOTIFY_EMAIL,
      replyTo: value('email'),
      name: 'Water@School is Cool',
      subject: 'Water@School is Cool: Free Assessment request from ' + value('school_name'),
      body: 'New Book A Free Assessment request from the Water@School is Cool page:\n\n' + lines.join('\n') +
        '\n\nAll requests: ' + SpreadsheetApp.getActiveSpreadsheet().getUrl(),
    });
  } catch (err) {
    console.error('Sales email not sent: ' + err.message);
  }

  try {
    if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value('email'))) sendConfirmation(value);
  } catch (err) {
    console.error('Confirmation email not sent: ' + err.message);
  }

  return reply({ ok: true });
}

function sendConfirmation(value) {
  const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  const offer = new Date() < OFFER_ENDS;
  const details = [
    ['School', value('school_name')],
    ['Number of learners', value('number_of_learners')],
    ['Phone', value('phone')],
    ['Postal code', value('postal_code')],
  ];

  const text =
    'Hi ' + value('first_name') + ',\n\n' +
    'Thank you for booking a free Water@School is Cool assessment for ' + value('school_name') + '.\n' +
    'Our team will be in touch shortly to arrange a time to visit your school.\n\n' +
    'Your details:\n' + details.map((d) => d[0] + ': ' + d[1]).join('\n') + '\n\n' +
    (offer ? 'Install now, pay nothing until 2027! Offer ends 30 November 2026. T&C apply.\n\n' : '') +
    'Questions? Reply to this email or call us on ' + PHONE + '.\n\n' +
    'Kind regards,\nThe Top Coolers team\nhttps://wateratschooliscool.co.za';

  const html =
    '<div style="font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.6;color:#363636;max-width:560px">' +
    '<h2 style="color:#1C618E;font-size:22px;margin:0 0 16px">Thank you for your request</h2>' +
    '<p style="margin:0 0 12px">Hi ' + esc(value('first_name')) + ',</p>' +
    '<p style="margin:0 0 12px">Thank you for booking a free <strong>Water@School is Cool</strong> assessment for ' +
    '<strong>' + esc(value('school_name')) + '</strong>. Our team will be in touch shortly to arrange a time to visit your school.</p>' +
    '<table style="border-collapse:collapse;margin:16px 0;font-size:14px">' +
    details.map((d) => '<tr><td style="padding:4px 16px 4px 0;color:#616161">' + esc(d[0]) + '</td>' +
      '<td style="padding:4px 0;font-weight:bold">' + esc(d[1]) + '</td></tr>').join('') +
    '</table>' +
    (offer ? '<p style="background:#C9F5D3;color:#0B5D33;padding:12px 16px;border-radius:8px;margin:0 0 16px">' +
      '<strong>Install now, pay nothing until 2027!</strong> Offer ends 30 November 2026. T&amp;C apply.</p>' : '') +
    '<p style="margin:0 0 12px">Questions? Reply to this email or call us on <a href="tel:0861100185" style="color:#1C618E">' + PHONE + '</a>.</p>' +
    '<p style="margin:0">Kind regards,<br>The Top Coolers team<br>' +
    '<a href="https://wateratschooliscool.co.za" style="color:#1C618E">wateratschooliscool.co.za</a></p>' +
    '</div>';

  MailApp.sendEmail({
    to: value('email'),
    replyTo: NOTIFY_EMAIL, // replies go to the sales team, not the account running this script
    name: 'Top Coolers',
    subject: 'Thank you for booking a free assessment – Water@School is Cool',
    body: text,
    htmlBody: html,
  });
}

function reply(obj) {
  return ContentService.createTextOutput(JSON.stringify(obj)).setMimeType(ContentService.MimeType.JSON);
}
