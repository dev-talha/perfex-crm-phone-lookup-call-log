# Alpha PBX With CRM - MicroSIP Setup Guide

এই গাইডটি MicroSIP-এ incoming call আসলে Alpha PBX CRM portal auto-open করার জন্য। ধরে নেওয়া হচ্ছে আপনার MicroSIP আগে থেকেই setup/install করা আছে। যদি MicroSIP install করা না থাকে, তাহলে আগে আপনার installation documentation অনুসরণ করে MicroSIP install করুন।

---

## 1. AlphaPBX.zip Copy & Extract করুন

1. `AlphaPBX.zip` ফাইলটি copy করুন।
2. ফাইলটি `C:` drive-এ paste করুন।
3. `C:` drive-এ ফাইলটি unzip/extract করুন।

Extract করার পর folder path হবে:

```text
C:\AlphaPBX
```

---

## 2. MicroSIP সম্পূর্ণভাবে Exit করুন

`MicroSIP.ini` ফাইল edit করার আগে অবশ্যই MicroSIP সম্পূর্ণভাবে বন্ধ করতে হবে।

1. MicroSIP window close করুন।
2. System Tray / Notification Area চেক করুন।
3. সেখানে MicroSIP icon থাকলে right-click করে **Exit** করুন।

> MicroSIP পুরোপুরি exit না করলে `MicroSIP.ini` ফাইল save নাও হতে পারে।

---

## 3. MicroSIP.ini ফাইল Locate করুন

MicroSIP-এর default installation/config location সাধারণত:

```text
C:\Users\{YourUserName}\AppData\Roaming\MicroSIP
```

এই folder-এ গিয়ে নিচের ফাইলটি খুঁজুন:

```text
MicroSIP.ini
```

ফাইলটি Notepad অথবা Notepad++ দিয়ে open করুন।

---

## 4. Incoming Call Command Update করুন

`MicroSIP.ini` ফাইলের ভিতরে নিচের line টি খুঁজুন:

```ini
cmdIncomingCall=""
```

এটি replace করুন নিচের line দিয়ে:

```ini
cmdIncomingCall="C:\AlphaPBX\open_portal.cmd"
```

এতে MicroSIP-এ incoming call আসলে Alpha PBX CRM portal open হবে এবং caller number দিয়ে CRM-এ client information search করবে।

---

## 5. Extension Call-এ CRM Portal Open না করতে চাইলে

যদি extension-to-extension call-এর সময় CRM portal open করতে না চান, তাহলে উপরের command-এর পরিবর্তে নিচের line ব্যবহার করুন:

```ini
cmdIncomingCall="C:\AlphaPBX\open_portal_without_extension.cmd"
```

---

## 6. File Save Verify করুন

1. `MicroSIP.ini` ফাইল save করুন।
2. ফাইলটি close করুন।
3. আবার open করে নিশ্চিত করুন line টি correctly save হয়েছে।

Expected line হবে যেকোনো একটি:

```ini
cmdIncomingCall="C:\AlphaPBX\open_portal.cmd"
```

অথবা

```ini
cmdIncomingCall="C:\AlphaPBX\open_portal_without_extension.cmd"
```

---

## 7. MicroSIP Restart করুন

ফাইল save verify করার পর MicroSIP আবার চালু করুন। এখন incoming call আসলে CRM portal auto-open হওয়ার কথা।

---

## 8. Windows Security / Antivirus Warning

কিছু system এই script বা command file-কে harmful হিসেবে detect করতে পারে।

এটি সাধারণত false warning হতে পারে, কারণ এই script শুধুমাত্র MicroSIP-এ আসা incoming call information Alpha PBX CRM portal-এ পাঠায় এবং caller number দিয়ে CRM-এ client information search করে।

যদি আপনার internal IT/security policy অনুমতি দেয়, তাহলে warning ignore/allow করুন।

---

## Troubleshooting

### MicroSIP.ini save হচ্ছে না

- MicroSIP পুরোপুরি exit করা হয়েছে কিনা চেক করুন।
- System Tray / Notification Area থেকে MicroSIP exit করুন।
- প্রয়োজন হলে Notepad/Notepad++ **Run as Administrator** করে open করুন।

### Incoming call-এ CRM open হচ্ছে না

- `C:\AlphaPBX` folder আছে কিনা চেক করুন।
- `open_portal.cmd` অথবা `open_portal_without_extension.cmd` ফাইল আছে কিনা চেক করুন।
- `cmdIncomingCall` line ঠিকভাবে update হয়েছে কিনা verify করুন।
- MicroSIP restart করুন।

---

## Final Configuration Example

CRM portal সব incoming call-এর জন্য open করতে চাইলে:

```ini
cmdIncomingCall="C:\AlphaPBX\open_portal.cmd"
```

Extension call বাদ দিয়ে CRM portal open করতে চাইলে:

```ini
cmdIncomingCall="C:\AlphaPBX\open_portal_without_extension.cmd"
```
