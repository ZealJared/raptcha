<?php declare(strict_types=1);
header('Content-Type: application/json');
session_start();
if (empty($_POST['email']) && !empty($_SESSION['result']) && is_string($_SESSION['result'])) {
  $resultString = is_object(json_decode($_SESSION['result'])) ? $_SESSION['result'] : json_encode([ 'error' => 'Something went wrong.' ]);
  die($resultString);
}
/** @var string|false|null $email */
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
if (!is_string($email)) {
  $_SESSION['result'] = json_encode([ 'error' => 'No E-mail address provided.' ], JSON_PRETTY_PRINT);
  header('Location: ./sendEmail.php');
  die();
}
$challengeId = is_string($_POST['challenge_id']) ? $_POST['challenge_id'] : '0';
$curlOptions = [
  CURLOPT_URL => 'http://localhost:1080/check_challenge',
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => http_build_query(['challenge_id' => $challengeId]),
  CURLOPT_RETURNTRANSFER => true,
];
$curlRequest = curl_init();
curl_setopt_array($curlRequest, $curlOptions);
$curlResponse = curl_exec($curlRequest);
if (is_bool($curlResponse)) {
  $_SESSION['result'] = json_encode([ 'error' => 'Communication error with challenge API.' ], JSON_PRETTY_PRINT);
  header('Location: ./sendEmail.php');
  die();
}
$apiResponse = json_decode($curlResponse);
if (!is_object($apiResponse) || !property_exists($apiResponse, 'passed') || !is_bool($apiResponse->passed)) {
  $_SESSION['result'] = json_encode([ 'error' => 'API returned invalid value.' ], JSON_PRETTY_PRINT);
  header('Location: ./sendEmail.php');
  die();
}
$pass = $apiResponse->passed;
if (!$pass) {
  $_SESSION['result'] = json_encode($apiResponse, JSON_PRETTY_PRINT);
  header('Location: ./sendEmail.php');
  die();
}
// send E-mail here
$_SESSION['result'] = json_encode(['email' => $email, 'status' => 'sent'], JSON_PRETTY_PRINT);
header('Location: ./sendEmail.php');
die();
