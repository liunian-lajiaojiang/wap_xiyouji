// JavaScript Document
var talk = "士兵说到：有什么事吗？";
var	fight = "他似乎并不想跟你打架。";
var	kill = "士兵恶狠狠道：滚！";
var	bill = "士兵说到：我只有一件甲胄了。" + "<br>" + "他似乎不想跟你交易。";
var dazuo = "你坐下来运气用功，一股内息开始在体内流动。";
function Talk() {
document.getElementById("demo").innerHTML = talk;
}
function Fight() {
document.getElementById("demo").innerHTML = fight;
}
function Kill() {
document.getElementById("demo").innerHTML = kill;
}
function Bill() {
document.getElementById("demo").innerHTML = bill;
}
function Dazuo() {
document.getElementById("demo").innerHTML = dazuo;
}
