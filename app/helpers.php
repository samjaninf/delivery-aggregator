<?php

/**
 * Create a POST request to Firebase containing the specified body
 *
 * @param array $body The body of the Firebase request
 *
 * @return array The result of the request or false if it failed
 */
function fb_curl_post($body)
{
    $FIREBASE_API_KEY = config('app.FIREBASE_API_KEY');
    $FIREBASE_PROJECT_ID = config('app.FIREBASE_PROJECT_ID');

    if (!$FIREBASE_API_KEY || !$FIREBASE_PROJECT_ID) {
        return false;
    }

    $headers = [
        "Authorization: key={$FIREBASE_API_KEY}",
        "project_id: {$FIREBASE_PROJECT_ID}",
        'Content-Type: application/json',
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/notification');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    $result = curl_exec($ch);
    curl_close($ch);

    return $result ? json_decode($result) : false;
}

function curl_get($url, $headers)
{
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $url,
        CURLOPT_HTTPHEADER => $headers,
    ]);

    $result = curl_exec($ch);
    $errorNo = curl_errno($ch);
    curl_close($ch);

    if ($errorNo) {
        throw new Exception([
            'code' => $errorNo,
            'error' => curl_error($ch),
        ]);
    }

    return $result ? json_decode($result) : false;
}

function curl_post($url, $headers, $body)
{
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_POST => 1,
        CURLOPT_URL => $url,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => http_build_query($body),
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $result = curl_exec($ch);
    $errorNo = curl_errno($ch);
    curl_close($ch);

    if ($errorNo) {
        throw new Exception([
            'code' => $errorNo,
            'error' => curl_error($ch),
        ]);
    }

    return $result ? json_decode($result) : false;
}
