System.register(["./ionic-legacy-DmxRaRhy.js","./vendor-legacy-C5HeBJnl.js"],function(t,e){"use strict";var n,r,i;return{setters:[t=>{n=t.M,r=t.N,i=t.O},null],execute:function(){
/*!
             * (C) Ionic http://ionicframework.com - MIT License
             */
t("createSwipeBackGesture",(t,e,o,s,c)=>{const a=t.ownerDocument.defaultView;let l=n(t);const u=t=>l?-t.deltaX:t.deltaX;return r({el:t,gestureName:"goback-swipe",gesturePriority:101,threshold:10,canStart:r=>(l=n(t),(t=>{const{startX:e}=t;return l?e>=a.innerWidth-50:e<=50})(r)&&e()),onStart:o,onMove:t=>{const e=u(t)/a.innerWidth;s(e)},onEnd:t=>{const e=u(t),n=a.innerWidth,r=e/n,o=(t=>l?-t.velocityX:t.velocityX)(t),s=o>=0&&(o>.2||e>n/2),d=(s?1-r:r)*n;let h=0;if(d>5){const t=d/Math.abs(o);h=Math.min(t,540)}c(s,r<=0?.01:i(0,r,.9999),h)}})})}}});
