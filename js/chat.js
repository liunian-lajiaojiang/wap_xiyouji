// JavaScript Document
window.onload = function(){
            var btn = document.getElementById('btn');
            var text = document.getElementById('text');
            var content = document.getElementsByTagName('ul')[0];
            btn.onclick = function(){
                if(text.value ==''){
                    alert('不能发送空消息');
                }else {
                    content.innerHTML += '<a href="../humman.html">坏男人</a>：'+text.value+'<br />';
                    text.value = '';
                    // 内容过多时,将滚动条放置到最底端
                    content.scrollTop=content.scrollHeight;
                }
            }
        }