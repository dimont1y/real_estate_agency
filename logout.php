<?php
session_start();
session_destroy();
header("Location: /real_estate/index.php");
exit();
