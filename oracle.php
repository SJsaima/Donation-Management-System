<?php
$conn = oci_connect('SYS', 'sys', 'localhost/XE',null,OCI_SYSDBA);

if (!$conn) {
    $e = oci_error();
    echo "Connection failed: " . $e['message'];
} else {
    echo "Connected to Oracle as SYSDBA!";
}
?>