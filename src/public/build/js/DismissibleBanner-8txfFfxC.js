import{r as i,j as e}from"./app-D1-6c0e8.js";import{A as m}from"./index-VsXG4Swt.js";import{m as d}from"./proxy-Brcav4d7.js";const x=({text:n,bgColor:a})=>{const[r,s]=i.useState(!0);i.useEffect(()=>{if(sessionStorage.getItem("bannerDismissed"))s(!1);else{const c=setTimeout(()=>{t()},1e4);return()=>clearTimeout(c)}},[]);const t=()=>{s(!1),sessionStorage.setItem("bannerDismissed","true")},o={weekday:"long",year:"numeric",month:"long",day:"numeric"},l=new Date().toLocaleDateString("en-US",o).replace(",","/");return e.jsx(m,{children:r&&e.jsxs(d.div,{initial:{y:-100,opacity:0},animate:{y:0,opacity:1},exit:{y:-100,opacity:0},transition:{duration:.5},className:`fixed top-0 left-0 right-0 ${a} text-white p-4 flex justify-between items-center`,style:{zIndex:1e3},children:[e.jsx("div",{className:"flex-grow"}),e.jsxs("span",{className:"text-center flex-grow",children:[e.jsxs("p",{children:[" ",n]}),e.jsx("p",{className:"text-sm font-bold",children:`Updated Today, ${l}`})]}),e.jsx("div",{className:"flex-grow flex justify-end",children:e.jsx("button",{onClick:t,className:"bg-transparent border-0 text-white text-2xl",children:"×"})})]})})},h=x;export{h as D};
