# Finding 09 — Missing CSRF Tokens (Design Threat)

| | |
|---|---|
| **Category** | Design-level threat |
| **CWE** | CWE-352 (Cross-Site Request Forgery) |
| **STRIDE** | Tampering / Repudiation |
| **Severity** | High |
| **Module** | DVWA → Administrative password modification |

## 1. Discovery & Identification

**Action:** analysis of the administrative password-modification functionality within the application.

![Vulnerable form](screenshots/01-vulnerable-form.png)

**Observation:** the high-privilege, state-changing action (password modification) does not implement a unique, per-session CSRF token.

![Vulnerable code](screenshots/02-vulnerable-code.png)

## 2. Vulnerability Analysis (Before Fix)

**Tampering:** without CSRF tokens, the application is vulnerable to forged requests. An attacker can craft a malicious link or auto-submitting form that, if loaded by an authenticated administrator's browser, silently forces a password change — without the administrator's knowledge or consent.

**Repudiation issue:** because the forged request executes using the administrator's legitimate, already-authenticated session, the application has no way to prove whether the password change was intentionally initiated by the administrator or triggered by a malicious third-party script. This makes the action effectively non-repudiable from the server's point of view — a serious accountability gap.

## 3. Remediation (The Fix)

**Action:** implement a synchronizer token pattern (CSRF token) for all sensitive, state-changing operations.

Implementation details:

1. Generate a unique, cryptographically secure token and bind it to the user's session.
2. Include this token as a hidden field in the password-modification form.
3. Validate the presence **and** correctness of the token server-side before processing the password-update request — reject the request otherwise.

![Fix implementation 1](screenshots/03-fix-implementation-1.png)
![Fix implementation 2](screenshots/04-fix-implementation-2.png)

### Representative fix

```php
// On form render — generate and store a per-session token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
// <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

// On form submit — validate before processing
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('Invalid or missing CSRF token — request rejected.');
}
// ... proceed with password update ...
```

**Why it is secure:** a token tied to the legitimate session and unknown to a third-party site cannot be included by a forged cross-origin request. `hash_equals()` performs a constant-time comparison, avoiding timing side-channels. Since the forged request can never carry the correct token, the tampering vector is closed and the server can now trust that a validated request genuinely originated from the legitimate form.

## 4. Verification (Re-check)

**Action:** attempted to perform an administrative password-change request without including the required CSRF token.

![Verification](screenshots/05-verification.png)

**Final result:** the application rejects the request as unauthorized, confirming that only requests originating from the legitimate form interface (carrying a valid token) are processed.
