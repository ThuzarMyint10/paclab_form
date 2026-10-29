# Pacific Lab Services – Lab Portal (Laravel 13 + MySQL)

A website for Pacific Lab Services (PacLab Pte Ltd) with:

* a **customer side**: price request, quotation approval, Sample Submission Form, tracking, and downloads of the COA and invoices
* an **admin side** (login required): pricing, quotations, tracking updates, invoices, COA, price list, customer discounts, staff, and settings

---

## 1. The workflow

| # | Who | What happens | Email sent |
|---|-----|--------------|-----------|
| 1 | Customer | Submits a **New Price Request** on the website: company details, samples, and the tests they need (chosen from the test list). **Customers never see the price list.** Prices appear only on the quotation that staff send. | **Customer:** confirmation with the reference number (e.g. `ENQ-2026-00001`). New customers also get a "set your portal password" link.<br>**Admin:** "New enquiry" alert with the full details. |
| 2 | System (staff only) | Pre-fills a draft price for every test from the **standard price list** (SGD or USD), visible only in the admin area. If the company is in the *Customer Companies* list, its agreed **discount %** and **payment terms** are applied automatically. | – |
| 3 | PacLab staff | Log in to **/admin** and open the enquiry. Check the prices, **change any unit price or the overall discount**, and add or remove tests. | – |
| 4 | PacLab staff | Click **"Save & Send Quotation to Customer"**. A quotation number (e.g. `Q312/PLS/2026`) is assigned. | **Customer:** quotation PDF attached, plus a secure link to **Approve / Decline**. |
| 5a | Customer | **Declines** → the job is closed. No further pricing, tracking or invoicing is possible. | Admin alert and customer acknowledgement. |
| 5b | Customer | **Approves** (optionally with a PO number and PO upload) → the **Sample Submission Form is generated automatically** (number `2026-09-29-001`). | **Customer:** Sample Submission Form PDF and sending instructions.<br>**Admin:** "Approved" alert. |
| 6 | PacLab staff | As the job progresses, pick a status from the **pre-set list**:<br>• Sample Received – via Courier / via Post / Hand Delivered<br>• Sample Checked & Registered<br>• Testing Started – In Progress<br>• Sent to Sub-contract Laboratory<br>• Testing Completed<br>• Sample Sent / Dispatched<br>• On Hold – Awaiting Customer Information<br>• Job Closed | **Customer:** status update email (can be switched off per update). |
| 7 | PacLab staff | **COA / Results**: enter results, then **Release COA**. | **Customer:** COA PDF. |
| 8 | PacLab staff | **Generate Invoice** (built from the approved quotation) → review → **Issue & Email** → later **Mark as paid**. | **Customer:** Tax Invoice PDF. |

Customers can follow each step in the **customer portal** (`/portal`) or on the public **Track** page, using any reference number together with their email address.

**Security**

* Admin pages need a staff login.
* Customers only ever see their **own** enquiries, quotations, forms, reports and invoices.
* Quotation links in emails use a long, secret token.

---

## 2. What you need

* **PHP 8.4 or newer** with these extensions: `pdo_mysql`, `mbstring`, `openssl`, `gd`, `dom`, `fileinfo`, `zip`
* **MySQL 5.7+ or MariaDB 10.3+**
* **Composer** (https://getcomposer.org)
* An SMTP mailbox for sending email (Office 365, Gmail/Google Workspace, or your hosting mail)

On a Windows PC the easiest option is **Laragon** (https://laragon.org) or **XAMPP** (https://www.apachefriends.org). Both include PHP and MySQL.

---

## 3. Installation (step by step)

1. **Unzip** the project, for example to `C:\laragon\www\paclab` (Laragon) or `C:\xampp\htdocs\paclab` (XAMPP).
2. **Create the database.** Open phpMyAdmin (or HeidiSQL in Laragon) and create an empty database named `paclab_portal` with collation `utf8mb4_unicode_ci`.
3. **Open a terminal** in the project folder and install the Laravel packages:
   ```
   composer install
   ```
4. **Create the settings file:**
   ```
   copy .env.example .env
   php artisan key:generate
   ```
5. **Edit `.env`** in Notepad:
   * `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: your MySQL details. XAMPP/Laragon default is user `root` with an empty password.
   * `APP_URL`: the address of the site, e.g. `http://localhost:8000` or `https://portal.pacificlab.com.sg`. Links in emails use this.
   * `PACLAB_ADMIN_EMAILS`: who receives the "new enquiry" alerts. Separate several addresses with commas.
   * `PACLAB_SEED_ADMIN_EMAIL` / `PACLAB_SEED_ADMIN_PASSWORD`: the first administrator login.
6. **Create the tables and load the data.** This loads the 2026 price list (415 tests), the 237 customer companies with their discounts and payment terms, the tracking statuses, the settings, and the admin login.
   ```
   php artisan migrate --seed
   ```
7. **Start the site** (for testing on your own PC):
   ```
   php artisan serve
   ```
   * Website: http://localhost:8000
   * Admin: http://localhost:8000/admin/login (use the admin email and password from step 5)

> **Change the admin password** after the first login: click your name at the top right, then **My account**.

---

## 4. Email setup

While `MAIL_MAILER=log` is set, emails are **not sent**. They are written to `storage/logs/laravel.log`, so you can safely test the whole flow first.

To send real emails, set the following in `.env`:

**Office 365 / Outlook**
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_USERNAME=paclab@pacificlab.com.sg
MAIL_PASSWORD=your-password-or-app-password
MAIL_FROM_ADDRESS="paclab@pacificlab.com.sg"
MAIL_FROM_NAME="Pacific Lab Services"
```

**Gmail / Google Workspace**: use `MAIL_HOST=smtp.gmail.com`, `MAIL_PORT=587`, and an *App Password*.

After changing `.env` on a live server, run `php artisan config:clear`.

If the mail server is ever unavailable, the action still completes (for example, the quotation is still saved). The email error is written to `storage/logs/laravel.log`.

---

## 5. Putting it on the internet (hosting)

1. Upload the project to the server. Any PHP hosting with SSH/Composer works, e.g. cPanel, a VPS or Laravel Forge.
2. Point the domain's **document root to the `public` folder**.
3. Run `composer install --no-dev --optimize-autoloader`, then `php artisan migrate --seed --force`.
4. In `.env` set `APP_ENV=production`, `APP_DEBUG=false` and the correct `APP_URL` (https).
5. Make `storage/` and `bootstrap/cache/` writable by the web server.
6. Optionally run `php artisan config:cache` and `php artisan route:cache` for speed.

---

## 6. Admin menu

| Menu | Purpose |
|------|---------|
| **Dashboard** | New enquiries to price, quotations awaiting reply, samples awaiting arrival, jobs in the lab, jobs ready to invoice, unpaid invoices |
| **Enquiries & Jobs** | Search and filter all jobs. Opening a job gives you pricing, send quotation, record the customer's reply (if they answered by email), samples and lab codes, tracking updates, COA and invoices |
| **Invoices** | All invoices: edit drafts, issue, mark paid, void |
| **Price List (Tests)** | The standard 2026 price list. Edit prices, add tests, or **Export → edit in Excel → Import** (CSV) |
| **Customer Companies** | Agreed currency, discount % and payment terms per customer (loaded from *PAC LAB EXTERNAL PAYMENT TERMS – DISCOUNTS*) |
| **Customer Logins** | Customer portal accounts. Enable or disable them, or email a password link |
| **Tracking Statuses** *(Administrator)* | The pre-set words staff choose from. Change the wording, the customer message, and whether the customer is emailed |
| **Staff Logins** *(Administrator)* | Add staff. **Administrator** = full access; **Lab Staff** = everything except settings, statuses and staff |
| **Settings** *(Administrator)* | Company details and bank details printed on documents, GST %, urgent surcharge, quotation validity and notes, COA signatory and disclaimer, running numbers |

### Pricing rules

* **Unit price** = the list price in the enquiry currency (SGD or USD). Staff can overwrite it on any line; amended lines are highlighted.
* **Discount %** comes from the linked customer company and can be changed.
* **Urgent** turnaround adds +50% (set in Settings) and gives no discount.
* **Invoice:** TOTAL → LESS DISCOUNT → SUB-TOTAL → ADD GST → GRAND TOTAL. GST applies automatically only to Singapore customers (9% by default; this can be changed on each invoice).

### Document numbering (set the starting numbers in Settings)

| Document | Example |
|----------|---------|
| Enquiry | `ENQ-2026-00001` |
| Quotation | `Q312/PLS/2026` |
| Sample Submission Form (also used as the COA report number) | `2026-09-29-001` |
| Lab sample code (given on receipt) | `AJ12926` |
| Invoice | `PLB262223` |

---

## 7. Project structure (for developers)

```
app/Services/Workflow.php     all business rules (enquiry → quotation → approval → tracking → COA → invoice)
app/Services/Documents.php    PDF generation (dompdf)
app/Services/Numbering.php    running numbers
app/Mail/                     the 9 email types
app/Http/Controllers/         public site, customer portal (Portal/), admin (Admin/)
resources/views/pdf/          quotation, ssf (Sample Submission Form), invoice, coa templates
resources/views/emails/       email templates
database/seeders/data/        tests.json (2026 price list), companies.json (discounts & terms)
```

The layout uses Bootstrap 5, loaded from the jsDelivr CDN, so no Node/npm build step is needed.
