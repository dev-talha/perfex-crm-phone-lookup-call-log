# Changelog
## Unified Phone Search, MicroSIP Calling & Call Log Module for Perfex CRM

This changelog summarizes the development history from version **1.0** to **1.17**.

---

## Version 1.0.0 - Initial Professional Module

### Added
- Created the first professional Perfex CRM module structure.
- Added unified phone search page.
- Added Bangladesh phone number normalization.
- Added URL-based phone search support.
- Added CRM search support for customers, contacts, and leads.
- Added Unichat API integration support.
- Added Add Call Log feature.
- Added basic call log list page.
- Added reports page.
- Added settings page.
- Added database tables for:
  - `tblunified_call_logs`
  - `tblunified_call_outcomes`
  - `tblunified_phone_cache`
- Added role-based permissions.
- Added sidebar menu under Perfex CRM.

---

## Version 1.1.0 - Perfex Codebase Compatibility Improvements

### Improved
- Updated module to better follow real Perfex CRM conventions.
- Used standard Perfex module hooks.
- Improved controller/model structure.
- Added better permissions registration.
- Added customer and lead tab integration attempt.
- Improved search page structure.
- Added README and installation notes.

---

## Version 1.2.0 - Settings Route and Add Call Log Fixes

### Fixed
- Fixed broken settings page route.
- Added working settings URL: `/admin/unified_phone/settings`.
- Added compatibility route for older settings path.

### Changed
- Updated Add Call Log form fields:
  - Date field changed to date only.
  - Start time changed to time only.
  - End time changed to time only.
- Set default call type to Incoming.
- Start time now sets when search page loads.
- End time now sets when user saves/adds call log.

### Improved
- Removed Unichat as a call log relation option.
- Related To changed to CRM-only records:
  - Project
  - Invoice
  - Customer
  - Estimate
  - Contract
  - Ticket
  - Expense
  - Lead
  - Proposal
- Added searchable Related ID dropdown.
- Added lead call log tab support.

---

## Version 1.3.0 - Settings Save, Menu, and Relation Fixes

### Fixed
- Fixed settings radio button save issue.
- Yes/No settings now save correctly in `tbloptions`.
- Fixed Related To and Related ID AJAX loading issue.
- Fixed Lead relation selection issue.

### Added
- Improved sidebar menu and submenu structure.
- Added better admin/staff menu visibility.
- Added Call Outcomes management basics.

### Changed
- Removed manual Unichat Inbox ID field.
- Unichat inboxes are now discovered from configured account.
- Removed follow-up fields temporarily from Add Call Log.

### Documented
- Confirmed settings are saved in Perfex CRM `tbloptions` table.

---

## Version 1.4.0 - Unichat Display and Previous Call Log Pagination

### Added
- Added role-based permission for viewing call log details.
- Added call log details page.
- Added View action in call logs options column.

### Improved
- Unichat section now shows only when Unichat integration is enabled.
- Unichat conversations limited to latest 10 records.
- Unichat timestamp now displays as readable date/time.
- Previous Call Logs section shows latest call history with pagination.
- Previous Call Logs limited to latest 100 matched records.
- Previous Call Logs display 20 records per page.

---

## Version 1.5.0 - Search Result Sections and UI Improvements

### Added
- Added missing CRM search result sections:
  - Payments
  - Projects
  - Contracts
  - Expenses
  - Tasks
  - Notes
- Added settings to control which search result sections are visible.
- Added settings to control result limit per section.

### Improved
- Improved search result page UI.
- Added cleaner summary cards.
- Added count badges per section.
- Improved customer/contact/lead display cards.
- Fixed multiple missing language strings.

---

## Version 1.6.0 - Report Filter and Call Log Navigation Cleanup

### Removed
- Removed unstable search sections:
  - Expenses
  - Tasks
  - Notes

### Added
- Added report filters:
  - Outcome
  - Related To
  - Staff
- Added follow-up fields back to Add Call Log:
  - Follow-up Required
  - Follow-up Date/Time

### Changed
- Removed separate Call Logs menu from sidebar.
- Redirected old Call Logs page to Reports page.
- Updated Call Log detail/edit back links to Reports.
- Removed Calls by Outcome block from reports.
- Removed Calls by Staff block from reports.
- End Time now defaults to current time.

### Improved
- Added clickable Unichat summary card at the top of the search result page when enabled.

---

## Version 1.7.0 - Modal UI, Language, Date Format, and Lead Tab Update

### Improved
- Updated Add Call Log modal layout.
- Updated Edit Call Log page layout.
- Improved related CRM searchable dropdown.

### Fixed
- Fixed missing language labels across the module.
- Fixed Call Details View page Back button.
- Back button now uses `onclick="history.back();"`.
- Fixed lead details Call Log tab logic.

### Changed
- All module date displays changed to Day-Month-Year format.
- Lead tab name changed to only `Call Log`, without icon.

### Added
- Added Reset Filter button in Reports page.

---

## Version 1.8.0 - Lead Call Log Tab and Related Links

### Fixed
- Fixed Lead Details modal Call Log tab rendering again.
- Fixed language fallback for tab text.

### Removed
- Removed `latest configured limit` text from search page sections.

### Added
- Added links to call log table values:
  - Call date links to call log details.
  - Phone number links to phone search page.
  - Related CRM record links to related CRM detail page.
  - Staff name links to staff profile.
- Added clickable related record value in Call Log Details page.

---

## Version 1.9.0 - Stronger Lead Modal Support and Statistic Update

### Fixed
- Improved Lead Details modal Call Log tab using Perfex hooks.
- Added fallback script to recreate/fix the tab if missing.
- Added AJAX endpoint for lead call logs by lead phone number:
  - `/admin/unified_phone/lead_call_logs_tab/{lead_id}`

### Changed
- Search page statistics updated.
- Removed Contacts statistic card.
- Added Previous Call Logs count statistic.
- Previous Call Logs statistic card is clickable and jumps to the Previous Call Logs section.

---

## Version 1.10.0 - Global Lead Modal Fallback

### Fixed
- Added stronger global Lead modal fallback script.
- Script checks the lead modal after load and injects the Call Log tab if missing.
- Script runs after:
  - Lead modal opens.
  - Lead AJAX data loads.
  - Lead modal DOM changes.

### Improved
- Lead Call Log tab displays as plain text: `Call Log`.
- No icon or extra text in the tab.

---

## Version 1.11.0 - Call Outcomes Edit and Delete

### Added
- Added Edit button for Call Outcomes in settings.
- Added Delete button for Call Outcomes in settings.
- Added edit modal for Call Outcomes.

### Improved
- Call Outcome edit supports:
  - Name
  - Color
  - Sort order
  - Default status
  - Active status
- When one outcome is default, other outcomes are automatically unset.
- If default outcome is deleted, default option is cleared.

---

## Version 1.12.0 - Outcome Icon and Follow-up Column

### Changed
- Updated Call Outcomes edit icon to use:
  - `fa-pen-to-square`

### Added
- Added Follow-up Date/Time column after Outcome in call log tables.
- If follow-up date/time is empty, system displays `-`.

---

## Version 1.13.0 - URL Call Type, Required Fields, and Follow-up Filter

### Added
- Added URL-based call type support:
  - `calltype=incoming`
  - `calltype=outgoing`
- Added admin settings for required Add/Edit Call Log fields.
- Added backend validation for required fields.
- Added Follow-up From and Follow-up To filters on Reports page.

### Changed
- If `calltype` parameter is missing, Call Type remains unselected.
- Removed Follow-up Required checkbox from Add/Edit Call Log form.
- Follow-up status is now automatically based on Follow-up Date/Time.
- Removed Follow-up Required filter from Reports page.

---

## Version 1.14.0 - Call By SIP, Recording Upload, and Global Click-to-Call

### Added
- Added new Call By SIP page under Phone Lookup menu.
- Added SIP dialing support for MicroSIP.
- Added configurable SIP schemes:
  - `sip:`
  - `tel:`
  - `callto:`
- Added automatic Add Call Log modal after initiating SIP call.
- Added recording upload support in Add/Edit Call Log.
- Added recording display on Call Log Details page.
- Added Global CRM Click-to-Call support.
- Added URL parameter auto-fill support for:
  - Phone number
  - Call type
  - Date
  - Start time
  - Related To
  - Related ID

### Settings Added
- Enable/disable SIP calling.
- Enable/disable Global CRM Click-to-Call.
- SIP URL scheme.
- Enable/disable recording upload.
- Allowed recording file types.
- Maximum recording file size.
- Field show/hide settings.

### Improved
- Settings page reorganized into tab-wise layout:
  - General
  - Search Result Sections
  - Call Log Form
  - SIP & Recordings
  - Unichat/Unichat
  - Permissions

---

## Version 1.15.0 - Auto-fill, SIP UI, Settings Language, and Upload Fixes

### Fixed
- Fixed Related To and Related ID auto-fill for global click-to-call from common CRM pages:
  - Lead
  - Proposal
  - Invoice
  - Estimate
  - Customer
  - Project
  - Contract
  - Ticket
- Fixed call timing rules:
  - Incoming: Start Time = page load time, End Time = current time.
  - Outgoing or empty call type: Start Time and End Time = current time.
- Fixed recording upload size error handling.
- Fixed multiple Settings page language issues.

### Improved
- Rebuilt Call By SIP page UI.
- Added compact dial pad.
- Added keyboard input and paste support.
- Moved instructions into an info/collapsible section.
- Replaced Settings page `Unichat` text with `Unichat`.
- Added API Timeout label with seconds unit.
- Made Call Outcome color visible as badges in tables/details.

### General Settings Fixes
- Like Search setting now affects search behavior.
- Phone Normalization setting now affects normalization/search keys.
- Edit After Save setting now controls edit access.

---

## Version 1.16.0 - List Page Click-to-Call and Recording Permissions

### Fixed
- Fixed Related To and Related ID auto-fill from list pages:
  - `/admin/clients`
  - `/admin/leads`
- Fixed SIP dial pad 0 button issue.
- Removed dialer input placeholder.

### Improved
- `.unified-sip-card` maximum width set to `430px`.
- Added better relation detection from table rows.

### Security Added
- Added role-based permission for call recording access.
- Added capability: `Access Call Recordings`.
- Only permitted staff can upload, view, play, or download recordings.
- Recording playback/download now goes through permission-checked controller route.

---

## Version 1.17.0 - Final UI, Recording Label, Security and Performance Pass

### Fixed
- Fixed Options column UI issue in call log tables.
- View/Edit/Delete buttons now stay aligned in one clean row.

### Added
- Added dynamic Call Recording help label in Add Call Log modal.
- Info label displays allowed file types and max size from settings.
- Example:
  - `Allowed: mp3,wav. Max size: 1000 KB.`

### Security Improvements
- Recording files are served only through permission-checked controller route.
- Added recording path validation to prevent unsafe file access.
- Added file extension validation after upload.
- Sanitized original recording filenames.
- Added `X-Content-Type-Options: nosniff` for recording response.
- Added private/no-cache headers for recording streaming.
- Added `.htaccess` and `index.html` protection in recording upload folders.
- Old recording file is removed when a new recording is uploaded.
- Recording file is removed when a call log is deleted.

### Performance Improvements
- Preserved result limits for search sections.
- Preserved previous call log pagination.
- Avoided heavy global frontend loading.
- Maintained indexed call-log lookup pattern.
- Kept search and report queries limited and controlled by settings.

---

# Final Stable Version

The final completed version is:

**v1.17.0 - Unified Phone Search, MicroSIP Calling, Call Log, Recording, and Reporting Module**

This version includes:

- Unified phone lookup
- Bangladesh phone normalization
- Incoming call auto lookup with MicroSIP
- Call By SIP page
- Global CRM click-to-call
- Add/Edit/View call logs
- Previous call history
- CRM relation linking
- Follow-up tracking
- Recording upload and secure playback
- Role-based permissions
- Unichat integration
- Advanced reports
- Export support
- Tab-wise settings
- Admin customizable fields, limits, permissions, sections, SIP, recording, and API settings

---

## [1.18.0] - Staff UI Fix, Payments Removal & Lead Call Log Improvements

### Fixed
- Fixed employee/staff UI issue where module CSS/JS loaded only for administrator users.
- Corrected asset loading logic in:
  - `unified_phone_add_head_components()`
  - `unified_phone_add_footer_components()`
- Normal permitted staff users now receive required module CSS/JS.
- Controller and page-level permissions remain unchanged.
- Fixed search error when searching with only 3 digits.

### Changed
- Removed the `Payments` section from all module areas:
  - Search result UI
  - Settings result visibility list
  - Related records query/model usage
  - Payment-related labels/references
- Updated Search Phone validation:
  - Minimum 4 digits are now required before search is allowed.
  - Invalid short searches now show a warning instead of causing an error page.
- Updated Search Phone form UI:
  - `.unified-phone-search-form` max width set to `550px`.

### Added
- Added floating call button.
  - Button opens the `Call By SIP` page.
  - Added enable/disable setting under `Settings → General`.
- Improved Lead Details Modal → `Call Log` tab:
  - Shows maximum latest 10 call logs.
  - If more than 10 logs exist, a `More` button appears.
  - `More` button redirects to the Reports page with the lead phone number pre-filtered.

### Validation
- PHP syntax check passed.
- JavaScript syntax check passed.
- ZIP integrity test passed.

---

## [1.19.0] - UI & Language Polish Update

### Changed
- Removed the phone search input placeholder:
  - `01712345678 / 8801712345678 / +8801712345678`
- Updated phone search result page UI text:
  - Replaced visible `Unichat` text with `Unichat` in the statistic block.
  - Replaced visible `Unichat` text with `Unichat` in the result block.
  - Internal/backend integration naming remains unchanged for compatibility.
- Moved the floating call button from bottom-left to bottom-right using CSS only.
- Reduced spacing inside the Lead Details Modal → `Call Log` tab.
- Updated language file text for a cleaner user-facing experience.

### Language Updates
- Replaced:
  - `Search by Bangladesh phone number. Supported formats: 017XXXXXXXX, 88017XXXXXXXX, +88017XXXXXXXX, 1712345678.`
- With:
  - `Supported formats: 017XXXXXXXX, 88017XXXXXXXX, +88017XXXXXXXX, 1712345678.`

- Replaced:
  - `Dial quickly through MicroSIP and create the call log in the same workflow.`
- With:
  - `Dial quickly through MicroSIP.`

- Replaced:
  - `Type, paste, or use the dial pad, then press Call Now.`
- With:
  - `Type, paste, or use the dial pad.`

### Validation
- PHP syntax check passed.
- JavaScript syntax check passed.
- ZIP integrity test passed.


