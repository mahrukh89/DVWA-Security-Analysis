# Findings Summary

| # | Finding | Category | CWE | Severity | Root Cause | Fix |
|---|---|---|---|---|---|---|
| 01 | [SQL Injection](../findings/01-sql-injection) | Code | CWE-89 | Critical | User input concatenated directly into SQL query | Parameterized queries via `mysqli_prepare` / bound parameters |
| 02 | [OS Command Injection](../findings/02-command-injection) | Code | CWE-78 | Critical | Raw input passed to `shell_exec()` | Strict input validation with `filter_var(..., FILTER_VALIDATE_IP)` |
| 03 | [Reflected XSS](../findings/03-reflected-xss) | Code | CWE-79 | High | User input echoed into HTML without encoding | Output encoding with `htmlspecialchars()` |
| 04 | [Unrestricted File Upload](../findings/04-unrestricted-file-upload) | Code | CWE-434 | Critical | No validation of uploaded file extension/type | Extension allowlist (`jpg`, `jpeg`, `png`) before `move_uploaded_file()` |
| 05 | [Stored XSS](../findings/05-stored-xss) | Code | CWE-79 | High | User input inserted into DB and rendered without encoding | Output encoding with `htmlspecialchars()` before storage/render |
| 06 | [Local File Inclusion](../findings/06-local-file-inclusion) | Code | CWE-98 | Critical | User-controlled path passed to `include()` | Allowlist of permitted filenames |
| 07 | [Weak Cryptographic Storage](../findings/07-weak-cryptographic-storage) | Code | CWE-916 | High | Passwords hashed with unsalted `md5()` | `password_hash()` with `PASSWORD_DEFAULT` (bcrypt) |
| 08 | [Weak Session ID Generation](../findings/08-weak-session-ids) | Code | CWE-330 | High | Predictable/low-entropy session identifiers, no regeneration on login | Cryptographically secure session tokens, `session_regenerate_id()` on privilege change |
| 09 | [Missing CSRF Protection](../findings/09-missing-csrf) | Design | CWE-352 | High | No anti-CSRF token on state-changing requests (e.g. password change) | Per-session synchronizer token, validated server-side |
| 10 | [Broken Access Control](../findings/10-broken-access-control) | Design | CWE-284 | Critical | No server-side role check on admin-only functionality | Session-based role check (`$_SESSION['username'] !== 'admin'`) |
| 11 | [Inadequate Security Logging](../findings/11-inadequate-security-logging) | Design | CWE-778 | Medium | No audit trail for sensitive/administrative actions | Centralized `log_security_event()` writing to `audit_log.txt` |
| 12 | [Plaintext Credentials in Config](../findings/12-plaintext-credentials) | Design | CWE-256/CWE-798 | High | DB password hardcoded in `config.inc.php` | Credentials read from environment variables (`getenv('DB_PASS')`) |

## Distribution

- **Code-level findings:** 8 (01–08)
- **Design-level findings:** 4 (09–12)
- **Critical:** 4 · **High:** 6 · **Medium:** 1 (totals reflect the higher of code/design severity per row above; see individual finding READMEs for full detail)

## Notable patterns across findings

- **Missing/failed input validation** is the root cause of the majority of code-level issues (01, 02, 04, 06) — reinforcing input validation + output encoding as the two highest-leverage fixes in this codebase.
- **Trust-boundary confusion** shows up repeatedly: user input was trusted as SQL (01), as a shell command (02), as HTML (03, 05), and as a filesystem path (04, 06).
- **Weak-by-default cryptographic and session choices** (07, 08) show the risk of relying on legacy PHP defaults instead of modern, purpose-built APIs (`password_hash()`, secure session config).
- **Design-level gaps** (09–12) are process/architecture issues rather than a single bad line of code, and required broader changes (token infrastructure, centralized auth checks, logging, secrets management) rather than a one-line patch.
