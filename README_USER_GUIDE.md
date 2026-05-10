# Unified Phone Search & Call Log Module — User Guide

![Perfex CRM](https://img.shields.io/badge/Perfex%20CRM-Phone%20Lookup-blue)
![Guide](https://img.shields.io/badge/guide-beginner%20friendly-success)

## Welcome

The **Unified Phone Search & Call Log** module helps your team quickly find customer information by phone number, make calls through MicroSIP, save call notes, upload call recordings, and review call history from one place inside Perfex CRM.

This guide is written for normal staff and admins. No technical knowledge is required.

---

## 1. What This Module Does

With this module, you can:

- Search any phone number in the CRM.
- See matching customers, leads, invoices, projects, tickets, and other related records.
- See previous call history for that phone number.
- Add call logs with notes and outcomes.
- Make outgoing calls through MicroSIP.
- Upload call recordings if your admin allows it.
- View reports of call activity.
- Use click-to-call from CRM pages.

---

## 2. Where to Find It

After the module is enabled, you will see a menu named:

```text
Phone Lookup
```

Possible submenu items:

```text
Search Phone
Call By SIP
Reports
Settings
```

You may not see all menu items. Your admin controls what each staff role can access.

---

## 3. Search Phone

### How to search

1. Open **Phone Lookup > Search Phone**.
2. Enter a phone number.
3. Click **Search**.

You can search using different phone formats, such as:

```text
01712345678
8801712345678
+8801712345678
008801712345678
1712345678
```

The system will automatically try to match the number correctly.

### What you will see after searching

The result page may show:

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
- Unichat conversations, if enabled
- Previous call logs

Your admin can choose which sections are visible.

---

## 4. Previous Call Logs

The **Previous Call Logs** section shows recent calls for the searched phone number.

From this section, you can usually see:

- Call date
- Phone number
- Call type
- Duration
- Outcome
- Follow-up date/time
- Related CRM record
- Staff member
- Options such as View, Edit, or Delete

Available buttons depend on your permission.

---

## 5. Add a Call Log

### How to add a call log

1. Search a phone number.
2. Click **Add Call Log**.
3. Fill in the call details.
4. Click **Save**.

### Common fields

| Field | Meaning |
|---|---|
| Phone Number | The phone number used for the call |
| Call Type | Incoming or Outgoing |
| Outcome | Result of the call, such as Connected or Busy |
| Call Date | Date of the call |
| Start Time | When the call started |
| End Time | When the call ended |
| Duration | Automatically calculated from start and end time |
| Related To | CRM record type, such as Lead or Customer |
| Related ID | The specific lead, customer, invoice, proposal, etc. |
| Follow-up Date/Time | When follow-up is needed |
| Call Recording | Optional recording upload, if allowed |
| Note | Your call note |

Your admin may make some fields required.

---

## 6. Call Type and Time Behavior

The module automatically fills time fields depending on how the call log opens.

| Situation | Start Time | End Time |
|---|---|---|
| Incoming call | Time when the page loaded | Current time |
| Outgoing call | Current time | Current time |
| No call type selected | Current time | Current time |

You can still edit the time if needed.

---

## 7. Related To and Related ID

These fields connect a call log to a CRM record.

Example:

If you are calling a lead, choose:

```text
Related To: Lead
Related ID: The lead name
```

Supported related types:

- Project
- Invoice
- Customer
- Estimate
- Contract
- Ticket
- Lead
- Proposal

The **Related ID** field is searchable. Start typing the name or number and choose the correct record.

---

## 8. Call Recordings

If your admin enables call recordings and gives you permission, you can upload a recording when adding or editing a call log.

Beside the recording field, you will see a helpful note like:

```text
Allowed: mp3,wav. Max size: 1000 KB.
```

Only allowed file types and allowed file sizes can be uploaded.

### Where recordings appear

Recordings are shown on the **Call Log Details** page.

Depending on your permission, you may be able to:

- Play the recording
- Download the recording
- Replace the recording when editing

If you do not see recording controls, you may not have permission.

---

## 9. Call By SIP

The **Call By SIP** page lets you dial a number through MicroSIP.

### Important requirement

MicroSIP must already be installed and configured on your computer.

### How to make a SIP call

1. Open **Phone Lookup > Call By SIP**.
2. Type a phone number, paste a number, or use the dial pad.
3. Click **Call Now**.
4. Your browser will open MicroSIP.
5. The Add Call Log modal will open automatically.
6. After the call ends, fill in the remaining details and save.

The module will automatically fill:

- Phone number
- Call type: Outgoing
- Date: Today
- Start time: Current time

---

## 10. Global Click-to-Call

If your admin enables this feature, phone numbers in the CRM can be clicked to start a call.

For example, you may click a phone number from:

- Lead list
- Lead details
- Customer list
- Customer details
- Proposal page
- Invoice page
- Other supported CRM pages

When you click the number:

1. The call is sent to MicroSIP.
2. The Add Call Log modal opens.
3. The module tries to fill the related CRM record automatically.

Example:

If you click a phone number from a lead page, the module should fill:

```text
Related To: Lead
Related ID: That lead
```

---

## 11. Lead and Customer Call Logs

The module can show call logs inside CRM records.

### Lead details

Inside a lead modal, you may see a tab named:

```text
Call Log
```

This tab shows calls related to that lead's phone number.

### Customer profile

Inside a customer profile, you may see a call log section/tab depending on your permissions and module settings.

---

## 12. Reports

The Reports page helps managers and staff review call activity.

Open:

```text
Phone Lookup > Reports
```

You can filter reports by:

- Date range
- Phone number
- Staff
- Call type
- Outcome
- Related To
- Follow-up date/time

You can also use **Reset Filter** to clear filters quickly.

If your admin allows export, you may see export options.

---

## 13. Settings for Admins

Only admins or permitted staff can access settings.

Open:

```text
Phone Lookup > Settings
```

The settings page is divided into tabs so it is easier to manage.

Common setting areas:

### General

- Enable or disable the module.
- Enable phone normalization.
- Enable like search.
- Enable reporting/export.

### Search Result Sections

Choose which sections staff can see on the search page.

Example:

- Customers
- Leads
- Invoices
- Projects
- Tickets
- Previous Call Logs

You can also choose how many latest records to show.

### Call Log Form

Control which fields are visible and which fields are required.

Example:

- Phone Number
- Call Type
- Outcome
- Related To
- Follow-up Date/Time
- Note

### SIP & Recordings

Control:

- Call By SIP
- Global CRM click-to-call
- SIP URL scheme
- Recording upload
- Allowed recording file types
- Maximum recording file size

### Unichat

Configure Unichat API information if you want to show conversation data on the phone lookup page.

### Permissions

Permissions are managed from Perfex CRM role settings.

---

## 14. Staff Permissions

Admins can control who can use each feature.

Possible permissions include:

- View phone search
- Add call log
- Edit call log
- Delete call log
- View call log details
- Access call recordings
- View reports
- Export reports
- Manage settings
- Manage Unichat settings

If a staff member cannot see a button or page, check their role permissions first.

---

## 15. Common Problems and Easy Fixes

### I cannot see the Phone Lookup menu

Ask your admin to check:

- Module is enabled.
- Your role has permission to view the module.

### MicroSIP does not open

Check:

- MicroSIP is installed on your computer.
- MicroSIP is configured correctly.
- Browser allows opening external apps.
- Admin selected the correct SIP scheme, usually `sip:`.

### I cannot upload a recording

Check:

- Recording upload is enabled in settings.
- Your role has **Access Call Recordings** permission.
- File type is allowed.
- File size is not too large.
- Server PHP upload limit is not smaller than the module limit.

### Related To / Related ID is not filling automatically

This can happen on custom pages or custom themes.

You can still manually select:

```text
Related To
Related ID
```

If it happens often, ask a developer to check the page structure.

### I see no Unichat data

Check:

- Unichat integration is enabled.
- API settings are correct.
- The searched phone number exists in Unichat.
- You have permission to view Unichat data.

### I cannot edit or delete a call log

Your role may not have edit/delete permission. Ask an admin to check your role.

---

## 16. Recommended Daily Workflow

### For incoming calls

1. Search the caller's phone number.
2. Review customer/lead history.
3. Click **Add Call Log**.
4. Select outcome.
5. Add note.
6. Add follow-up date/time if needed.
7. Save.

### For outgoing calls

1. Open **Call By SIP** or click a phone number in CRM.
2. Start the call.
3. Wait for the Add Call Log modal.
4. After the call, add outcome and note.
5. Upload recording if needed and allowed.
6. Save.

### For managers

1. Open Reports.
2. Filter by date, staff, call type, or outcome.
3. Review call activity.
4. Export if needed.

---

## 17. Best Practices

- Always write a clear note after each important call.
- Select the correct outcome.
- Link the call to the correct CRM record.
- Use follow-up date/time when another call or action is needed.
- Upload recordings only when required and permitted.
- Use search before calling so you understand customer history.

---

## 18. Quick Reference

| Task | Where to Go |
|---|---|
| Search a phone number | Phone Lookup > Search Phone |
| Make a SIP call | Phone Lookup > Call By SIP |
| Add a call log | Search page or call modal |
| View call history | Search result page or Reports |
| View call log details | Click the View button |
| Upload recording | Add/Edit Call Log, if permitted |
| Review reports | Phone Lookup > Reports |
| Change settings | Phone Lookup > Settings |

---

## 19. Final Note

This module is designed to help staff work faster and keep call history organized. Use it every time you make or receive an important customer call so your CRM stays accurate and useful for the whole team.
