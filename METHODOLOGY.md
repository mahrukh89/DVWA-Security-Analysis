# Methodology

## Approach

Each finding in this repository was worked through the same five-phase lifecycle:

1. **Discovery & Identification** — explore the target module, submit benign input, and observe how it's reflected/processed to form a hypothesis about the underlying code.
2. **Exploitation (Before Fix)** — craft and submit a payload designed to confirm the hypothesis (e.g. a SQL tautology, a shell metacharacter, a `<script>` tag, a path traversal string).
3. **Root Cause Analysis** — read the actual DVWA source for the module/security level under test to confirm *why* the payload worked.
4. **Remediation** — apply a standard, well-understood fix for the vulnerability class (parameterized queries, input validation/allowlisting, output encoding, safe hashing, etc.) and patch the source.
5. **Verification (Re-check)** — resubmit the exact same payload against the patched code and confirm it is now rejected, sanitized, or otherwise neutralized.

## Tools used

- **Browser DevTools** — inspecting responses and reflected input
- **Burp Suite (Community Edition)** — intercepting and modifying requests, replaying payloads
- **DVWA source browsing** — direct review of `low.php` / fixed implementations for each module
- **Manual payload crafting** — no automated scanners were relied on as the primary evidence source; every finding was manually reproduced

## Rules of engagement

- Testing was performed exclusively against a **local, self-hosted DVWA instance**.
- DVWA's built-in security level was used to select the vulnerable ("Low") implementation being audited.
- No destructive payloads were used beyond what was necessary to demonstrate impact (e.g. listing directory contents rather than deleting files).
- All fixes were implemented and re-tested in the same local environment before being documented.

## Severity rating

Severity was assigned qualitatively based on:

- **Impact** — what an attacker gains (data disclosure, code execution, account takeover, etc.)
- **Ease of exploitation** — whether the payload requires authentication, user interaction, or special conditions
- **Blast radius** — whether the issue affects a single user, all users, or the underlying server

| Severity | Meaning |
|---|---|
| Critical | Remote code execution, full database compromise, or full authentication bypass |
| High | Significant data exposure or integrity impact, typically with some precondition |
| Medium | Limited impact, or impact primarily on auditability/accountability rather than direct compromise |

## Reproducibility

Each finding's `README.md` includes the exact payload and request used, so any step can be independently reproduced against a fresh DVWA install set to the same security level.
