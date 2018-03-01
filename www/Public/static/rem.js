'use strict';

function fontRem() {
  var designW = 720;
  var html = document.getElementsByTagName('html')[0];
  var winW = html.offsetWidth;
  html.style.fontSize = winW / designW * 100 + 'px';
}
fontRem();
window.onload = fontRem;
window.onresize = fontRem;