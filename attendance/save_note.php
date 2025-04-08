<?php
include '../config/connection.php';

$id = $_POST['id'];
$note = $_POST['note'];

$stmt = $conn->prepare("UPDATE attendance SET note = ? WHERE id = ?");
$stmt->bind_param("si", $note, $id);
$success = $stmt->execute();

header('Content-Type: application/json');
echo json_encode(['success' => $success]);
?>