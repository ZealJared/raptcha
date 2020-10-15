<?php
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$challengeId = filter_input(INPUT_POST, 'challenge_id', FILTER_SANITIZE_STRING);
$curlOptions = [
  CURLOPT_URL => 'http://localhost:1080/check_challenge',
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => http_build_query(['challenge_id' => $challengeId]),
  CURLOPT_RETURNTRANSFER => true
];
$curlRequest = curl_init();
curl_setopt_array($curlRequest, $curlOptions);
$curlResponse = curl_exec($curlRequest);
$apiResponse = json_decode($curlResponse);
$pass = $apiResponse->passed;
header('Content-Type: application/json');
if (!$pass) {
  die(json_encode($apiResponse, JSON_PRETTY_PRINT));
}
// send E-mail here
die(json_encode(['email' => $email, 'status' => 'sent'], JSON_PRETTY_PRINT));
