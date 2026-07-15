DVWA Security Analysis & Code Audit
This repository contains a targeted code-level security analysis and architectural audit of Damn Vulnerable Web Application (DVWA).

Rather than relying on automated scanners, this project focuses on a manual code-level review to identify why specific software design choices result in exploitable vulnerabilities.

Vulnerabilities Audited
SQL Injection (SQLi): Analyzed how direct concatenation of inputs into database query strings bypasses authentication controls.

Command Injection: Audited how passing unsanitized parameters directly to system shell execution sinks (shell_exec) allows remote code execution.

Stored Cross-Site Scripting (XSS): Reviewed how a lack of context-aware HTML output encoding allows malicious scripts to persist in the database and execute in victim browsers.

Project Scope
Code Analysis: Dissecting flawed PHP code implementations (Low/Medium security settings) to locate high-risk "Input-to-Sink" pathways.

Design Flaws: Identifying structural architecture flaws, such as relying purely on easily-bypassed client-side JavaScript validation.

Remediation: Comparing vulnerable source code side-by-side with secure, parameterized, and encoded code blocks (Impossible security setting) to demonstrate mitigation.
