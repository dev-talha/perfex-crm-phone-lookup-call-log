# Unified Phone Search & Call Log Module — Developer README

![Perfex CRM](https://img.shields.io/badge/Perfex%20CRM-Module-blue)
![Version](https://img.shields.io/badge/version-1.17.0-success)
![PHP](https://img.shields.io/badge/PHP-CodeIgniter%203-informational)
![Status](https://img.shields.io/badge/status-production--ready-brightgreen)

## 1. Overview

**Unified Phone Search & Call Log** is a custom Perfex CRM module built for call center and CRM teams. It provides a centralized phone lookup workflow, SIP calling through MicroSIP, call logging, CRM relationship mapping, Unichat/Chatwoot context lookup, call recording upload, reporting, and role-based access control.

The module is designed around Bangladesh phone-number normalization and fast CRM lookup. It can search CRM records by raw phone, normalized phone, last 10 digits, and last 11 digits. It also provides global click-to-call behavior from supported CRM pages and automatically opens the call-log modal with contextual data.

---

## 2. Current Version

```text
Module Version: 1.17.0
Module Folder: unified_phone
Perfex Path: public_html/modules/unified_phone
```

### Major capabilities in v1.17

- Central phone lookup page.
- Bangladesh phone number normalization.
- CRM search across customers, contacts, leads, proposals, estimates, invoices, payments, projects, contracts, and tickets.
- Optional Unichat/Chatwoot contact and conversation lookup.
- Previous call logs with pagination.
- Add/Edit/View call log workflow.
- Call By SIP page for MicroSIP dialing.
- Global CRM click-to-call with automatic modal prefill.
- Call recording upload with permission-checked playback/download.
- Admin settings with tab-wise layout.
- Role-based permissions.
- Reports with filters and export controls.
- Lead/customer call-log tabs.

---

## 3. Installation

1. Extract the module ZIP.
2. Upload the folder:

```text
unified_phone
```

to:

```text
public_html/modules/unified_phone
```

3. Go to **Setup > Modules** in Perfex CRM.
4. Activate the module.
5. If updating an existing installation, deactivate and reactivate the module once so new options, columns, and permissions are registered.
6. Go to:

```text
/admin/unified_phone/settings
```

and configure the module.

---

## 4. Module File Structure

```text
unified_phone/
├── unified_phone.php                     # Module bootstrap, hooks, menu, permissions
├── install.php                           # DB tables, columns, options, upgrade logic
├── uninstall.php                         # Uninstall handling
├── config/
│   └── routes.php                        # Compatibility routes
├── controllers/
│   ├── Unified_phone.php                 # Main controller: search, settings, SIP, logs, uploads
│   ├── Unified_phone_reports.php         # Compatibility reports controller
│   ├── Unified_phone_settings.php        # Compatibility settings redirect/controller
│   └── Unified_phone_api.php             # API compatibility endpoint
├── models/
│   ├── Unified_phone_model.php           # CRM lookup/search logic
│   ├── Unified_call_log_model.php        # Call log CRUD and lookup logic
│   ├── Unified_phone_report_model.php    # Report metrics/data
│   └── Chatwoot_model.php                # Unichat/Chatwoot API/cache integration
├── libraries/
│   ├── Phone_normalizer.php              # Bangladesh phone normalization
│   └── Chatwoot_api.php                  # API client wrapper
├── helpers/
│   └── unified_phone_helper.php          # Permissions, formatting, routes, utilities
├── views/
│   ├── search.php                        # Search form page
│   ├── result.php                        # Unified phone lookup results
│   ├── call_by_sip.php                   # SIP dialer page
│   ├── call_log_modal.php                # Add call log modal
│   ├── edit_call_log.php                 # Edit call log page
│   ├── view_call_log.php                 # Call log detail page
│   ├── reports.php                       # Reports page
│   ├── settings.php                      # Tab-wise settings page
│   ├── partials/                         # Result and table partials
│   ├── customer_groups/call_logs.php     # Customer profile tab content
│   └── lead_tabs/call_logs.php           # Lead modal tab content
├── assets/
│   ├── css/unified_phone.css             # Module UI styles
│   └── js/unified_phone.js               # Modal, SIP, global click-to-call logic
└── language/
    └── english/unified_phone_lang.php    # Language strings
```

---

## 5. Bootstrapping and Hook Flow

The bootstrap file is:

```text
unified_phone/unified_phone.php
```

Important hooks:

```php
hooks()->add_action('admin_init', 'unified_phone_permissions');
hooks()->add_action('admin_init', 'unified_phone_init_menu_items');
hooks()->add_action('admin_init', 'unified_phone_register_customer_tab');
hooks()->add_action('after_lead_lead_tabs', 'unified_phone_register_lead_tab_menu');
hooks()->add_action('after_lead_tabs_content', 'unified_phone_register_lead_tab_content');
hooks()->add_action('lead_modal_profile_bottom', 'unified_phone_lead_modal_fallback_tab');
hooks()->add_action('app_admin_head', 'unified_phone_add_head_components');
hooks()->add_action('app_admin_footer', 'unified_phone_add_footer_components');
```

### Notes for debugging hooks

- If the **Lead Call Log** tab does not appear, check whether the Perfex version uses the expected lead modal hooks.
- The module includes both server-side hook rendering and a JavaScript fallback to inject/fix the tab.
- The tab endpoint is:

```text
/admin/unified_phone/lead_call_logs_tab/{lead_id}
```

The tab loads call logs by the lead phone number, not only by `lead_id`.

---

## 6. Main Routes / URLs

| Feature | URL |
|---|---|
| Search page | `/admin/unified_phone` |
| URL phone lookup | `/admin/unified_phone/search?phone=017XXXXXXXX` |
| URL lookup with call type | `/admin/unified_phone/search?phone=017XXXXXXXX&calltype=outgoing` |
| Call By SIP | `/admin/unified_phone/call_by_sip` |
| Reports | `/admin/unified_phone/reports` |
| Settings | `/admin/unified_phone/settings` |
| Add call log | `/admin/unified_phone/add_call_log` |
| Edit call log | `/admin/unified_phone/edit_call_log/{id}` |
| View call log | `/admin/unified_phone/view_call_log/{id}` |
| Related CRM search | `/admin/unified_phone/related_search` |
| Lead call log tab | `/admin/unified_phone/lead_call_logs_tab/{lead_id}` |
| Recording stream/download | permission-checked controller route |

---

## 7. Database Tables

### 7.1 `tblunified_call_logs`

Stores all call log records.

Important fields:

```sql
id
phone_raw
phone_normalized
call_type
call_datetime
start_time
end_time
duration_seconds
duration_text
outcome
note
follow_up_required
follow_up_datetime
rel_type
rel_id
client_id
contact_id
lead_id
created_by
updated_by
created_at
updated_at
recording_file
recording_original_name
recording_mime_type
recording_size
```

The module stores both raw and normalized phone values for accurate searching and display.

### 7.2 `tblunified_call_outcomes`

Stores configurable call outcomes.

```sql
id
name
color
is_default
is_active
sort_order
```

Outcome color is used as a visible badge in call log tables and detail views.

### 7.3 `tblunified_phone_cache`

Used for optional Unichat/Chatwoot API response caching.

```sql
id
phone_raw
phone_normalized
source
source_id
payload
created_at
expires_at
```

---

## 8. Settings Storage

All module settings are stored in Perfex's standard options table:

```text
tbloptions
```

Option names are prefixed with:

```text
unified_phone_
```

Examples:

```text
unified_phone_enabled
unified_phone_sip_enabled
unified_phone_global_click_to_call_enabled
unified_phone_recording_enabled
unified_phone_recording_max_size
unified_phone_recording_allowed_types
unified_phone_chatwoot_enabled
unified_phone_chatwoot_base_url
unified_phone_chatwoot_account_id
unified_phone_chatwoot_api_token
unified_phone_chatwoot_api_timeout
unified_phone_reporting_enabled
unified_phone_export_enabled
```

Read settings using:

```php
get_option('unified_phone_enabled');
```

Update settings using:

```php
update_option('unified_phone_enabled', '1');
```

---

## 9. Permissions

The module registers staff capabilities under the `unified_phone` permission group.

| Capability | Purpose |
|---|---|
| `view` | Access phone search and basic module pages |
| `view_detail` | View call log detail page |
| `recordings` | Upload, view, play, and download call recordings |
| `create` | Add call logs |
| `edit` | Edit call logs |
| `delete` | Delete call logs |
| `view_reports` | View reports page |
| `export` | Export report data |
| `settings` | Manage module settings |
| `chatwoot` | Manage Unichat/Chatwoot settings |

Recommended helper check:

```php
unified_phone_can('view');
unified_phone_can('recordings');
```

Admins normally bypass most staff permission restrictions.

---

## 10. Phone Normalization

The module is optimized for Bangladesh phone numbers.

Supported user input examples:

```text
01712345678
8801712345678
+8801712345678
008801712345678
1712345678
```

Internal normalized output:

```text
8801712345678
```

Common search keys:

```text
raw phone
normalized phone
last 10 digits
last 11 digits
```

Core file:

```text
libraries/Phone_normalizer.php
```

If phone matching behaves unexpectedly, debug this library first, then inspect the search query builder in:

```text
models/Unified_phone_model.php
models/Unified_call_log_model.php
```

---

## 11. Search Result Architecture

Search results are section based. Admins can enable/disable sections from settings and control the latest-record limit per section.

Supported sections include:

- Customers
- Contacts
- Leads
- Proposals
- Estimates
- Invoices
- Payments
- Projects
- Contracts
- Tickets
- Unichat/Chatwoot
- Previous Call Logs

Removed/disabled sections due to search issues:

- Expenses
- Tasks
- Notes

Previous call logs are capped and paginated to avoid heavy rendering on high-volume installations.

---

## 12. Call Log Workflow

### 12.1 Add Call Log modal

The modal supports:

- Phone Number
- Call Type
- Outcome
- Call Date
- Start Time
- End Time
- Duration
- Related To
- Related ID
- Follow-up Date/Time
- Call Recording
- Note

Field visibility and required status can be controlled from settings.

### 12.2 Call type and time rules

When the modal is opened from a URL or click-to-call workflow:

| Condition | Start Time | End Time |
|---|---|---|
| `calltype=incoming` | Last page load time | Current time |
| `calltype=outgoing` | Current time | Current time |
| Call type empty | Current time | Current time |

### 12.3 Related To values

Supported CRM relation types:

```text
Project
Invoice
Customer
Estimate
Contract
Ticket
Lead
Proposal
```

The related ID dropdown uses a searchable AJAX endpoint:

```text
/admin/unified_phone/related_search
```

---

## 13. SIP / MicroSIP Integration

The module does not make calls directly. It opens the staff member's local SIP client using browser URL schemes.

Supported schemes:

```text
sip:
tel:
callto:
```

Typical MicroSIP setup uses:

```text
sip:{phone_number}
```

### Call By SIP flow

1. Staff opens **Phone Lookup > Call By SIP**.
2. Staff types, pastes, or dials with the on-screen keypad.
3. Staff clicks **Call Now**.
4. Browser opens MicroSIP through the configured URI scheme.
5. Add Call Log modal opens automatically.
6. Staff fills outcome, note, related record, recording, etc.
7. Staff saves the call log.

---

## 14. Global CRM Click-to-Call

When enabled, the module intercepts phone links in the CRM such as:

```html
<a href="tel:01712345678">01712345678</a>
<a href="sip:01712345678">01712345678</a>
<a href="callto:01712345678">01712345678</a>
```

The JavaScript then:

1. Detects the phone number.
2. Detects the current CRM context when possible.
3. Opens the call-log modal.
4. Sends the call to SIP.
5. Prefills fields such as phone, call type, date, start time, related type, and related ID.

Supported context detection includes detail and list pages for common CRM records, especially Leads and Customers.

Core JavaScript file:

```text
assets/js/unified_phone.js
```

If a relation is not detected on a custom page, inspect the DOM and add a selector fallback in the relation-detection block.

---

## 15. Call Recordings

### 15.1 Storage

Recordings are stored under:

```text
uploads/unified_phone_recordings/{call_log_id}/
```

The module creates protection files where needed:

```text
.htaccess
index.html
```

### 15.2 Permissions

Recording access is controlled by:

```text
Access Call Recordings
```

This permission controls:

- Uploading recordings
- Viewing recording controls
- Playing recordings
- Downloading recordings

### 15.3 Security behavior

Recordings should be served only through the module controller route, not direct public links.

Security checks include:

- Staff permission check
- File path validation
- Extension validation
- Sanitized original filename
- Private/no-cache headers
- `X-Content-Type-Options: nosniff`
- Old file cleanup when replaced
- File cleanup when call log is deleted

### 15.4 Upload-size troubleshooting

If users see:

```text
The uploaded file exceeds the maximum allowed size in your PHP configuration file.
```

check server limits:

```ini
upload_max_filesize
post_max_size
memory_limit
max_execution_time
```

The module setting cannot exceed PHP/server-level limits. Increase PHP configuration first, then set the module recording max size.

---

## 16. Unichat / Chatwoot Integration

On settings pages, the UI label is **Unichat**. Internally, some files/classes may still use `Chatwoot` naming for backward compatibility.

Configuration settings:

- Enable Unichat integration
- Base URL
- Account ID
- API token
- API Timeout (seconds)
- Cache enabled
- Cache duration (seconds)

The search result page shows the Unichat section only when:

1. Integration is enabled.
2. The search-result section is enabled in settings.
3. The user has the required permission.

Conversation display is limited for performance and readability.

---

## 17. Reports

The report page supports filtering by:

- Date range
- Phone number
- Staff
- Call type
- Outcome
- Related To
- Customer/lead context where available
- Follow-up Date/Time range

The page also includes a **Reset Filter** button.

Export is controlled by the `export` capability.

The old Call Logs page is intentionally redirected to Reports to keep module management simple.

---

## 18. UI / UX Notes

The module follows Perfex CRM/Bootstrap styling where possible.

Important UI decisions:

- Tab-wise settings layout to avoid a long confusing settings page.
- Compact SIP dialer with keypad.
- Collapsible/information area for SIP instructions.
- Colored badges for call outcomes.
- Permission-aware recording controls.
- Clean Options column button alignment.
- Responsive behavior for modal and table layouts.

---

## 19. Debugging Guide

### 19.1 Settings not saving

Check:

- POST payload names in `views/settings.php`.
- Save logic in `controllers/Unified_phone.php`.
- Whether options exist in `tbloptions`.
- Whether values are posted under `settings[...]` or direct field names.

### 19.2 Related To / Related ID not auto-filling

Check:

- `assets/js/unified_phone.js` relation detection.
- Current URL pattern.
- Row/table DOM selectors.
- Whether the clicked phone link is inside a row containing an ID link or checkbox.
- `related_search` endpoint response.

### 19.3 Lead Call Log tab missing

Check:

- Perfex lead modal hooks.
- `after_lead_lead_tabs` output.
- `after_lead_tabs_content` output.
- Fallback script from `lead_modal_profile_bottom`.
- Browser console errors.
- Endpoint `/admin/unified_phone/lead_call_logs_tab/{lead_id}`.

### 19.4 SIP call not opening MicroSIP

Check:

- MicroSIP is installed and configured on the workstation.
- Browser allows opening external applications.
- Correct URI scheme is selected in settings.
- Test manually in browser address bar:

```text
sip:01712345678
```

### 19.5 Recording upload fails

Check:

- Staff has `Access Call Recordings` permission.
- Module recording upload is enabled.
- File extension is allowed.
- File size is below module limit.
- File size is below PHP `upload_max_filesize` and `post_max_size`.
- `uploads/` is writable.

### 19.6 Raw language key showing in UI

Check:

```text
language/english/unified_phone_lang.php
```

Add missing key:

```php
$lang['missing_key'] = 'Readable Text';
```

Then clear cache/hard refresh if needed.

---

## 20. Performance Notes

The module was designed to avoid loading unlimited CRM history.

Performance controls:

- Per-section latest-record limits.
- Previous call logs pagination.
- Previous call history maximum cap.
- Optional Unichat/Chatwoot API cache.
- Limited Unichat conversation display.
- Call log lookup based on normalized/partial phone keys.

Recommended database indexes for high-volume installations:

```sql
CREATE INDEX idx_unified_call_logs_phone_normalized ON tblunified_call_logs(phone_normalized);
CREATE INDEX idx_unified_call_logs_phone_raw ON tblunified_call_logs(phone_raw);
CREATE INDEX idx_unified_call_logs_created_at ON tblunified_call_logs(created_at);
CREATE INDEX idx_unified_call_logs_rel ON tblunified_call_logs(rel_type, rel_id);
CREATE INDEX idx_unified_call_logs_lead_id ON tblunified_call_logs(lead_id);
CREATE INDEX idx_unified_call_logs_client_id ON tblunified_call_logs(client_id);
```

Before adding new result sections, always apply a limit and avoid full-table scans where possible.

---

## 21. Security Checklist

When modifying the module, preserve these protections:

- Every controller method must check staff login and permission.
- Settings page must require admin or settings capability.
- Export must require export capability.
- Recording playback/download must require recording permission.
- File uploads must validate size, extension, and path.
- Never expose raw recording file paths directly in public HTML.
- Use `html_escape()` when printing user-provided data.
- Use parameterized/Query Builder DB queries.
- Restrict AJAX endpoints to logged-in staff.
- Keep API tokens in `tbloptions` and never print them unmasked.

---

## 22. Upgrade Notes

When adding new settings:

1. Add default option in `install.php`.
2. Add field in `views/settings.php`.
3. Add save handling in `controllers/Unified_phone.php`.
4. Add language strings.
5. Mention the setting in this README.
6. Test activation/reactivation on an existing installation.

When adding new database columns:

1. Add `CREATE TABLE` column for fresh installs.
2. Add `ALTER TABLE` logic for existing installs.
3. Add model insert/update handling.
4. Add validation and permission logic where needed.

---

## 23. Testing Checklist Before Release

- [ ] Activate module on a fresh Perfex installation.
- [ ] Reactivate module on an existing installation.
- [ ] Check settings save correctly.
- [ ] Test phone search with `017`, `88017`, `+88017`, `0088017`, and last 10 digits.
- [ ] Test incoming and outgoing call log creation.
- [ ] Test URL `calltype=incoming` and `calltype=outgoing`.
- [ ] Test related record AJAX dropdown.
- [ ] Test click-to-call from lead detail, customer detail, lead list, and customer list.
- [ ] Test Call By SIP dial pad including `0`.
- [ ] Test recording upload/play/download with and without permission.
- [ ] Test reports filters and reset button.
- [ ] Test export permission.
- [ ] Test lead modal Call Log tab.
- [ ] Test responsive layout on desktop, tablet, and mobile.
- [ ] Run PHP syntax check.
- [ ] Check browser console for JavaScript errors.

---

## 24. Useful Commands

Run PHP syntax checks:

```bash
find unified_phone -name "*.php" -print0 | xargs -0 -n1 php -l
```

Create a ZIP package:

```bash
zip -r unified_phone.zip unified_phone
```

Inspect ZIP contents:

```bash
unzip -l unified_phone.zip
```

---

## 25. Maintainer Notes

- Keep internal class/file names stable to avoid breaking existing installations.
- Settings page labels may say **Unichat**, while some internal code may still reference `chatwoot` for backward compatibility.
- Do not remove call-log database tables unless you are intentionally deleting all historical call data.
- Keep result sections limited and permission-aware.
- For future telephony integrations, add a new adapter layer instead of hardcoding provider logic inside controllers.

---

## 26. Support Summary

For most future issues, start debugging in this order:

1. Browser console errors.
2. Perfex logs.
3. Staff permissions.
4. Module settings in `tbloptions`.
5. Controller method permission checks.
6. Model query filters.
7. JavaScript relation detection.
8. Server PHP upload limits.

This module is now structured to be maintainable, permission-aware, and easy to extend.
