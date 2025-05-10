<?php
session_start();
session_destroy();
header('Location: ../../views/admin/adminLogin.php');
exit();

?>