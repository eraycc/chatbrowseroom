<?php
error_reporting(0);
session_start();

// 超级管理员密码，可自行设置多个
$SUPER_ADMIN_PASSWORD = 'test';

$SUPER_ADMIN_PASSWORD2 = 'test2';

// 数据库连接
$db = new SQLite3('chatroom.db');

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['admin_password']) && $_POST['admin_password'] === $SUPER_ADMIN_PASSWORD) {
        $_SESSION['is_super_admin'] = true;
    }
    
    if (isset($_POST['admin_password']) && $_POST['admin_password'] === $SUPER_ADMIN_PASSWORD2) {
        $_SESSION['is_super_admin'] = true;
    }

    // 房间管理
    if (isset($_POST['create_room'])) {
        $stmt = $db->prepare('INSERT OR REPLACE INTO rooms (room_name, room_password) VALUES (:name, :password)');
        $stmt->bindValue(':name', $_POST['room_name'], SQLITE3_TEXT);
        $stmt->bindValue(':password', $_POST['room_password'], SQLITE3_TEXT);
        $stmt->execute();
    }

    // 删除房间
    if (isset($_POST['delete_room'])) {
        $room_name = $_POST['room_name'];

        // 删除room_message
        $stmt1 = $db->prepare('DELETE FROM room_messages WHERE room_name = :room');
        $stmt1->bindValue(':room', $room_name, SQLITE3_TEXT);
        $stmt1->execute();

        //删除room_users
        $stmt2 = $db->prepare('DELETE FROM room_users WHERE room_name = :room');
        $stmt2->bindValue(':room', $room_name, SQLITE3_TEXT);
        $stmt2->execute();

        //删除room_urls
        $stmt3 = $db->prepare('DELETE FROM room_urls WHERE room_name = :room');
        $stmt3->bindValue(':room', $room_name, SQLITE3_TEXT);
        $stmt3->execute();

        //删除rooms
        $stmt4 = $db->prepare('DELETE FROM rooms WHERE room_name = :room');
        $stmt4->bindValue(':room', $room_name, SQLITE3_TEXT);
        $stmt4->execute();
    }

    // 用户管理
    if (isset($_POST['add_user'])) {
        $stmt = $db->prepare('INSERT OR REPLACE INTO room_users (room_name, username, is_admin) VALUES (:room, :username, :is_admin)');
        $stmt->bindValue(':room', $_POST['room_name'], SQLITE3_TEXT);
        $stmt->bindValue(':username', $_POST['username'], SQLITE3_TEXT);
        $stmt->bindValue(':is_admin', isset($_POST['is_admin']) ? 1 : 0, SQLITE3_INTEGER);
        $stmt->execute();
    }

    //删除用户
    if (isset($_POST['delete_user'])) {
        $stmt = $db->prepare('DELETE FROM room_users WHERE room_name = :room AND username = :username');
        $stmt->bindValue(':room', $_POST['room_name'], SQLITE3_TEXT);
        $stmt->bindValue(':username', $_POST['username'], SQLITE3_TEXT);
        $stmt->execute();
    }

    // 清空聊天记录
    if (isset($_POST['clear_chat'])) {
        $stmt = $db->prepare('DELETE FROM room_messages WHERE room_name = :room');
        $stmt->bindValue(':room', $_POST['room_name'], SQLITE3_TEXT);
        $stmt->execute();
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>聊天浏览室管理后台</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .admin-section {
            background-color: #f4f4f4;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        input, select {
            padding: 10px;
            margin: 10px 0;
        }
        button {
            padding: 10px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
        }
        button:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <?php if (!isset($_SESSION['is_super_admin'])): ?>
    <div class="admin-section">
        <h2>超级管理员登录</h2>
        <form method="post">
            <input type="password" name="admin_password" placeholder="超级管理员密码" required>
            <button type="submit">登录</button>
        </form>
    </div>
    <?php else: ?>
    <div class="admin-section">
        <h2>房间管理</h2>
        <form method="post">
            <input type="text" name="room_name" placeholder="房间名" required>
            <input type="password" name="room_password" placeholder="房间密码" required>
            <input type="hidden" name="create_room" value="1">
            <button type="submit">创建/修改房间</button>
        </form>

        <form method="post">
            <input type="text" name="room_name" placeholder="房间名" required>
            <input type="hidden" name="delete_room" value="1">
            <button type="submit">删除房间</button>
        </form>
    </div>

    <div class="admin-section">
        <h2>房间信息查询</h2>
        <form method="post">
            <select name="room_name" required>
                <?php 
                $rooms = $db->query('SELECT room_name FROM rooms');
                while ($room = $rooms->fetchArray(SQLITE3_ASSOC)) {
                    echo "<option value='{$room['room_name']}'>{$room['room_name']}</option>";
                }
                ?>
            </select>
            <input type="hidden" name="view_rooms" value="1">
            <button type="submit">查询房间信息</button>
        </form>

        <?php 
        if (isset($_POST['view_rooms'])) {
            $room_name = $_POST['room_name'];
            $messages = $db->prepare('SELECT * FROM rooms WHERE room_name = :room');
            $messages->bindValue(':room', $room_name, SQLITE3_TEXT);
            $result = $messages->execute();

            echo "<h3>{$room_name} 房间信息</h3>";
            echo "<table border='1' style='width:100%;'>";
            echo "<tr><th>房间名</th><th>房间密码</th></tr>";

            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['room_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['room_password']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        ?>
    </div>

    <div class="admin-section">
        <h2>房间用户管理</h2>
        <form method="post">
            <select name="room_name" required>
                <?php 
                $rooms = $db->query('SELECT room_name FROM rooms');
                while ($room = $rooms->fetchArray(SQLITE3_ASSOC)) {
                    echo "<option value='{$room['room_name']}'>{$room['room_name']}</option>";
                }
                ?>
            </select>
            <input type="text" name="username" placeholder="用户名" required>
            <label>
                <input type="checkbox" name="is_admin"> 设为管理员
            </label>
            <input type="hidden" name="add_user" value="1">
            <button type="submit">添加/修改用户</button>
        </form>

        <form method="post">
            <select name="room_name" required>
                <?php 
                $rooms = $db->query('SELECT room_name FROM rooms');
                while ($room = $rooms->fetchArray(SQLITE3_ASSOC)) {
                    echo "<option value='{$room['room_name']}'>{$room['room_name']}</option>";
                }
                ?>
            </select>
            <input type="text" name="username" placeholder="用户名" required>
            <input type="hidden" name="delete_user" value="1">
            <button type="submit">删除用户</button>
        </form>
    </div>

    <div class="admin-section">
        <h2>房间用户查看</h2>
        <form method="post">
            <select name="room_name" required>
                <?php 
                $rooms = $db->query('SELECT room_name FROM rooms');
                while ($room = $rooms->fetchArray(SQLITE3_ASSOC)) {
                    echo "<option value='{$room['room_name']}'>{$room['room_name']}</option>";
                }
                ?>
            </select>
            <input type="hidden" name="view_users" value="1">
            <button type="submit">查看房间用户</button>
        </form>

        <?php 
        if (isset($_POST['view_users'])) {
            $room_name = $_POST['room_name'];
            $messages = $db->prepare('SELECT * FROM room_users WHERE room_name = :room');
            $messages->bindValue(':room', $room_name, SQLITE3_TEXT);
            $result = $messages->execute();

            echo "<h3>{$room_name} 房间用户</h3>";
            echo "<table border='1' style='width:100%;'>";
            echo "<tr><th>房间名</th><th>用户名</th><th>用户组</th></tr>";

            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['room_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                $usergroup = $row['is_admin'] == 1 ? '管理员' : '普通用户';
                echo "<td>" . $usergroup . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        ?>
    </div>

    <div class="admin-section">
        <h2>房间聊天记录管理</h2>
        <form method="post">
            <select name="room_name" required>
                <?php 
                $rooms = $db->query('SELECT room_name FROM rooms');
                while ($room = $rooms->fetchArray(SQLITE3_ASSOC)) {
                    echo "<option value='{$room['room_name']}'>{$room['room_name']}</option>";
                }
                ?>
            </select>
            <input type="hidden" name="clear_chat" value="1">
            <button type="submit" onclick="return confirm('确定要清空该房间的聊天记录吗？')">清空聊天记录</button>
        </form>
    </div>

    <div class="admin-section">
        <h2>房间聊天记录查看</h2>
        <form method="post">
            <select name="room_name" required>
                <?php 
                $rooms = $db->query('SELECT room_name FROM rooms');
                while ($room = $rooms->fetchArray(SQLITE3_ASSOC)) {
                    echo "<option value='{$room['room_name']}'>{$room['room_name']}</option>";
                }
                ?>
            </select>
            <input type="hidden" name="view_chat" value="1">
            <button type="submit">查看聊天记录</button>
        </form>

        <?php 
        if (isset($_POST['view_chat'])) {
            $room_name = $_POST['room_name'];
            $messages = $db->prepare('SELECT * FROM room_messages WHERE room_name = :room ORDER BY timestamp DESC');
            $messages->bindValue(':room', $room_name, SQLITE3_TEXT);
            $result = $messages->execute();

            echo "<h3>{$room_name} 房间聊天记录</h3>";
            echo "<table border='1' style='width:100%;'>";
            echo "<tr><th>用户</th><th>消息</th><th>时间</th></tr>";

            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                echo "<td>" . htmlspecialchars(base64_decode($row['message'])) . "</td>";
                echo "<td>" . $row['timestamp'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        ?>
    </div>

    <div class="admin-section">
        <h2>房间浏览网址管理</h2>
        <form method="post">
            <select name="room_name" required>
                <?php 
                $rooms = $db->query('SELECT room_name FROM rooms');
                while ($room = $rooms->fetchArray(SQLITE3_ASSOC)) {
                    echo "<option value='{$room['room_name']}'>{$room['room_name']}</option>";
                }
                ?>
            </select>
            <input type="url" name="default_url" placeholder="房间浏览网址" required>
            <input type="hidden" name="set_default_url" value="1">
            <button type="submit">设置浏览网址</button>
        </form>

        <?php 
        if (isset($_POST['set_default_url'])) {
            $stmt = $db->prepare('REPLACE INTO room_urls (room_name, url) VALUES (:room, :url)');
            $stmt->bindValue(':room', $_POST['room_name'], SQLITE3_TEXT);
            $stmt->bindValue(':url', base64_encode($_POST['default_url']), SQLITE3_TEXT);
            $stmt->execute();
            echo "<p>房间浏览网址设置成功！</p>";
        }
        ?>
    </div>

    <div class="admin-section">
        <h2>房间浏览网址查看</h2>
        <form method="post">
            <select name="room_name" required>
                <?php 
                $rooms = $db->query('SELECT room_name FROM rooms');
                while ($room = $rooms->fetchArray(SQLITE3_ASSOC)) {
                    echo "<option value='{$room['room_name']}'>{$room['room_name']}</option>";
                }
                ?>
            </select>
            <input type="hidden" name="view_urls" value="1">
            <button type="submit">查看浏览网址</button>
        </form>

        <?php 
        if (isset($_POST['view_urls'])) {
            $room_name = $_POST['room_name'];
            $messages = $db->prepare('SELECT * FROM room_urls WHERE room_name = :room');
            $messages->bindValue(':room', $room_name, SQLITE3_TEXT);
            $result = $messages->execute();

            echo "<h3>{$room_name} 房间浏览网址</h3>";
            echo "<table border='1' style='width:100%;'>";
            echo "<tr><th>房间名</th><th>浏览网址</th></tr>";

            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['room_name']) . "</td>";
                echo "<td>" . htmlspecialchars(base64_decode($row['url'])) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        ?>
    </div>

    <div style="text-align:center;">
        <form method="post" action="">
            <button type="submit" name="logout">退出超级管理员</button>
        </form>
    </div>

    <?php 
    if (isset($_POST['logout'])) {
        unset($_SESSION['is_super_admin']);
        echo "<script>window.location.reload();</script>";
    }
    ?>
    <?php endif; ?>
</body>
</html>