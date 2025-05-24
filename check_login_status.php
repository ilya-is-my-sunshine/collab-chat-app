<?php
session_start();
header('Content-Type: application/json'); // Tell the browser this is JSON

$response = ['loggedIn' => false];

if (isset($_SESSION['Sesh'])) {
    $response['loggedIn'] = true;

    $response['username'] = $_SESSION['username'];
}

echo json_encode($response);
?>