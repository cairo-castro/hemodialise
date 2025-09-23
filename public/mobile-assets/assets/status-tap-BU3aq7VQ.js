import{S as i,T as a,U as c,V as d,W as l}from"./ionic-Duh8nJBT.js";import"./vendor-C2XN_qWx.js";/*!
 * (C) Ionic http://ionicframework.com - MIT License
 */const w=()=>{const e=window;e.addEventListener("statusTap",()=>{i(()=>{const o=e.innerWidth,s=e.innerHeight,n=document.elementFromPoint(o/2,s/2);if(!n)return;const t=a(n);t&&new Promise(r=>c(t,r)).then(()=>{d(async()=>{t.style.setProperty("--overflow","hidden"),await l(t,300),t.style.removeProperty("--overflow")})})})})};export{w as startStatusTap};
