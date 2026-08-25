# Scope

## In scope

- The DVWA web application, running locally, at the **Low** security level (unless a finding states otherwise)
- The following DVWA modules:
  - SQL Injection
  - Command Injection
  - XSS (Reflected)
  - XSS (Stored)
  - File Upload
  - File Inclusion
  - Authentication / password storage (Brute Force & CSRF-adjacent password-change functionality)
  - Session management
- Design/architecture-level review of:
  - CSRF protection on state-changing requests
  - Role-based access control on administrative functionality
  - Security event logging
  - Handling of credentials in configuration files

## Out of scope

- The underlying host operating system, hypervisor, or network infrastructure
- Any DVWA module not explicitly listed in `findings/`
- Third-party dependencies bundled with DVWA (e.g. the web server or database engine itself) beyond how the application uses them
- Denial-of-service testing
- Any system other than the local DVWA instance — **no external or production targets were tested**

## Environment

- Local instance of DVWA (PHP + MySQL/MariaDB on Apache)
- All testing performed from `localhost`
- Security level: **Low**, used as the baseline for discovering and exploiting each issue; fixes were then verified against the same level with the patched source in place

## Assumptions

- The assessment assumes an attacker with the same access level as a standard, unauthenticated-or-low-privilege user of the application, escalating only where a specific finding (e.g. Broken Access Control) demonstrates that escalation is possible.
- Findings describe vulnerability classes that are broadly applicable to PHP web applications, not exploits specific to any particular DVWA build/version beyond what is shown in the screenshots.
