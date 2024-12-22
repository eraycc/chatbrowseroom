<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>聊天浏览室登陆</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: #f4f4f9;
        }
        .login-box {
            background: #fff;
            padding: 46px;
            border-radius: 18px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .login-box h2 {
            margin-bottom: 20px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 12px;
        }
        .form-group button {
            width: 100%;
            padding: 10px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
        }
        .form-group button:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>登录房间</h2>
        <form action="auth.php" method="POST">
            <div class="form-group">
                <label for="room_name">房间名</label>
                <input type="text" id="room_name" name="room_name" required>
            </div>
            <div class="form-group">
                <label for="room_password">房间密码</label>
                <input type="password" id="room_password" name="room_password" required>
            </div>
            <div class="form-group">
                <label for="username">用户名</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <button type="submit">进入房间</button>
            </div>
            <h3>开源 <a href="">Github</a> By Eray</h3>
        </form>
    </div>
</body>
</html>