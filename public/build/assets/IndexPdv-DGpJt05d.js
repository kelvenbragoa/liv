import{s as H}from"./index-BxoxFt3q.js";import{s as I}from"./index-DXRr9s5p.js";import{s as L}from"./index-C9eKcGB-.js";import{s as T}from"./index-CSQGv-FN.js";import{u as Q,r as s,w as W,d as G,c as d,b as e,a as o,t as i,e as _,F as b,o as c,N as P,n as j,f as B,i as J}from"./app-YFIdyBAI.js";import{d as K,h as Y}from"./moment-CQ1ixRO1.js";import"./index-B1pdew71.js";import"./index-wAzSaUqS.js";import"./index-CTjzzlTF.js";import"./index-CizBcoq2.js";import"./index-DivXqZU5.js";import"./index-QWZZwtfY.js";import"./index-Buwx1Nwl.js";import"./index-vMMweMWc.js";import"./index-Ek7lvqn1.js";import"./index-DQULIKzD.js";import"./index-DeV7dN66.js";import"./index-BNAmUDWR.js";const O={key:0,class:"flex flex-col md:flex-row gap-12 min-h-screen items-center justify-center"},X={class:"w-full"},Z={class:"flex flex-col gap-4 text-center"},ee={key:1},te={class:"grid grid-cols-12 gap-4"},se={class:"card col-span-12 lg:col-span-6 xl:col-span-6"},ae={class:"font-semibold text-xl"},le={class:"flex flex-col"},oe={class:"flex flex-col md:flex-row justify-between md:items-center flex-1 gap-6"},ie={class:"flex flex-row md:flex-col justify-between items-start gap-2"},re={class:"font-medium text-surface-500 dark:text-surface-400 text-sm"},ne={class:"text-lg font-medium mt-2"},de={class:"font-medium text-surface-500 dark:text-surface-400 text-sm"},ce={class:"flex flex-col md:items-end gap-8"},ue={class:"flex flex-row-reverse md:flex-row gap-2"},me={class:"card col-span-12 lg:col-span-6 xl:col-span-6"},fe={class:"font-semibold text-xl"},pe={class:"flex flex-col"},ve={class:"flex flex-col md:flex-row justify-between md:items-center flex-1 gap-6"},xe={class:"flex flex-row md:flex-col justify-between items-start gap-2"},_e={class:"font-medium text-surface-500 dark:text-surface-400 text-sm"},ge={class:"text-lg font-medium mt-2"},he={class:"font-medium text-surface-500 dark:text-surface-400 text-sm"},qe={__name:"IndexPdv",setup(ye){const w=J(),u=Q();s(null);const g=s(!0),r=s(!1);let h=s(null);const k=s("");s(null);const M=s(1);s(15);const E=s(0),C=s(!1),m=s(!1),f=s([]),F=s([]),N=s([]),p=s([]);s(null),s(null),s(null);function R(){w&&w.back()}const y=()=>{m.value=!1},z=a=>{m.value=!0,h.value=a},D=async(a=1)=>{axios.get(`/api/pdvbar?page=${a}`,{params:{query:k.value}}).then(t=>{f.value=t.data.order_itens_pending,p.value=t.data.order_itens_delivered,E.value=t.data.total,g.value=!1}).catch(t=>{g.value=!1,u.add({severity:"error",summary:`${t}`,detail:"Message Detail",life:3e3}),R()})},U=()=>{r.value=!0,axios.delete(`/api/tables/${h.value}`).then(()=>{f.value=response.data.order_itens_pending,p.value=response.data.order_itens_delivered,y(),u.add({severity:"success",summary:"Sucesso",detail:"Sucesso ao apagar",life:3e3})}).catch(a=>{u.add({severity:"error",summary:"Erro",detail:`${a}`,life:3e3}),r.value=!1}).finally(()=>{r.value=!1})},V=()=>{r.value=!0,axios.get(`/api/barchangestatus/${h.value}`).then(a=>{f.value=a.data.order_itens_pending,F.value=a.data.order_itens_getting_ready,N.value=a.data.order_itens_ready,p.value=a.data.order_itens_delivered,r.value=!1,y(),u.add({severity:"success",summary:"Sucesso",detail:"Sucesso ao transitar o estado.",life:3e3})}).catch(a=>{u.add({severity:"error",summary:"Erro",detail:`${a}`,life:3e3}),r.value=!1}).finally(()=>{r.value=!1})},q=K(()=>{D(M.value)},300);return W(k,q),G(()=>{D()}),(a,t)=>{const A=T,v=L,S=I,$=H;return c(),d(b,null,[g.value?(c(),d("div",O,[e("div",X,[e("div",Z,[o(A,{style:{width:"50px",height:"50px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"}),t[3]||(t[3]=e("p",null,"Por Favor Aguarde...",-1))])])])):(c(),d("div",ee,[e("div",te,[e("div",se,[e("div",ae,"Pendentes("+i(f.value.length)+")",1),o(S,{value:f.value,paginator:"",rows:50,layout:"list"},{list:_(n=>[e("div",le,[(c(!0),d(b,null,P(n.items,(l,x)=>(c(),d("div",{key:x},[e("div",{class:j(["flex flex-col sm:flex-row sm:items-center p-6 gap-4",{"border-t border-surface":x!==0}])},[e("div",oe,[e("div",ie,[e("div",null,[e("span",re,i(l.order.table?l.order.table.name:"Pedido Rápido")+" | #"+i(l.order.id),1),e("div",ne,i(l.product.name),1),e("span",de,i(B(Y)(l.created_at).format("DD-MM-YYYY H:mm")),1)])]),e("div",ce,[e("div",ue,[o(v,{onClick:be=>z(l.id),icon:"pi pi-check",class:"flex-auto md:flex-initial whitespace-nowrap"},null,8,["onClick"])])])])],2)]))),128))])]),_:1},8,["value"])]),e("div",me,[e("div",fe,"Entregue("+i(p.value.length)+")",1),o(S,{value:p.value,paginator:"",rows:50,layout:"list"},{list:_(n=>[e("div",pe,[(c(!0),d(b,null,P(n.items,(l,x)=>(c(),d("div",{key:x},[e("div",{class:j(["flex flex-col sm:flex-row sm:items-center p-6 gap-4",{"border-t border-surface":x!==0}])},[e("div",ve,[e("div",xe,[e("div",null,[e("span",_e,i(l.order.table?l.order.table.name:"Pedido Rápido")+" | #"+i(l.order.id),1),e("div",ge,i(l.product.name),1),e("span",he,i(B(Y)(l.created_at).format("DD-MM-YYYY H:mm")),1)])]),t[4]||(t[4]=e("div",{class:"flex flex-col md:items-end gap-8"},null,-1))])],2)]))),128))])]),_:1},8,["value"])])])])),o($,{header:"Confirmação",visible:C.value,"onUpdate:visible":t[0]||(t[0]=n=>C.value=n),style:{width:"350px"},modal:!0},{footer:_(()=>[o(v,{label:"Não",icon:"pi pi-times",onClick:y,class:"p-button-text"}),o(v,{label:"Sim",icon:"pi pi-check",onClick:U,class:"p-button-text",autofocus:""})]),default:_(()=>[t[5]||(t[5]=e("div",{class:"flex align-items-center justify-content-center"},[e("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}}),e("span",null,"Tem certeza que deseja proceder?")],-1))]),_:1},8,["visible"]),o($,{header:"Deseja avançar o pedido para o próximo estado?",visible:m.value,"onUpdate:visible":t[2]||(t[2]=n=>m.value=n),style:{width:"30vw"}},{default:_(()=>[t[6]||(t[6]=e("p",null,"Ao clicar em avançar, seu pedido será adicionado em outra tabela de referência.",-1)),o(v,{class:"m-4",label:"Fechar",severity:"danger",onClick:t[1]||(t[1]=n=>m.value=!1)}),o(v,{class:"m-4",label:"Proximo Estado",disabled:r.value==!0,onClick:V},null,8,["disabled"])]),_:1},8,["visible"])],64)}}};export{qe as default};
