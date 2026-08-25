<?php
// DVWA - SQL Injection (Low security level)
// VULNERABLE: user input is concatenated directly into the SQL string.

if (isset($_REQUEST['Submit'])) {
    // Grab the user ID from the request (UNSANITIZED)
    $id = $_REQUEST['id'];

    // Vulnerable SQL query — $id is embedded directly into the query string
    $query = "SELECT first_name, last_name FROM users WHERE user_id = '$id'";
    $result = mysqli_query($GLOBALS["___mysqli_ston"], $query);

    // Get results
    while ($row = mysqli_fetch_assoc($result)) {
        $first = $row["first_name"];
        $last  = $row["last_name"];

        echo "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
    }
}
?>
