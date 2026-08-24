# DVWA Security Analysis — Vulnerability Auditing & Remediation

A hands-on security audit of **DVWA (Damn Vulnerable Web Application)** performed in a controlled local lab environment (XAMPP + Burp Suite Community + VS Code). For each vulnerability class, the code was manually reviewed, exploited at the "Low" security level to confirm the flaw, patched at the source-code level, and re-tested to verify the fix.

> **Environment note:** All testing was performed against a self-hosted local instance of DVWA (`localhost/DVWA`), which is explicitly designed and distributed for this purpose. No external systems were accessed.

---

## Objective

Move beyond automated scanner output and understand *why* each vulnerability exists at the code level — then fix it the way a developer or AppSec engineer would, and prove the fix works.

## Methodology

For every vulnerability:
1. **Discovery** — identify the vulnerable feature and confirm normal functionality
2. **Exploitation** — craft a payload and confirm the vulnerability is real (before-fix evidence)
3. **Code Analysis** — review the exact PHP source causing the flaw
4. **Remediation** — patch the source code following secure-coding practice
5. **Verification** — re-run the same payload post-fix and confirm it's blocked

## Tools & Environment

| Tool | Purpose |
|---|---|
| XAMPP | Local PHP/MySQL server hosting DVWA |
| Burp Suite Community | Intercepting requests, confirming payload delivery |
| VS Code | Source code review and remediation |
| DVWA | Intentionally vulnerable target application |
| Browser DevTools | Cookie/session inspection (Weak Session IDs) |

---

## Vulnerabilities Audited

| # | Vulnerability | Type | Status |
|---|---|---|---|
| 1 | SQL Injection | Injection | ✅ Fixed |
| 2 | OS Command Injection | Injection | ✅ Fixed |
| 3 | Reflected XSS | Injection / Output Encoding | ✅ Fixed |
| 4 | Unrestricted File Upload | Validation | ✅ Fixed |
| 5 | Stored XSS | Injection / Output Encoding | ✅ Fixed |
| 6 | Local File Inclusion (LFI) | Path Traversal | ✅ Fixed |
| 7 | Weak Cryptographic Storage (MD5) | Cryptography | ✅ Fixed |
| 8 | Missing CSRF Tokens | Design (Tampering) | ✅ Fixed |
| 9 | Broken Access Control | Design (Elevation of Privilege) | ✅ Fixed |
| 10 | Inadequate Security Logging | Design (Repudiation) | ✅ Fixed |
| 11 | Plaintext Credentials in Config | Design (Information Disclosure) | ✅ Fixed |

---

## 1. SQL Injection

**Where:** `SQL Injection` module, User ID field
**Payload:** `' OR '1'='1`
**Impact:** Returned every row in the `users` table instead of one record — full table dump via a single unauthenticated field.

The original query concatenated raw user input directly into the SQL string, so the database had no way to distinguish code from data.

**Fix:** Rebuilt the query using `mysqli_prepare()` + `mysqli_stmt_bind_param()` (prepared statements), which binds `$id` strictly as a data value, never as executable SQL. Re-tested the same payload post-fix — no data returned.

See [`code-fixes/sqli_fix.php`](code-fixes/sqli_fix.php)

---

## 2. OS Command Injection

**Where:** `Command Injection` module, ping input
**Payload:** `127.0.0.1 && dir`
**Impact:** Application executed an attacker-supplied second command (`dir`) alongside the intended `ping`, proving arbitrary OS command execution.

The vulnerable code passed the raw `$_REQUEST['ip']` value straight into `shell_exec()`.

**Fix:** Applied `filter_var($target, FILTER_VALIDATE_IP)` to reject anything that isn't a syntactically valid IP address before it ever reaches the shell. Re-tested with the same payload — request rejected with "Invalid IP address."

See [`code-fixes/command_injection_fix.php`](code-fixes/command_injection_fix.php)

---

## 3. Reflected XSS

**Where:** `XSS (Reflected)` module, name parameter
**Payload:** `<script>alert('XSS-R')</script>`
**Impact:** Script executed immediately in the browser — a crafted link would run attacker JavaScript in any victim's session.

The app echoed `$_GET['name']` straight into the HTML response with zero encoding.

**Fix:** Wrapped the output in `htmlspecialchars($_GET['name'], ENT_QUOTES)`, converting `<` and `>` into safe HTML entities so the browser renders the payload as inert text. Re-tested — payload displayed as plain text, no execution.

See [`code-fixes/xss_reflected_fix.php`](code-fixes/xss_reflected_fix.php)

---

## 4. Unrestricted File Upload

**Where:** `File Upload` module
**Test:** Uploaded `New Text Document.php`
**Impact:** Server accepted and stored the `.php` file with a direct, guessable path — equivalent to remote code execution if that path were requested.

`move_uploaded_file()` was called with no extension or content-type check at all.

**Fix:** Added an extension allowlist (`jpg`, `jpeg`, `png`) validated via `pathinfo()` before the file is ever moved into the upload directory. Re-tested with the same `.php` file — upload rejected.

See [`code-fixes/file_upload_fix.php`](code-fixes/file_upload_fix.php)

---

## 5. Stored XSS

**Where:** `XSS (Stored)` guestbook
**Payload:** `<script>alert('Laiba')</script>`
**Impact:** Payload persisted in the database and fired for *every* visitor to the guestbook page — a stored, self-propagating XSS affecting all users, not just the submitter.

The message field was inserted into the database with no sanitization.

**Fix:** Sanitized input with `htmlspecialchars(..., ENT_QUOTES)` before the `INSERT`, so stored content renders as text rather than executable markup. Re-tested — payload displayed as literal text, no alert fired.

See [`code-fixes/xss_stored_fix.php`](code-fixes/xss_stored_fix.php)

---

## 6. Local File Inclusion (LFI)

**Where:** `File Inclusion` module, `page` parameter
**Payload:** `?page=../../../windows/win.ini`
**Impact:** Arbitrary local files were rendered directly from the server's filesystem via path traversal.

`$_GET['page']` was passed straight into `include()` with no validation.

**Fix:** Replaced free-form input with a strict allowlist of permitted filenames (`include.php`, `file1.php`, etc.) — anything not on the list is rejected with "Access Denied." Re-tested the traversal payload — blocked.

See [`code-fixes/lfi_fix.php`](code-fixes/lfi_fix.php)

---

## 7. Weak Cryptographic Storage (MD5)

**Where:** User authentication / password storage
**Finding:** Passwords hashed with unsalted MD5 — fast to brute-force and reversible via precomputed rainbow tables.

**Fix:** Replaced `md5()` with PHP's native `password_hash($password, PASSWORD_DEFAULT)`, which uses bcrypt and generates a unique salt per password automatically, defeating rainbow-table attacks even for identical passwords across accounts.

See [`code-fixes/weak_crypto_fix.php`](code-fixes/weak_crypto_fix.php)

---

## Design-Level Vulnerabilities (STRIDE-style review)

Beyond individual input-validation bugs, four **architectural** weaknesses were identified and remediated:

### 8. Missing CSRF Tokens (Tampering / Repudiation)
The admin password-change endpoint accepted state-changing GET requests with no CSRF token, so a malicious link clicked by an authenticated admin could silently change their password. **Fix:** added a per-session CSRF token, required and validated server-side on every state-changing request. Verified: request without a valid token is now rejected with "CSRF Token Missing or Invalid."

### 9. Broken Access Control (Elevation of Privilege)
Any authenticated user — not just admins — could directly access administrative modules by URL. **Fix:** added a server-side role check (`$_SESSION['username'] !== 'admin'`) before any privileged module loads. Verified: non-admin accounts now receive "Access Denied."

### 10. Inadequate Security Logging (Repudiation)
There was no audit trail — no way to determine who did what, or when. **Fix:** implemented a `log_security_event()` function writing timestamped, user-attributed entries to an audit log for sensitive actions (e.g. access attempts on the File Inclusion module). Verified: log file now captures each event with timestamp and username.

### 11. Plaintext Credentials in Configuration (Information Disclosure)
`config.inc.php` stored the live database password in plaintext in the source file. **Fix:** moved all sensitive values (DB password, DB user, reCAPTCHA keys) to environment variables read via `getenv()`, so credentials never live in version-controlled source.

---

## Key Takeaways

- Input validation has to happen **server-side**; client-side checks are trivially bypassed by intercepting the raw request in Burp Suite.
- The same two fixes — parameterized queries and output encoding — close the majority of classic injection vulnerabilities (SQLi, both XSS types).
- Architectural gaps (CSRF, access control, logging, secrets management) don't show up in a single-endpoint scan the way injection bugs do, but they're just as exploitable and were treated with the same discover → exploit → fix → verify process.

## Disclaimer

This project was conducted entirely against a local, self-hosted, intentionally vulnerable application designed for security training. No unauthorized systems were tested.

## Author

**Mahrukh** — BS Cyber Security student
[GitHub](https://github.com/mahrukh89) · [LinkedIn](https://linkedin.com/in/Ms.mahrukh)
