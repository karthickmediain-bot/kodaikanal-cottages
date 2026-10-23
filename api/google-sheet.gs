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
var HEADERS = ['Received', 'ID', 'Category', 'Check-in', 'Check-out', 'Guests', 'Phone', 'Page', 'IP'];

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
    new Date(), data.id, data.category, data.check_in || '', data.check_out || '',
    data.guests || '', "'" + data.phone, data.source_url || '', data.ip || ''
  ]);
  return out({ ok: true });
}

function out(obj) {
  return ContentService.createTextOutput(JSON.stringify(obj)).setMimeType(ContentService.MimeType.JSON);
}
