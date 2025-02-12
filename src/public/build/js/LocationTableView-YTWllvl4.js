import{r as o,j as e,Y as c,a as x}from"./app-D1-6c0e8.js";import{A as m}from"./AuthGuestLayout-DCYq-5lN.js";import{f as h}from"./formarDate-BlP67w9g.js";import{P as p}from"./Pagination-DvVGFjep.js";/* empty css            */import"./ApplicationLogo-BS_bBg2K.js";import"./ThemeSelector-B68vem5a.js";import"./transition-CwOWG9eU.js";import"./Footer-DfvHVtGF.js";import"./index-VsXG4Swt.js";import"./proxy-Brcav4d7.js";function F({auth:l,passports:s,location:a}){let{data:r,links:y}=s;console.log("location : ",a,"passports : ",s);const[d,i]=o.useState(""),n=t=>{i(t===s.current_page?"":"opacity-0"),setTimeout(()=>i(""),500)};return e.jsxs(m,{user:l.user,header:e.jsxs("h2",{className:"font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight py-4 capitalize",children:["Daily updated passports ",a?"for "+a:""]}),children:[e.jsx(c,{title:"All Passports"}),e.jsxs("main",{className:"  max-w-[990px] m-auto  mb-20 bg-gray-200 rounded-2xl border border-transparent  hover:border-blue-500 transition-colors duration-300 group mt-8 py-8 selection:bg-[#FF2D20] selection:text-white bg-gradient-to-b from-slate-400 to-slate-100 dark:from-slate-400  dark:text-white/50 overflow-hidden shadow-sm sm:rounded-lg ",children:[e.jsx("div",{children:e.jsx("div",{className:"flex justify-center items-center",children:e.jsx("h2",{className:"font-bold text-3xl text-white dark:text-black leading-tight pb-4 capitalize   ",children:a||"Passports"})})}),e.jsxs("div",{className:"overflow-x-auto ",children:[e.jsxs("table",{className:"min-w-full divide-y-2 divide-gray-200 bg-white dark:bg-gray-300 text-sm p-4",children:[e.jsx("thead",{className:"ltr:text-left rtl:text-right",children:e.jsxs("tr",{className:"font-semibold",children:[e.jsx("th",{className:"whitespace-nowrap px-4 py-2 font-medium text-gray-900",children:"ID"}),e.jsx("th",{className:"whitespace-nowrap px-4 py-2 font-medium text-gray-900",children:"First Name"}),e.jsx("th",{className:"whitespace-nowrap px-4 py-2 font-medium text-gray-900",children:"Middle Name"}),e.jsx("th",{className:"whitespace-nowrap px-4 py-2 font-medium text-gray-900",children:"Last Name"}),e.jsx("th",{className:"whitespace-nowrap px-4 py-2 font-medium text-gray-900",children:"Date"}),e.jsx("th",{className:"whitespace-nowrap px-4 py-2 font-medium text-gray-900",children:"Request Number"}),e.jsxs("th",{className:"px-4 py-2",children:[" ","         "," "]})]})}),r.length>0?r==null?void 0:r.map(t=>e.jsx("tbody",{className:`divide-y divide-gray-200 pl-4 transition-opacity duration-500 ease-out ${d}`,children:e.jsxs("tr",{className:"hover:bg-gray-100 cursor-pointer pl-4",children:[e.jsxs("td",{className:"whitespace-nowrap px-4 py-2 font-medium text-gray-900",children:["#",t.id]}),e.jsx("td",{className:"whitespace-nowrap px-4 py-2 font-medium text-gray-900",children:t.firstName}),e.jsx("td",{className:"whitespace-nowrap px-4 py-2 text-gray-700",children:t.middleName}),e.jsx("td",{className:"whitespace-nowrap px-4 py-2 text-gray-700",children:t.lastName}),e.jsx("td",{className:"whitespace-nowrap px-4 py-2 text-gray-700",children:h(t.dateOfPublish)}),e.jsx("td",{className:"whitespace-nowrap px-4 py-2 text-gray-700",children:t.requestNumber}),e.jsx("td",{className:"whitespace-nowrap px-4 py-2",children:e.jsx(x,{href:route("passport.showDetail",{id:t.id}),className:"inline-block rounded bg-indigo-600 px-4 py-2 text-xs font-medium text-white  transition ease-in-out delay-100  hover:-translate-y-1 hover:scale-110 hover:bg-[#FF2D20] duration-200",children:"Detail"})})]},t.id)},t.id)):e.jsx("tbody",{className:"text-center text-gray-500 dark:text-gray-400",children:e.jsx("tr",{children:e.jsxs("td",{className:"whitespace-nowrap px-4 py-2 text-center",children:[" ","No data found"]})})})]}),e.jsx("div",{className:"p-4 m-4",children:e.jsx(p,{passports:s,handlePageChange:n})})]})]})]})}export{F as default};
