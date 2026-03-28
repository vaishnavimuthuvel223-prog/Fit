<?php
require_once 'db.php';
checkLogin();
$userId = $_SESSION['user_id'];

header('Content-Type: application/json');
$response = ['success' => false];

$action = $_POST['action'] ?? '';
if ($action === 'update_goals') {
    $stepsGoal = max(1, (int)$_POST['steps_goal']);
    $caloriesGoal = max(1, (int)$_POST['calories_goal']);
    $waterGoal = max(0.1, (float)$_POST['water_goal']);

    $stmt = $conn->prepare('UPDATE goals SET steps_goal = ?, calories_goal = ?, water_goal = ? WHERE user_id = ?');
    $stmt->bind_param('iidd', $stepsGoal, $caloriesGoal, $waterGoal, $userId);
    $stmt->execute();
    $stmt->close();
    $response['success'] = true;
}

if ($action === 'update_progress') {
    $steps = max(0, (int)$_POST['steps']);
    $calories = max(0, (int)$_POST['calories']);
    $water = max(0, (float)$_POST['water']);
    $date = date('Y-m-d');

    $stmt = $conn->prepare('UPDATE fitness_data SET steps = ?, calories = ?, water = ? WHERE user_id = ? AND date = ?');
    $stmt->bind_param('iidis', $steps, $calories, $water, $userId, $date);
    $stmt->execute();
    $affectedRows = $stmt->affected_rows;
    $stmt->close();

    if ($affectedRows === 0) {
        $stmt = $conn->prepare('INSERT INTO fitness_data (user_id, steps, calories, water, date) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('iidds', $userId, $steps, $calories, $water, $date);
        $stmt->execute();
        $stmt->close();
    }
    $response['success'] = true;
}

echo json_encode($response);
