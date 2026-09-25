<?php
session_start();

session_unset();
session_destroy();

header("Location: ../actions/auth.php?action=logout");
exit;
