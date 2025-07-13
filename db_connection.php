<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Connect to Oracle
    $conn = oci_connect('SYS', 'sys', 'localhost/XE', 'AL32UTF8', OCI_SYSDBA);
    if (!$conn) {
        $e = oci_error();
        die("Connection failed: " . htmlentities($e['message']));
    }

    // Get SQL query
    $sql = isset($_POST['sql_query']) ? trim($_POST['sql_query']) : '';
    if (empty($sql)) {
        die("No SQL query provided.");
    }

    // Parse
    $stid = oci_parse($conn, $sql);
    if (!$stid) {
        $e = oci_error($conn);
        die("Parse error: " . htmlentities($e['message']));
    }

    // Execute
    $r = @oci_execute($stid);
    if (!$r) {
        $e = oci_error($stid);
        die("Execution error: " . htmlentities($e['message']));
    }

    // Output results (same as before)
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Query Results</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f7f7f7; padding: 20px; }
            .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.2); }
            table { border-collapse: collapse; width: 100%; margin-top: 20px; }
            th, td { border: 1px solid #ccc; padding: 8px; }
            th { background-color: #eee; }
            .success { color: green; margin-top: 20px; }
            .back-link { display: inline-block; margin-top: 20px; text-decoration: none; color: #007bff; }
            .back-link:hover { text-decoration: underline; }
        </style>
    </head>
    <body>
    <div class="container">
        <h1>Query Results</h1>
    <?php
    if (preg_match('/^\s*SELECT/i', $sql)) {
        $ncols = oci_num_fields($stid);
        echo "<table><tr>";
        for ($i = 1; $i <= $ncols; $i++) {
            echo "<th>" . htmlentities(oci_field_name($stid, $i)) . "</th>";
        }
        echo "</tr>";

        $rowCount = 0;
        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            echo "<tr>";
            foreach ($row as $item) {
                echo "<td>" . htmlentities($item !== null ? $item : "&nbsp;") . "</td>";
            }
            echo "</tr>";
            $rowCount++;
        }
        echo "</table>";
        if ($rowCount === 0) {
            echo "<p>No rows returned.</p>";
        }
    } else {
        echo "<p class='success'>Statement executed successfully.</p>";
    }
    ?>
        <a class="back-link" href="javascript:history.back()">&#8592; Back</a>
    </div>
    </body>
    </html>
    <?php
    oci_free_statement($stid);
    oci_close($conn);
} else {
    echo "This page must be accessed via the form submission.";
}
?>