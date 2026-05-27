// JavaScript Document

      let theme = "light";
      //添加暗色主题
      function addDarkTheme() {
        const link = document.createElement("link");
        link.id = "theme-css-dark"; // 加上id方便后面好查找到进行删除
        link.rel = "stylesheet";
        link.type = "text/css";
        link.href = "theme.css";
        document.querySelector("head").appendChild(link);
      }
      //移除暗色主题
      function removeDarkTheme() {
        document.querySelector("#theme-css-dark").remove();
      }
      //切换主题
      const changeTheme = () => {
        if (theme === "light") {
          addDarkTheme();
          theme = "dark";
        } else {
          removeDarkTheme();
          theme = "light";
        }
      };
    