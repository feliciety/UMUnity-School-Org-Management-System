<?php
require_once(__DIR__ . "/../../../database/config.php");

if (isset($_POST["org_id"]) && isset($_POST["leader_id"])) {
    $orgId = intval($_POST["org_id"]);
    $leaderId = intval($_POST["leader_id"]);

    $stmt = $conn->prepare("UPDATE organizations SET leader_id = ? WHERE org_id = ?");
    $stmt->bind_param("ii", $leaderId, $orgId);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
} else {
    echo "invalid request";
}
