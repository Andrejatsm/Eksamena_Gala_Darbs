<?php
session_start();
// Legacy page kept for older links.
// Speciālisti izmanto to pašu ielogošanos kā visi pārējie.
header("Location: login.php");
exit();