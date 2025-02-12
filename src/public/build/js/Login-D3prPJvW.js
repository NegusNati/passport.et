import{j as e,W as x,r as g,Y as f,a as h}from"./app-D1-6c0e8.js";import{G as b}from"./GuestLayout-B8GeFN-n.js";import{I as m}from"./InputError-DYwPtwfE.js";import{I as i}from"./InputLabel-ku1As_d0.js";import{P as j}from"./PrimaryButton-CmefnpE4.js";import{T as d}from"./TextInput-C10GYCUl.js";/* empty css            */import"./ApplicationLogo-BS_bBg2K.js";function w({className:s="",...a}){return e.jsx("input",{...a,type:"checkbox",className:"rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800 "+s})}function F({status:s,canResetPassword:a}){const{data:o,setData:t,post:u,processing:l,errors:n,reset:c}=x({phone_number:"",password:"",remember:!1});g.useEffect(()=>()=>{c("password")},[]);const p=r=>{r.preventDefault(),u(route("login"))};return e.jsxs(b,{children:[e.jsx(f,{title:"Log in"}),s&&e.jsx("div",{className:"mb-4 font-medium text-sm text-green-600",children:s}),e.jsxs("form",{onSubmit:p,children:[e.jsxs("div",{children:[e.jsx(i,{htmlFor:"phone_number",value:"Phone Number"}),e.jsx(d,{id:"phone_number",name:"phone_number",value:o.phone_number,className:"mt-1 block w-full",autoComplete:"phone_number",isFocused:!0,onChange:r=>t("phone_number",r.target.value),required:!0}),e.jsx(m,{message:n.phone_number,className:"mt-2"})]}),e.jsxs("div",{className:"mt-4",children:[e.jsx(i,{htmlFor:"password",value:"Password"}),e.jsx(d,{id:"password",type:"password",name:"password",value:o.password,className:"mt-1 block w-full",autoComplete:"current-password",onChange:r=>t("password",r.target.value)}),e.jsx(m,{message:n.password,className:"mt-2"})]}),e.jsx("div",{className:"block mt-4",children:e.jsxs("label",{className:"flex items-center",children:[e.jsx(w,{name:"remember",checked:o.remember,onChange:r=>t("remember",r.target.checked)}),e.jsx("span",{className:"ms-2 text-sm text-gray-600 dark:text-gray-400",children:"Remember me"})]})}),e.jsxs("div",{className:"flex items-center justify-end mt-4",children:[a&&e.jsx(h,{href:route("password.request"),className:"underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800",children:"Forgot your password?"}),e.jsx(j,{className:"ms-4",disabled:l,children:"Log in"})]})]})]})}export{F as default};
