import{s as H}from"./index-CML4I8xk.js";import{s as L}from"./index-D4UqmU6h.js";import{s as T}from"./index-DsYGz-yW.js";import{s as Q}from"./index-DoDxNUM1.js";import{u as W,r as s,w as G,b as J,aS as K,c as d,d as e,a as i,t as o,e as _,F as w,o as c,a1 as j,n as B,f as Y,i as O}from"./app-DpegVBBD.js";import{d as X,h as M}from"./moment-CQ1ixRO1.js";import"./index-CNQNH7rt.js";import"./index-SIrGyeQJ.js";import"./index-BmkqFG3L.js";import"./index-HiKbvKD5.js";import"./index-8e2s9cPg.js";import"./index-CfWSzMo7.js";import"./index-kIrwgUrv.js";import"./index-Dwoluwl6.js";import"./index-BrMVFeMR.js";import"./index-DDjKLpnN.js";import"./index-h5R4gv03.js";import"./index-DdRnJjEh.js";const Z={key:0,class:"flex flex-col md:flex-row gap-12 min-h-screen items-center justify-center"},ee={class:"w-full"},te={class:"flex flex-col gap-4 text-center"},se={key:1},ae={class:"grid grid-cols-12 gap-4"},le={class:"card col-span-12 lg:col-span-6 xl:col-span-6"},oe={class:"font-semibold text-xl"},ie={class:"flex flex-col"},re={class:"flex flex-col md:flex-row justify-between md:items-center flex-1 gap-6"},ne={class:"flex flex-row md:flex-col justify-between items-start gap-2"},de={class:"font-medium text-surface-500 dark:text-surface-400 text-sm"},ce={class:"text-lg font-medium mt-2"},ue={class:"font-medium text-surface-500 dark:text-surface-400 text-sm"},me={class:"flex flex-col md:items-end gap-8"},fe={class:"flex flex-row-reverse md:flex-row gap-2"},pe={class:"card col-span-12 lg:col-span-6 xl:col-span-6"},ve={class:"font-semibold text-xl"},xe={class:"flex flex-col"},_e={class:"flex flex-col md:flex-row justify-between md:items-center flex-1 gap-6"},ge={class:"flex flex-row md:flex-col justify-between items-start gap-2"},he={class:"font-medium text-surface-500 dark:text-surface-400 text-sm"},ye={class:"text-lg font-medium mt-2"},be={class:"font-medium text-surface-500 dark:text-surface-400 text-sm"},Ae={__name:"IndexPdv",setup(we){const k=O(),u=W();s(null);const g=s(!0),r=s(!1);let h=s(null);const C=s("");s(null);const E=s(1);s(15);const F=s(0),D=s(!1),m=s(!1);let S;const f=s([]),I=s([]),R=s([]),p=s([]);s(null),s(null),s(null);function U(){k&&k.back()}const y=()=>{m.value=!1},q=a=>{m.value=!0,h.value=a},b=async(a=1)=>{axios.get(`/api/pdvbar?page=${a}`,{params:{query:C.value}}).then(t=>{f.value=t.data.order_itens_pending,p.value=t.data.order_itens_delivered,F.value=t.data.total,g.value=!1}).catch(t=>{g.value=!1,u.add({severity:"error",summary:`${t}`,detail:"Message Detail",life:3e3}),U()})},z=()=>{r.value=!0,axios.delete(`/api/tables/${h.value}`).then(()=>{f.value=response.data.order_itens_pending,p.value=response.data.order_itens_delivered,y(),u.add({severity:"success",summary:"Sucesso",detail:"Sucesso ao apagar",life:3e3})}).catch(a=>{u.add({severity:"error",summary:"Erro",detail:`${a}`,life:3e3}),r.value=!1}).finally(()=>{r.value=!1})},N=()=>{r.value=!0,axios.get(`/api/barchangestatus/${h.value}`).then(a=>{f.value=a.data.order_itens_pending,I.value=a.data.order_itens_getting_ready,R.value=a.data.order_itens_ready,p.value=a.data.order_itens_delivered,r.value=!1,y(),u.add({severity:"success",summary:"Sucesso",detail:"Sucesso ao transitar o estado.",life:3e3})}).catch(a=>{u.add({severity:"error",summary:"Erro",detail:`${a}`,life:3e3}),r.value=!1}).finally(()=>{r.value=!1})},V=X(()=>{b(E.value)},300);return G(C,V),J(()=>{b(),S=setInterval(()=>{b()},3e4)}),K(()=>{clearInterval(S)}),(a,t)=>{const A=Q,v=T,$=L,P=H;return c(),d(w,null,[g.value?(c(),d("div",Z,[e("div",ee,[e("div",te,[i(A,{style:{width:"50px",height:"50px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"}),t[3]||(t[3]=e("p",null,"Por Favor Aguarde...",-1))])])])):(c(),d("div",se,[e("div",ae,[e("div",le,[e("div",oe,"Pendentes("+o(f.value.length)+")",1),i($,{value:f.value,paginator:"",rows:50,layout:"list"},{list:_(n=>[e("div",ie,[(c(!0),d(w,null,j(n.items,(l,x)=>(c(),d("div",{key:x},[e("div",{class:B(["flex flex-col sm:flex-row sm:items-center p-6 gap-4",{"border-t border-surface":x!==0}])},[e("div",re,[e("div",ne,[e("div",null,[e("span",de,o(l.order.table?l.order.table.name:"Pedido Rápido")+" | #"+o(l.order.id),1),e("div",ce,o(l.quantity)+" * "+o(l.product.name),1),e("span",ue,o(Y(M)(l.created_at).format("DD-MM-YYYY H:mm")),1)])]),e("div",me,[e("div",fe,[i(v,{onClick:ke=>q(l.id),icon:"pi pi-check",class:"flex-auto md:flex-initial whitespace-nowrap"},null,8,["onClick"])])])])],2)]))),128))])]),_:1},8,["value"])]),e("div",pe,[e("div",ve,"Entregue("+o(p.value.length)+")",1),i($,{value:p.value,paginator:"",rows:50,layout:"list"},{list:_(n=>[e("div",xe,[(c(!0),d(w,null,j(n.items,(l,x)=>(c(),d("div",{key:x},[e("div",{class:B(["flex flex-col sm:flex-row sm:items-center p-6 gap-4",{"border-t border-surface":x!==0}])},[e("div",_e,[e("div",ge,[e("div",null,[e("span",he,o(l.order.table?l.order.table.name:"Pedido Rápido")+" | #"+o(l.order.id),1),e("div",ye,o(l.product.name),1),e("span",be,o(Y(M)(l.created_at).format("DD-MM-YYYY H:mm")),1)])]),t[4]||(t[4]=e("div",{class:"flex flex-col md:items-end gap-8"},null,-1))])],2)]))),128))])]),_:1},8,["value"])])])])),i(P,{header:"Confirmação",visible:D.value,"onUpdate:visible":t[0]||(t[0]=n=>D.value=n),style:{width:"350px"},modal:!0},{footer:_(()=>[i(v,{label:"Não",icon:"pi pi-times",onClick:y,class:"p-button-text"}),i(v,{label:"Sim",icon:"pi pi-check",onClick:z,class:"p-button-text",autofocus:""})]),default:_(()=>[t[5]||(t[5]=e("div",{class:"flex align-items-center justify-content-center"},[e("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}}),e("span",null,"Tem certeza que deseja proceder?")],-1))]),_:1},8,["visible"]),i(P,{header:"Deseja avançar o pedido para o próximo estado?",visible:m.value,"onUpdate:visible":t[2]||(t[2]=n=>m.value=n),style:{width:"30vw"}},{default:_(()=>[t[6]||(t[6]=e("p",null,"Ao clicar em avançar, seu pedido será adicionado em outra tabela de referência.",-1)),i(v,{class:"m-4",label:"Fechar",severity:"danger",onClick:t[1]||(t[1]=n=>m.value=!1)}),i(v,{class:"m-4",label:"Proximo Estado",disabled:r.value==!0,onClick:N},null,8,["disabled"])]),_:1},8,["visible"])],64)}}};export{Ae as default};
