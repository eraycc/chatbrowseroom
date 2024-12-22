<?php
error_reporting(0);
session_start();
if (!isset($_SESSION['room_name'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>聊天浏览室</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        iframe {
            border: none;
            width: 100%;
            height: 52%;
        }
        .input-box {
            display: flex;
            padding: 10px;
            background: #f1f1f1;
        }
        .input-box input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .input-box button {
            padding: 10px;
            background: #4CAF50;
            color: white;
            border: none;
            margin-left: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .input-box button:hover {
            background: #45a049;
        }
        .chat-area {
            flex: 1;
            overflow-y: auto;
            padding: 6px;
            border-top: 1px solid #ddd;
            background: #f9f9f9;
        }
        .mymsg {
            background-color: #95EC69;
            color: black;
            border-radius: 12px;
            padding: 3px;
            margin-bottom: 6px;
        }
        .othermsg {
            background-color: #0099FF;
            color: white;
            border-radius: 12px;
            padding: 3px;
            margin-bottom: 6px;
        }
    </style>
</head>
<body>
    <iframe id="browser-frame" class="darkmode-ignore" src="about:blank" allowFullscreen="true" allowtransparency="true" sandbox="allow-top-navigation allow-same-origin allow-forms allow-scripts"></iframe>
    <?php if($_SESSION['is_admin']){ ?>
    <div class="input-box">
        <input type="text" id="url-input" placeholder="输入网址...">
        <button onclick="loadURL()">浏览</button>
    </div>
    <?php } ?>
    <div class="chat-area">
        <div class="darkmode-ignore" id="chat-container">
        <!-- 聊天内容动态插入 -->
        </div>
    </div>
    <div class="input-box">
        <input type="text" id="chat-input" placeholder="输入聊天内容...">
        <button onclick="sendMessage()">发送</button>
    </div>
    
    <script>
        const roomName = "<?php echo $_SESSION['room_name']; ?>";
        const username = "<?php echo $_SESSION['username']; ?>";
        const isAdmin = <?php echo $_SESSION['is_admin']; ?>;

        function stringToBase64(str) {
            return btoa(unescape(encodeURIComponent(str)));
        }
        
        function base64ToString(b64) {
            return decodeURIComponent(escape(atob(b64)));
        }

        function loadURL() {
            const url = document.getElementById('url-input').value;
            if(!url){
              alert("需要输入网址才能一起浏览呀！");
              return false;
            }
            fetch('update.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'updateURL',
                    url: stringToBase64(url),
                    room: roomName
                })
            });
            document.getElementById('browser-frame').src = url;
        }

        function sendMessage() {
            var messages = document.getElementById('chat-input').value;
            if(!messages){
                alert("输入完消息再发送呀！");
                return false;
            }
            var message = stringToBase64(messages);
            fetch('update.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'sendMessage',
                    message: message,
                    username: username,
                    room: roomName
                })
            });
            document.getElementById('chat-input').value = '';
        }

        function syncData() {
            fetch('update.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'sync',
                    room: roomName
                })
            })
            .then(response => response.json())
            .then(data => {
                // 处理URL同步
                if (data.url) {
                    const decodedURL = encodeURI(base64ToString(data.url));
                    const iframeURL = document.getElementById('browser-frame').src;
                    /*
                    console.log("获取的："+data.url);
                    console.log("解码的："+decodedURL);
                    console.log("框架的："+iframeURL);
                    */
                    if (iframeURL !== decodedURL) {
                        document.getElementById('browser-frame').src = decodedURL;
                        document.getElementById('url-input').value = decodedURL;
                    }
                }

                // 处理聊天消息同步
                if (data.messages) {
                    const chatContainer = document.getElementById('chat-container');
                    chatContainer.innerHTML = "";
                    data.messages.forEach(msg => {
                        const decodedMsg = base64ToString(msg.message);
                        let classToAdd = msg.username === username ? 'mymsg' : 'othermsg';
                        chatContainer.innerHTML += `<div class="${classToAdd}"><p><strong>${msg.username}:</strong> ${decodedMsg}</p></div>`;
                    });
                    //chatContainer.scrollTop = -chatContainer.scrollHeight;
                }
            });
        }

        // 每3秒同步一次
        setInterval(syncData, 3000);
    </script>
<script type="text/javascript" src="./js/autonight.js?ver=20241210"></script>
</body>
</html>