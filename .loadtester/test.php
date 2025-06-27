<?php

/**
 * Скрипт для нагрузочного тестирования сохранения сообщений клиента
 *
 * - Каждый клиент отправляет случайное количество сообщений
 * - Дубли и некорректные запросы (~ 90% валидных сообщений, ~ 5% дублей, ~ 5% невалидных)
 * - Один и тот же номер у разных клиентов (внешних источников)
 */

const REQUESTS   = 100_000; // общее количество сообщений
const CONCURRENT = 100;     // сколько одновременно слать
const API_URL    = 'http://nginx/api/v1/messages';

$logFile = __DIR__ . '/loadtester.log';
file_put_contents($logFile, "=== Тест начат " . date('Y-m-d H:i:s') . " ===\n", FILE_APPEND);

function logResult(string $message): void
{
    global $logFile;
    echo $message;
    file_put_contents($logFile, $message, FILE_APPEND);
}

$clients = [
    ['id' => 'client00000000000000000000000001', 'phone' => '+79990000001'],
    ['id' => 'client00000000000000000000000002', 'phone' => '+79990000002'],
    ['id' => 'client00000000000000000000000003', 'phone' => '+79990000003'],
    ['id' => 'client00000000000000000000000004', 'phone' => '+79990000004'],
    ['id' => 'client00000000000000000000000005', 'phone' => '+79990000005'],
    ['id' => 'client00000000000000000000000006', 'phone' => '+79990000001'],
    ['id' => 'client00000000000000000000000007', 'phone' => '+79990000006'],
    ['id' => 'client00000000000000000000000008', 'phone' => '+79990000007'],
    ['id' => 'client00000000000000000000000009', 'phone' => '+79990000008'],
    ['id' => 'client00000000000000000000000010', 'phone' => '+79990000009'],
];


// Генерация сообщений
$validMessages = [];
$maxValid      = intval(REQUESTS * 0.90);

while (count($validMessages) < $maxValid) {
    $client          = $clients[array_rand($clients)];
    $validMessages[] = [
        'external_client_id'  => $client['id'],
        'client_phone'        => $client['phone'],
        'external_message_id' => substr(bin2hex(random_bytes(16)), 0, 32),
        'message_text'        => 'Текст сообщения #' . count($validMessages),
        'send_at'             => time(),
    ];
}

// Дубликаты: 5% от REQUESTS
$duplicateMessages = array_slice($validMessages, 0, intval(REQUESTS * 0.05));

// Невалидные: ещё 5%
$invalidTemplates = [
    [],
    [
        'client_phone' => '+79991112233'
    ],
    [
        'external_client_id' => 'client0000000000000000000broken1'
    ],
    [
        'external_client_id' => 'client0000000000000000000broken2',
        'client_phone'       => ''
    ],
];
$invalidMessages  = [];
while (count($invalidMessages) < REQUESTS - count($validMessages) - count($duplicateMessages)) {
    $invalidMessages[] = $invalidTemplates[array_rand($invalidTemplates)];
}

// Объединяем и перемешиваем
$messages = array_merge($validMessages, $duplicateMessages, $invalidMessages);
shuffle($messages);

// Отправка
$multiHandle = curl_multi_init();
$handles     = [];
$success     = 0;
$fail        = 0;

function createCurlHandle(array $payload): CurlHandle|false
{
    $ch = curl_init(API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    return $ch;
}

foreach ($messages as $i => $payload) {

    //echo "Sending: " . json_encode($payload) . "\n";

    $ch          = createCurlHandle($payload);
    $handles[$i] = $ch;
    curl_multi_add_handle($multiHandle, $ch);

    if (($i + 1) % CONCURRENT === 0 || $i + 1 === count($messages)) {
        do {
            curl_multi_exec($multiHandle, $running);
            curl_multi_select($multiHandle);
        } while ($running > 0);

        foreach ($handles as $j => $h) {
            $response = curl_multi_getcontent($h);
            $code     = curl_getinfo($h, CURLINFO_HTTP_CODE);

            if ($code === 200 || $code === 201) {
                echo "[OK   #$j] HTTP $code\n";
                // logResult("[OK   #$j] HTTP $code\n"); // Раскомментируй если нужен лог успешных
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
logResult("Всего сообщений: " . count($messages) . "\n");
logResult("Успешно: $success\n");
logResult("Ошибки:  $fail\n");
