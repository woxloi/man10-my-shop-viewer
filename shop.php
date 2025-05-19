<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=UTF-8');

// POSTで受け取ったAPIキーを取得
$key = $_POST['key'] ?? null;

if (!$key) {
    http_response_code(400);
    echo json_encode(['error' => 'APIキーがありません']);
    exit;
}

$url = 'https://api.man10.red/v1/mshop/shop/list';

// cURL初期化
$ch = curl_init($url);

// cURLオプション設定
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $key],
    CURLOPT_FAILONERROR => false, // HTTPエラーでもレスポンスを取得
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_FOLLOWLOCATION => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// cURLエラーがあれば処理中断
if ($response === false) {
    $errorMsg = curl_error($ch);
    curl_close($ch);
    http_response_code(500);
    echo json_encode(['error' => "cURLエラー: $errorMsg"]);
    exit;
}

curl_close($ch);

// HTMLレスポンスが返された場合の対処（ログインページなどが返っている可能性）
if (stripos($response, '<html') !== false) {
    http_response_code(500);
    echo json_encode(['error' => 'APIがHTMLを返しました。ログインが必要な可能性があります。']);
    exit;
}

// HTTPステータスが400以上ならエラーとして返す
if ($httpCode >= 400) {
    http_response_code($httpCode);

    // すでにJSON形式の可能性があるためJSONチェックして返す
    $json = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo json_encode($json);
    } else {
        // JSONでなければ文字列のまま返す
        echo json_encode(['error' => $response]);
    }
    exit;
}

// 正常時はそのままAPIレスポンスを返す
echo $response;
