<?php
logoutUser();
header('Location: index.php?route=login');
header('Cache-Control: no-cache, no-store');
exit;
