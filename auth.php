<?php
error_reporting(0);
session_start();

// SQLite数据库连接
$db = new SQLite3('chatroom.db');

// 创建必要的表
$db->exec('CREATE TABLE IF NOT EXISTS rooms (
    room_name TEXT PRIMARY KEY,
    room_password TEXT
)');

$db->exec('CREATE TABLE IF NOT EXISTS room_users (
    room_name TEXT,
    username TEXT,
    is_admin INTEGER DEFAULT 0,
    PRIMARY KEY (room_name, username)
)');

$room_name = $_POST['room_name'];
$room_password = $_POST['room_password'];
$username = $_POST['username'];

// 验证房间是否存在
$stmt = $db->prepare('SELECT * FROM rooms WHERE room_name = :room_name AND room_password = :room_password');
$stmt->bindValue(':room_name', $room_name, SQLITE3_TEXT);
$stmt->bindValue(':room_password', $room_password, SQLITE3_TEXT);
$result = $stmt->execute();

if ($row = $result->fetchArray()) {
    // 验证用户是否存在
    $user_stmt = $db->prepare('SELECT * FROM room_users WHERE room_name = :room_name AND username = :username');
    $user_stmt->bindValue(':room_name', $room_name, SQLITE3_TEXT);
    $user_stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $user_result = $user_stmt->execute();

    if ($user_row = $user_result->fetchArray()) {
        // 存储用户信息到会话
        $_SESSION['room_name'] = $room_name;
        $_SESSION['username'] = $username;
        $_SESSION['is_admin'] = $user_row['is_admin'];

        // 跳转到聊天室
        header("Location: chatroom.php");
        exit();
    } else {
        echo "<script>alert('该房间不存在此用户，请联系管理员！'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('房间信息输入错误，或不存在该房间！'); window.history.back();</script>";
}
$db->close();
?>