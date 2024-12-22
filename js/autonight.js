// 创建一个新的 link 元素
const AutoDarkCss = document.createElement("link");
// 设置 link 元素的属性
AutoDarkCss.rel = "stylesheet";
AutoDarkCss.type = "text/css";
// 获取当前时间
const currentTime = new Date();
const currentHour = currentTime.getHours();
// 判断时间并加载不同的CSS
if (currentHour >= 6 && currentHour < 18) {
  AutoDarkCss.href = "./css/light.css?ver=1.1"; // 白天加载light.css
} else {
  AutoDarkCss.href = "./css/dark.css?ver=1.1"; // 晚上加载dark.css
}
// 将 link 元素添加到文档的 head 部分
document.head.appendChild(AutoDarkCss);
