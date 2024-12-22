<?php
error_reporting(0);
session_start();
header('Content-Type: application/json');

// 数据库连接
$db = new SQLite3('chatroom.db');

// 创建必要的表
$db->exec('CREATE TABLE IF NOT EXISTS room_urls (
    room_name TEXT PRIMARY KEY,
    url TEXT
)');

$db->exec('CREATE TABLE IF NOT EXISTS room_messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    room_name TEXT,
    username TEXT,
    message TEXT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
)');

// 接收POST数据
$data = json_decode(file_get_contents('php://input'), true);

switch ($data['action']) {
    case 'updateURL':
        // 更新房间URL
        $stmt = $db->prepare('REPLACE INTO room_urls (room_name, url) VALUES (:room, :url)');
        $stmt->bindValue(':room', $data['room'], SQLITE3_TEXT);
        $stmt->bindValue(':url', $data['url'], SQLITE3_TEXT);
        $stmt->execute();
        echo json_encode(['status' => 'success']);
        break;

    case 'sendMessage':
        // 发送消息
        if ($data['message'] === 'Y2xlYXI=' && $_SESSION['is_admin'] === 1) {  // base64编码的"Clear"
            if ($_SESSION['is_admin']) {
                $stmt = $db->prepare('DELETE FROM room_messages WHERE room_name = :room');
                $stmt->bindValue(':room', $data['room'], SQLITE3_TEXT);
                $stmt->execute();
            }
        } else {
            // 插入新消息
            $beijingTime = new DateTime('now', new DateTimeZone('Asia/Shanghai'));
            $timestamp = $beijingTime->format('Y-m-d H:i:s');
            $stmt = $db->prepare('INSERT INTO room_messages (room_name, username, message, timestamp) VALUES (:room, :username, :message, :timestamp)');
            $stmt->bindValue(':room', $data['room'], SQLITE3_TEXT);
            $stmt->bindValue(':username', $data['username'], SQLITE3_TEXT);
            $stmt->bindValue(':message', $data['message'], SQLITE3_TEXT);
            $stmt->bindValue(':timestamp', $timestamp, SQLITE3_TEXT);
            $stmt->execute();
        }
        echo json_encode(['status' => 'success']);
        break;

    case 'sync':
        // 同步URL
        $url_stmt = $db->prepare('SELECT url FROM room_urls WHERE room_name = :room');
        $url_stmt->bindValue(':room', $data['room'], SQLITE3_TEXT);
        $url_result = $url_stmt->execute();
        $url = $url_result->fetchArray(SQLITE3_ASSOC)['url'] ?? null;

        // 同步消息
        $msg_stmt = $db->prepare('SELECT username, message FROM room_messages WHERE room_name = :room ORDER BY timestamp DESC LIMIT 36');
        $msg_stmt->bindValue(':room', $data['room'], SQLITE3_TEXT);
        $msg_result = $msg_stmt->execute();
        
        $messages = [];
        while ($row = $msg_result->fetchArray(SQLITE3_ASSOC)) {
            $messages[] = $row;
        }

        echo json_encode([
            'url' => $url,
            'messages' => $messages
        ]);
        break;
}

$db->close();
?>