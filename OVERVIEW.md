# Architecture & Attack Surface Overview

## Application stack

| Layer | Technology |
|---|---|
| Web server | Apache (XAMPP/WAMP-style local stack) |
| Language / runtime | PHP |
| Database | MySQL / MariaDB |
| Session handling | Native PHP sessions (`PHPSESSID`) |
| Front end | Server-rendered HTML, minimal JS |

## Request flow

```
 ┌────────────┐        HTTP(S)        ┌──────────────────┐        SQL         ┌──────────────┐
 │   Browser  │ ────────────────────▶ │  Apache + PHP     │ ─────────────────▶ │  MySQL / DB  │
 │  (attacker │ ◀──────────────────── │  (DVWA app logic) │ ◀───────────────── │              │
 │  / tester) │      HTML response     └──────────────────┘      result set     └──────────────┘
 └────────────┘
```

Each DVWA "module" (SQL Injection, Command Injection, XSS, File Upload, File Inclusion, etc.) is a standalone PHP page under `vulnerabilities/<module>/source/`, with a `low.php` / `medium.php` / `high.php` / `impossible.php` implementation selectable via the app's security-level setting. This audit primarily targeted the **Low** security level to establish a baseline, then re-verified fixes against the same level.

## Trust boundaries identified

1. **Browser → Application** — all user-controllable input (GET/POST parameters, uploaded files, cookies) crosses this boundary. Every finding in `01`–`06` and `08` originates from insufficient validation at this boundary.
2. **Application → Database** — SQL is built from request data (Finding 01) and, separately, passwords cross this boundary in a weakly-hashed form (Finding 07).
3. **Application → OS shell** — the Command Injection module (Finding 02) passes user input into `shell_exec()`, crossing directly into the operating system.
4. **Application → Filesystem** — the File Upload (04) and File Inclusion (06) modules read/write the filesystem based on user-supplied names/paths.
5. **Application → Configuration** — database credentials are read from a config file that, prior to remediation, stored the password in plaintext (Finding 12).
6. **Session / State management** — session tokens (08) and CSRF protections (09) govern whether a request can be trusted as coming from an authenticated, intentional action by the user — both were found to be weak or absent.
7. **Authorization layer** — access to admin-only functionality was not consistently checked against the authenticated user's role (Finding 10).

## Why DVWA was used

DVWA is deliberately built with configurable, realistic vulnerability patterns (Low/Medium/High/Impossible) that mirror mistakes seen in real PHP applications: string-concatenated SQL, unsanitized `echo`, unchecked `move_uploaded_file()`, raw `include()`, unsalted `md5()`, and missing CSRF tokens. That makes it a reasonable, safe/legal proxy for practicing a full vulnerability-assessment lifecycle — discovery, exploitation, remediation, and verification — without touching production systems.
