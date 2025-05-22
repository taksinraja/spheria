<?php
session_start();
session_destroy();
header("Location: /spheria1/login.php");
exit();
?>