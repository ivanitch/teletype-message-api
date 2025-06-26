<?php

function logResult(string $message): void
{
    global $logFile;
    echo $message;
    file_put_contents($logFile, $message, FILE_APPEND);
}

$logFile = __DIR__ . '/loadtester.log';
file_put_contents($logFile, "=== Тест начат " . date('Y-m-d H:i:s') . " ===\n", FILE_APPEND);


const API_URL = 'http://nginx/api/v1/messages';

$clients = [
    ['id' => 'client00000000000000000000000001', 'phone' => '+79990000001'],
    ['id' => 'client00000000000000000000000002', 'phone' => '+79990000002'],
    ['id' => 'client00000000000000000000000003', 'phone' => '+79990000003'],
    ['id' => 'client00000000000000000000000004', 'phone' => '+79990000004'],
    ['id' => 'client00000000000000000000000005', 'phone' => '+79990000005'],
];

$requests   = 5000; // общее количество сообщений
$concurrent = 50;   // сколько одновременно слать

$multiHandle = curl_multi_init();
$handles     = [];
$success     = 0;
$fail        = 0;

for ($i = 0; $i < $requests; $i++) {
    $client = $clients[$i % count($clients)];

    $payload = [
        'external_client_id'  => $client['id'],
        'client_phone'        => $client['phone'],
        'external_message_id' => substr(bin2hex(random_bytes(16)), 0, 32),
        'message_text'        => 'Тест #' . $i,
        'send_at'             => time(),
    ];

    $ch = curl_init(API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    $handles[$i] = $ch;
    curl_multi_add_handle($multiHandle, $ch);

    //echo "Sending: " . json_encode($payload) . "\n";
    //curl_setopt($ch, CURLOPT_VERBOSE, true);


    // Блок отправки пачками
    if (($i + 1) % $concurrent === 0 || $i + 1 === $requests) {
        do {
            curl_multi_exec($multiHandle, $running);
            curl_multi_select($multiHandle);
        } while ($running > 0);

        foreach ($handles as $j => $h) {
            $response = curl_multi_getcontent($h);
            $code     = curl_getinfo($h, CURLINFO_HTTP_CODE);

            if ($code === 200 || $code === 201) {
                $success++;
            } else {
                $fail++;
                logResult("[FAIL #$j] HTTP $code — $response\n");
            }

            curl_multi_remove_handle($multiHandle, $h);
            curl_close($h);
        }

        $handles = [];
    }
}

curl_multi_close($multiHandle);

logResult("\n==== Результаты ====\n");
logResult("Успешно: $success\n");
logResult("Ошибки:  $fail\n");
