<?php

const REQUESTS   = 1000;                       // Общее количество запросов
const CONCURRENT = 100;                        // Кол-во одновременно отправляемых запросов
const API_URL    = 'http://nginx/api/v1/messages';

$logFile = __DIR__ . '/logs/loadtester.log';

function logResult(string $message): void {
    global $logFile;
    echo $message;
    file_put_contents($logFile, $message, FILE_APPEND);
}

// Инициализация замеров
$startTime = microtime(true);
$startMemory = memory_get_usage();

file_put_contents($logFile, "=== Тест начат " . date('Y-m-d H:i:s') . " ===\n", FILE_APPEND);

// --- 1. Клиенты (некоторые дублируют номера)
$clients = [
    ['id' => 'client00000000000000000000000001', 'phone' => '+79990000001'],
    ['id' => 'client00000000000000000000000002', 'phone' => '+79990000002'],
    ['id' => 'client00000000000000000000000003', 'phone' => '+79990000003'],
    ['id' => 'client00000000000000000000000004', 'phone' => '+79990000004'],
    ['id' => 'client00000000000000000000000005', 'phone' => '+79990000005'],
    ['id' => 'client00000000000000000000000006', 'phone' => '+79990000001'], // дубликат номера (это два разных внешних источника)
    ['id' => 'client00000000000000000000000007', 'phone' => '+79990000006'],
    ['id' => 'client00000000000000000000000008', 'phone' => '+79990000007'],
    ['id' => 'client00000000000000000000000009', 'phone' => '+79990000008'],
    ['id' => 'client00000000000000000000000010', 'phone' => '+79990000009'],
];

// --- 2. Пропорции
$validCount     = intdiv(REQUESTS * 90, 100);
$duplicateCount = intdiv(REQUESTS * 5, 100);
$invalidCount   = REQUESTS - $validCount - $duplicateCount;

// --- 3. Генерация валидных сообщений
$validMessages = [];
for ($i = 0; $i < $validCount; $i++) {
    $client = $clients[array_rand($clients)];
    $validMessages[] = [
        'external_client_id'  => $client['id'],
        'client_phone'        => $client['phone'],
        'external_message_id' => bin2hex(random_bytes(16)),
        'message_text'        => "Сообщение #$i от {$client['id']}",
        'send_at'             => time(),
    ];
}

// --- 4. Дубликаты
$duplicateMessages = array_slice($validMessages, 0, $duplicateCount);

// --- 5. Невалидные
$invalidTemplates = [
    [],
    ['client_phone' => '+79991112233'],
    ['external_client_id' => 'client0000000000000000000broken1'],
    [
        'external_client_id' => 'client0000000000000000000broken2',
        'client_phone'       => ''
    ],
];
$invalidMessages = [];
while (count($invalidMessages) < $invalidCount) {
    $invalidMessages[] = $invalidTemplates[array_rand($invalidTemplates)];
}

// --- 6. Перемешка
$messages = array_merge($validMessages, $duplicateMessages, $invalidMessages);
shuffle($messages);

// --- 7. Отправка
$multiHandle = curl_multi_init();
$handles     = [];

$success      = 0;
$fail         = 0;
$duplicates   = 0;
$invalid      = 0;
$otherErrors  = 0;

function createCurlHandle(array $payload): CurlHandle|false {
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
    $ch = createCurlHandle($payload);
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
            $payloadStr = json_encode($messages[$j], JSON_UNESCAPED_UNICODE);

            $isJson = str_starts_with(trim($response), '{') && json_decode($response) !== null;
            $parsed = $isJson ? json_decode($response, true) : [];

            if ($code === 200 || $code === 201) {
                echo "[OK   #$j] HTTP $code\n";
                $success++;
            } elseif ($code === 400 && isset($parsed['message'])) {
                $fail++;
                $msg = mb_strtolower($parsed['message']);

                if (str_contains($msg, 'дубликат')) {
                    $duplicates++;
                } elseif (
                    str_contains($msg, 'не передан') ||
                    str_contains($msg, 'невалид') ||
                    str_contains($msg, 'некоррект') ||
                    str_contains($msg, 'обязателен')
                ) {
                    $invalid++;
                } else {
                    $otherErrors++;
                }

                logResult("[FAIL #$j] HTTP $code\nPayload: $payloadStr\nОтвет: $response\n");
            } else {
                $fail++;
                $otherErrors++;
                logResult("[FAIL #$j] HTTP $code\nPayload: $payloadStr\nОтвет: $response\n");
            }

            curl_multi_remove_handle($multiHandle, $h);
            curl_close($h);
        }

        $handles = [];
    }
}

curl_multi_close($multiHandle);

// --- 8. Финальный лог
$duration = microtime(true) - $startTime;
$memoryUsed = memory_get_usage() - $startMemory;

logResult("\n=== Результаты ===\n");
logResult("Всего сообщений: " . count($messages) . "\n");
logResult("Успешно (2xx): $success\n");
logResult("Ошибки всего: $fail\n");
logResult(" - Дубликаты: $duplicates\n");
logResult(" - Невалидные: $invalid\n");
logResult(" - Прочие ошибки: $otherErrors\n");
logResult("Время выполнения: " . round($duration, 2) . " сек\n");
logResult("Использовано памяти: " . round($memoryUsed / 1024 / 1024, 2) . " MB\n");

