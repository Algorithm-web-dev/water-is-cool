/**
 * Water@School is Cool: lead collector.
 * Receives "Book A Free Assessment" requests from the Vercel site (api/enquire.js),
 * adds each one as a row in this Google Sheet, and emails it to the sales inbox.
 *
 * Setup: paste into Extensions > Apps Script of a Google Sheet, set SECRET,
 * then Deploy > New deployment > Web app (Execute as: Me, Who has access: Anyone).
 */

const SECRET = 'PASTE-THE-SAME-SECRET-AS-LEADS_WEBHOOK_SECRET-IN-VERCEL';
const NOTIFY_EMAIL = 'sales@topvending.co.za';
const SHEET_NAME = 'Leads';

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

  // Stops a value like "=HYPERLINK(...)" from being treated as a formula in the sheet
  const clean = (value) => {
    const text = String(value == null ? '' : value).slice(0, 200);
    return /^[=+\-@]/.test(text) ? "'" + text : text;
  };

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
    sheet.appendRow([new Date()].concat(FIELDS.map((f) => clean(data[f[0]]))));
  } finally {
    lock.releaseLock();
  }

  const lines = FIELDS.map((f) => f[1] + ': ' + String(data[f[0]] || ''));
  MailApp.sendEmail({
    to: NOTIFY_EMAIL,
    replyTo: String(data.email || ''),
    name: 'Water@School is Cool',
    subject: 'Water@School is Cool: Free Assessment request from ' + String(data.school_name || ''),
    body: 'New Book A Free Assessment request from the Water@School is Cool page:\n\n' + lines.join('\n') +
      '\n\nAll requests: ' + SpreadsheetApp.getActiveSpreadsheet().getUrl(),
  });

  return reply({ ok: true });
}

function reply(obj) {
  return ContentService.createTextOutput(JSON.stringify(obj)).setMimeType(ContentService.MimeType.JSON);
}
