<?php
include '../config/connection.php';

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT note FROM attendance WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

header('Content-Type: application/json');
echo json_encode(['note' => $row['note'] ?? '']);
?>