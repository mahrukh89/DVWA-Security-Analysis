# Finding 01 — SQL Injection

| | |
|---|---|
| **Category** | Code-level vulnerability |
| **CWE** | CWE-89 (Improper Neutralization of Special Elements used in an SQL Command) |
| **Severity** | Critical |
| **Module** | DVWA → SQL Injection (Low) |

## 1. Discovery & Identification

We navigated to the **SQL Injection** page in DVWA and performed an initial test by entering `1` into the `User ID` field.

**Observation:** the application successfully retrieved the matching user's first and last name, confirming the page was interacting directly with the database.

![Find weak point](screenshots/01-find-weak-point.png)

## 2. Vulnerability Exploitation (Before Fix)

We entered the payload:

```
' OR '1'='1
```

into the `User ID` field.

![Before fix — source](screenshots/02-before-fix-code.png)
![Exploit attempt 1](screenshots/03-exploit-attempt-1.png)
![Exploit attempt 2](screenshots/04-exploit-attempt-2.png)

**Result:** the application returned **every** record in the `users` table, proving the input was not validated and that SQL injection was possible.

![Exploit result — full table dumped](screenshots/05-exploit-result.png)

### Vulnerable code

```php
$query = "SELECT first_name, last_name FROM users WHERE user_id = '$id'";
$result = mysqli_query($GLOBALS["___mysqli_ston"], $query);
```

**Why it was vulnerable:** the user's input was treated as part of the SQL command itself instead of as data. Because `'1'='1'` is always true, the `WHERE` clause is effectively neutralized and the database returns the entire table.

## 3. Remediation (The Fix)

We updated the source to use **prepared statements**, which separate the SQL command structure from the user-supplied data.

![Fix code 1](screenshots/06-fix-code-1.png)
![Fix code 2](screenshots/07-fix-code-2.png)

### Fixed code

```php
$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);
mysqli_stmt_bind_param($stmt, "s", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
```

**Why it is secure:** `mysqli_prepare()` defines the query structure up front. `mysqli_stmt_bind_param()` then binds `$id` strictly as a *literal string value* — never as executable SQL — so injected SQL syntax can no longer alter the query's logic.

## 4. Verification (Re-check)

Re-submitting the same `' OR '1'='1` payload against the patched code no longer returns the full table; it is treated as a literal (non-matching) `user_id` value.

![After fix verification](screenshots/08-after-fix-verification.png)

## Files

- [`before-fix.php`](before-fix.php) — original vulnerable handler
- [`after-fix.php`](after-fix.php) — remediated handler using prepared statements
