import{s as A}from"./index-CDgfXS-q.js";import{s as G}from"./index-9mXFJ4bk.js";import{s as H}from"./index-DR2RjSeB.js";import{s as O}from"./index-eDXft2s-.js";import{s as K,a as Q}from"./index-DPadZcTW.js";import{s as Z}from"./index-CPnmDTcL.js";import{s as J}from"./index-BJuVzp9L.js";import{s as X}from"./index-CbJ_gzJK.js";import{u as ee,r,w as te,b as ae,c as g,d as l,a,e as s,F as oe,o as b,g as i,t as m,f as S,R as se,l as re,i as le}from"./app-CFr2Dm5b.js";import{d as ie,h as ne}from"./moment-CQ1ixRO1.js";import"./index-be3Wln0j.js";import"./index-x9M35Fxq.js";import"./index-CIoWWLqq.js";import"./index-CU8b40ee.js";import"./index-CzE2hQnB.js";import"./index-DNVfLTg7.js";import"./index-cgKZQo1-.js";import"./index--Ela_p_F.js";import"./index-CYibgX87.js";import"./index-D0gYlNQc.js";import"./index-B8QmToLB.js";import"./index-Bu6OmzmS.js";import"./index-BldKCWC7.js";import"./index-B4f6Zh7-.js";import"./index-QqnwJr0o.js";import"./index-CqHciBWw.js";const de={key:0,class:"flex flex-col md:flex-row gap-12 min-h-screen items-center justify-center"},ue={class:"w-full"},ce={class:"flex flex-col gap-4 text-center"},me={key:1,class:"flex flex-col md:flex-row gap-12"},pe={class:"w-full"},fe={class:"card flex flex-col gap-4"},ve={class:"flex justify-between"},ye=["src"],We={__name:"IndexOrders",setup(ge){const k=le(),d=ee();r(null);const u=r(!0),_=r(!1);let C=r(null);const p=r(""),f=r(null),v=r(1),h=r(15),R=r(0),x=r(!1),B=r(!1),y=r(!1),w=r(null);function V(){y.value=!1}function I(){k&&k.back()}const $=()=>{x.value=!1};function T(o){switch(o){case 1:return"danger";case 2:return"warn";case 3:return"success";case 4:return"danger";case 5:return"info";case 6:return"info"}}const D=async(o=1)=>{axios.get(`/api/orders?page=${o}`,{params:{query:p.value}}).then(e=>{f.value=e.data,R.value=e.data.total,u.value=!1}).catch(e=>{u.value=!1,d.add({severity:"error",summary:`${e}`,detail:"Message Detail",life:3e3}),I()})};function M(){const o=document.querySelector("iframe");o&&(o.contentWindow.focus(),o.contentWindow.print())}function N(){axios.post("/api/order/report",{},{responseType:"blob"}).then(o=>{const e=new Blob([o.data],{type:"application/pdf"});w.value=URL.createObjectURL(e),y.value=!0,B.value=!1,d.add({severity:"success",summary:"Successo",detail:"Relatorio Gerado Com sucesso!",life:3e3})}).catch(o=>{u.value=!1,d.add({severity:"error",summary:`${o}`,detail:"Message Detail",life:3e3})})}const U=()=>{_.value=!0,axios.delete(`/api/orders/${C.value}`).then(()=>{f.value.data=f.value.data.filter(o=>o.id!==C.value),$(),d.add({severity:"success",summary:"Sucesso",detail:"Sucesso ao apagar",life:3e3})}).catch(o=>{d.add({severity:"error",summary:"Erro",detail:`${o}`,life:3e3}),_.value=!1}).finally(()=>{_.value=!1})},E=o=>{v.value=o.page+1,h.value=o.rows,D(v.value)},F=ie(()=>{D(v.value)},300);return te(p,F),ae(()=>{D()}),(o,e)=>{const j=X,c=J,q=K,L=Z,Y=Q,n=O,z=H,W=G,P=A;return b(),g(oe,null,[u.value?(b(),g("div",de,[l("div",ue,[l("div",ce,[a(j,{style:{width:"50px",height:"50px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"}),e[4]||(e[4]=l("p",null,"Por Favor Aguarde...",-1))])])])):(b(),g("div",me,[l("div",pe,[l("div",fe,[e[10]||(e[10]=l("div",{class:"font-semibold text-xl"},"Encomendas",-1)),a(W,{value:f.value.data,paginator:!0,rows:h.value,totalRecords:R.value,dataKey:"id",lazy:!0,rowHover:!0,loading:u.value,first:(v.value-1)*h.value,onPage:E,showGridlines:""},{header:s(()=>[l("div",ve,[a(c,{label:"Relatorio",class:"mr-2 mb-2",onClick:e[0]||(e[0]=t=>N())},{default:s(()=>e[5]||(e[5]=[i("Relatório"),l("i",{class:"pi pi-print"},null,-1)])),_:1}),a(Y,null,{default:s(()=>[a(q,null,{default:s(()=>e[6]||(e[6]=[l("i",{class:"pi pi-search"},null,-1)])),_:1}),a(L,{modelValue:p.value,"onUpdate:modelValue":e[1]||(e[1]=t=>p.value=t),placeholder:"Pesquisa"},null,8,["modelValue"])]),_:1})])]),empty:s(()=>e[7]||(e[7]=[i("Nenhuma registro encontrado. ")])),loading:s(()=>e[8]||(e[8]=[i(" Carregando, por favor espere. ")])),default:s(()=>[a(n,{header:"ID",style:{"min-width":"12rem"}},{body:s(({data:t})=>[i(m(t.id),1)]),_:1}),a(n,{header:"Valor",style:{"min-width":"12rem"}},{body:s(({data:t})=>[i(" MZN "+m(t.total),1)]),_:1}),a(n,{header:"Mesa",style:{"min-width":"12rem"}},{body:s(({data:t})=>[i(m(t.table?t.table.name:"Venda Rápida"),1)]),_:1}),a(n,{header:"Estado da Encomenda",style:{"min-width":"12rem"}},{body:s(({data:t})=>[a(z,{value:t.status.name,severity:T(t.order_status_id)},null,8,["value","severity"])]),_:1}),a(n,{header:"Efetuada por",style:{"min-width":"12rem"}},{body:s(({data:t})=>[i(m(t.user_id),1)]),_:1}),a(n,{header:"Data",dataType:"date",style:{"min-width":"10rem"}},{body:s(({data:t})=>[i(m(S(ne)(t.created_at).format("DD-MM-YYYY H:mm")),1)]),_:1}),a(n,{header:"Ações",style:{"min-width":"12rem"}},{body:s(({data:t})=>[a(S(se),{class:"m-3",to:"/admin/orders/"+t.id},{default:s(()=>e[9]||(e[9]=[l("i",{class:"pi pi-eye"},null,-1)])),_:2},1032,["to"])]),_:1})]),_:1},8,["value","rows","totalRecords","loading","first"])])])])),a(P,{header:"Confirmação",visible:x.value,"onUpdate:visible":e[2]||(e[2]=t=>x.value=t),style:{width:"350px"},modal:!0},{footer:s(()=>[a(c,{label:"Não",icon:"pi pi-times",onClick:$,class:"p-button-text"}),a(c,{label:"Sim",icon:"pi pi-check",onClick:U,class:"p-button-text",autofocus:""})]),default:s(()=>[e[11]||(e[11]=l("div",{class:"flex align-items-center justify-content-center"},[l("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}}),l("span",null,"Tem certeza que deseja proceder?")],-1))]),_:1},8,["visible"]),a(P,{visible:y.value,"onUpdate:visible":e[3]||(e[3]=t=>y.value=t),header:"Recibo",modal:!0,style:{width:"600px"},closable:!1},{footer:s(()=>[a(c,{label:"Imprimir",icon:"pi pi-print",onClick:M}),a(c,{label:"Fechar",icon:"pi pi-times",class:"p-button-text",onClick:V})]),default:s(()=>[w.value?(b(),g("iframe",{key:0,src:w.value,style:{width:"100%",height:"500px"},frameborder:"0"},null,8,ye)):re("",!0)]),_:1},8,["visible"])],64)}}};export{We as default};
