/**
 * Google Apps Script — receives leads from api/book.php and appends them to the sheet.
 *
 * Setup:
 *  1. Create a Google Sheet. Open Extensions > Apps Script and paste this file.
 *  2. Change SECRET below to a long random string (same value goes in api/config.php).
 *  3. Deploy > New deployment > type "Web app"
 *       Execute as: Me      Who has access: Anyone
 *     Copy the Web app URL into api/config.php ('sheets' => 'url').
 *  4. After any later edit, use Deploy > Manage deployments > Edit > New version.
 */
var SECRET = 'CHANGE_ME_TO_A_LONG_RANDOM_STRING';
var SHEET_NAME = 'Leads';
// New columns are appended on the right, so existing sheets keep working (add the new headings by hand).
var HEADERS = ['Received', 'ID', 'Category', 'Check-in', 'Check-out', 'Guests', 'Phone', 'Page', 'IP',
  'Lead type', 'Name', 'Email', 'Message', 'Page path', 'Page group', 'Channel', 'Source', 'Medium', 'Campaign', 'First channel', 'First source'];

function doPost(e) {
  var data;
  try { data = JSON.parse(e.postData.contents); } catch (err) { return out({ ok: false, error: 'bad json' }); }
  if (data.secret !== SECRET) return out({ ok: false, error: 'unauthorized' });

  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = ss.getSheetByName(SHEET_NAME) || ss.insertSheet(SHEET_NAME);
  if (sheet.getLastRow() === 0) {
    sheet.appendRow(HEADERS);
    sheet.setFrozenRows(1);
  }
  // Phone as text so a leading zero / long number isn't mangled
  sheet.appendRow([
    new Date(), data.id, safe(data.category), safe(data.check_in), safe(data.check_out),
    safe(data.guests), "'" + safe(data.phone), safe(data.source_url), safe(data.ip),
    safe(data.lead_type), safe(data.name), safe(data.email), safe(data.message), safe(data.source_path), safe(data.page_group),
    safe(data.channel), safe(data.source), safe(data.medium), safe(data.campaign), safe(data.first_channel), safe(data.first_source)
  ]);
  return out({ ok: true });
}

// Neutralise spreadsheet formula injection: prefix cells starting with = + - @ tab or CR
function safe(v) {
  var s = (v === undefined || v === null) ? '' : String(v);
  return /^[=+\-@\t\r]/.test(s) ? "'" + s : s;
}

function out(obj) {
  return ContentService.createTextOutput(JSON.stringify(obj)).setMimeType(ContentService.MimeType.JSON);
}
