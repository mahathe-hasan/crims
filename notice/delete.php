<?php

require_once("../config/session.php");
require_once("../config/database.php");

if(isset($_GET['id'])){

    $stmt = $conn->prepare("DELETE FROM notices WHERE id=?");
    $stmt->execute([$_GET['id']]);

}

header("Location: index.php?deleted=1");
exit();

?>