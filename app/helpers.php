<?php

function fb_curl_post($body)
{
  $FIREBASE_API_KEY = env('FIREBASE_API_KEY', null);
  $FIREBASE_PROJECT_ID = env('FIREBASE_PROJECT_ID', null);

  if (!$FIREBASE_API_KEY || !$FIREBASE_PROJECT_ID)
    return false;

  $headers = [
    "Authorization: key={$FIREBASE_API_KEY}",
    "project_id: {$FIREBASE_PROJECT_ID}",
    'Content-Type: application/json'
  ];

  $ch = curl_init();
  curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/notification' );
  curl_setopt( $ch,CURLOPT_POST, true );
  curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
  curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
  curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
  curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $body ) );
  $result = curl_exec($ch );
  curl_close( $ch );

  return $result ? json_decode($result) : false;
}