import{s as F}from"./index-DgFzkDdR.js";import{s as Y}from"./index-BORxrjYa.js";import{s as q}from"./index-DphtFAnQ.js";import{s as z}from"./index-DvdxbrMZ.js";import{s as E,a as U}from"./index-D0L_gmj7.js";import{s as A}from"./index-DRQrLLY9.js";import{s as H}from"./index-C4Pb4f0M.js";import{u as L,r as l,w as G,o as K,c as x,a as s,b as a,d as o,F as Q,e as h,f as i,t as d,g as $,R as W,k as Z}from"./app-DjjNWAyZ.js";import{d as J,h as O}from"./moment-CQ1ixRO1.js";import"./index-CsBO25G4.js";import"./index-fpmgf2cM.js";import"./index-BfKxmIB5.js";import"./index-B-oFzC0U.js";import"./index-DPXoA4HO.js";import"./index-B3ELJNnu.js";import"./index--CR5sDXX.js";import"./index-1RL4_Pk4.js";import"./index-BF6O7oxD.js";import"./index-Cr-WPBZi.js";import"./index-BQ3oJh86.js";import"./index-D0iDJHK6.js";import"./index-CI54q-Jr.js";import"./index-CDgXT7G7.js";import"./index-D3CeYx5F.js";import"./index-CylxbUs7.js";const X={key:0,class:"flex flex-col md:flex-row gap-12 min-h-screen items-center justify-center"},ee={class:"w-full"},te={class:"flex flex-col gap-4 text-center"},ae={key:1,class:"flex flex-col md:flex-row gap-12"},oe={class:"w-full"},se={class:"card flex flex-col gap-4"},le={class:"flex justify-between"},Me={__name:"IndexPayments",setup(re){const w=Z(),f=L();l(null);const m=l(!0),v=l(!1);let b=l(null);const u=l(""),c=l(null),p=l(1),y=l(15),D=l(0),g=l(!1);function C(){w&&w.back()}const k=()=>{g.value=!1},_=async(r=1)=>{axios.get(`/api/payments?page=${r}`,{params:{query:u.value}}).then(e=>{c.value=e.data,D.value=e.data.total,m.value=!1}).catch(e=>{m.value=!1,f.add({severity:"error",summary:`${e}`,detail:"Message Detail",life:3e3}),C()})},B=()=>{v.value=!0,axios.delete(`/api/payments/${b.value}`).then(()=>{c.value.data=c.value.data.filter(r=>r.id!==b.value),k(),f.add({severity:"success",summary:"Sucesso",detail:"Sucesso ao apagar",life:3e3})}).catch(r=>{f.add({severity:"error",summary:"Erro",detail:`${r}`,life:3e3}),v.value=!1}).finally(()=>{v.value=!1})},I=r=>{p.value=r.page+1,y.value=r.rows,_(p.value)},S=J(()=>{_(p.value)},300);return G(u,S),K(()=>{_()}),(r,e)=>{const V=H,M=E,N=A,R=U,n=z,T=q,P=Y,j=F;return h(),x(Q,null,[m.value?(h(),x("div",X,[s("div",ee,[s("div",te,[a(V,{style:{width:"50px",height:"50px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"}),e[2]||(e[2]=s("p",null,"Por Favor Aguarde...",-1))])])])):(h(),x("div",ae,[s("div",oe,[s("div",se,[e[7]||(e[7]=s("div",{class:"font-semibold text-xl"},"Pagamentos",-1)),a(T,{value:c.value.data,paginator:!0,rows:y.value,totalRecords:D.value,dataKey:"id",lazy:!0,rowHover:!0,loading:m.value,first:(p.value-1)*y.value,onPage:I,showGridlines:""},{header:o(()=>[s("div",le,[a(R,null,{default:o(()=>[a(M,null,{default:o(()=>e[3]||(e[3]=[s("i",{class:"pi pi-search"},null,-1)])),_:1}),a(N,{modelValue:u.value,"onUpdate:modelValue":e[0]||(e[0]=t=>u.value=t),placeholder:"Pesquisa"},null,8,["modelValue"])]),_:1})])]),empty:o(()=>e[4]||(e[4]=[i("Nenhuma registro encontrado. ")])),loading:o(()=>e[5]||(e[5]=[i(" Carregando, por favor espere. ")])),default:o(()=>[a(n,{header:"ID",style:{"min-width":"12rem"}},{body:o(({data:t})=>[i(" #"+d(t.id),1)]),_:1}),a(n,{header:"Valor",style:{"min-width":"12rem"}},{body:o(({data:t})=>[i(d(t.amount)+" MZN ",1)]),_:1}),a(n,{header:"Metódo de Pagamento",style:{"min-width":"12rem"}},{body:o(({data:t})=>[i(d(t.method.name),1)]),_:1}),a(n,{header:"ID Encomenda",style:{"min-width":"12rem"}},{body:o(({data:t})=>[i(" #"+d(t.order_id),1)]),_:1}),a(n,{header:"Data",dataType:"date",style:{"min-width":"10rem"}},{body:o(({data:t})=>[i(d($(O)(t.created_at).format("DD-MM-YYYY H:mm")),1)]),_:1}),a(n,{header:"Ações",style:{"min-width":"12rem"}},{body:o(({data:t})=>[a($(W),{class:"m-3",to:"/admin/payments/"+t.id},{default:o(()=>e[6]||(e[6]=[s("i",{class:"pi pi-eye"},null,-1)])),_:2},1032,["to"])]),_:1})]),_:1},8,["value","rows","totalRecords","loading","first"])])])])),a(j,{header:"Confirmação",visible:g.value,"onUpdate:visible":e[1]||(e[1]=t=>g.value=t),style:{width:"350px"},modal:!0},{footer:o(()=>[a(P,{label:"Não",icon:"pi pi-times",onClick:k,class:"p-button-text"}),a(P,{label:"Sim",icon:"pi pi-check",onClick:B,class:"p-button-text",autofocus:""})]),default:o(()=>[e[8]||(e[8]=s("div",{class:"flex align-items-center justify-content-center"},[s("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}}),s("span",null,"Tem certeza que deseja proceder?")],-1))]),_:1},8,["visible"])],64)}}};export{Me as default};
