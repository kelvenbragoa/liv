import{s as Y}from"./index-GBIKbbqV.js";import{s as q}from"./index-BQYU3Hyk.js";import{s as z}from"./index-CGxYVyCm.js";import{s as E,a as U}from"./index-D8H-Bte4.js";import{s as A}from"./index-BVOFM5m3.js";import{s as H}from"./index-C_-hGcsj.js";import{s as L}from"./index-BxDvEDNp.js";import{u as G,r,w as K,o as Q,c as b,a as s,b as t,e as o,F as W,f as h,g as w,R as I,d as i,t as d,k as J}from"./app-BPw18tlI.js";import{d as O,h as X}from"./moment-CQ1ixRO1.js";import"./index-BADeJyrK.js";import"./index-C0li01ax.js";import"./index-CtVpGnOH.js";import"./index-rKSMm_yG.js";import"./index-C1Np7P6N.js";import"./index-CdC4w-tp.js";import"./index-Bb8Bgeko.js";import"./index-CpYhzkzM.js";import"./index-nvIrYAGW.js";import"./index-BszkrP4s.js";import"./index-BY6_n-Tl.js";import"./index-DZfUK6Wm.js";import"./index-BKVNYo35.js";import"./index-D8dfN3BX.js";import"./index-BVuaBPim.js";import"./index-D2WXHmd3.js";const Z={key:0,class:"flex flex-col md:flex-row gap-12 min-h-screen items-center justify-center"},ee={class:"w-full"},te={class:"flex flex-col gap-4 text-center"},oe={key:1,class:"flex flex-col md:flex-row gap-12"},ae={class:"w-full"},se={class:"card flex flex-col gap-4"},re={class:"flex justify-between"},Ve={__name:"IndexInventories",setup(le){const k=J(),f=G();r(null);const u=r(!0),v=r(!1);let D=r(null);const m=r(""),c=r(null),p=r(1),g=r(15),C=r(0),y=r(!1);function P(){k&&k.back()}const $=()=>{y.value=!1},_=async(l=1)=>{axios.get(`/api/inventories?page=${l}`,{params:{query:m.value}}).then(e=>{c.value=e.data,C.value=e.data.total,u.value=!1}).catch(e=>{u.value=!1,f.add({severity:"error",summary:`${e}`,detail:"Message Detail",life:3e3}),P()})},S=()=>{v.value=!0,axios.delete(`/api/inventories/${D.value}`).then(()=>{c.value.data=c.value.data.filter(l=>l.id!==D.value),$(),f.add({severity:"success",summary:"Sucesso",detail:"Sucesso ao apagar",life:3e3})}).catch(l=>{f.add({severity:"error",summary:"Erro",detail:`${l}`,life:3e3}),v.value=!1}).finally(()=>{v.value=!1})},B=l=>{p.value=l.page+1,g.value=l.rows,_(p.value)},R=O(()=>{_(p.value)},300);return K(m,R),Q(()=>{_()}),(l,e)=>{const V=L,x=H,N=E,T=A,F=U,n=z,j=q,M=Y;return h(),b(W,null,[u.value?(h(),b("div",Z,[s("div",ee,[s("div",te,[t(V,{style:{width:"50px",height:"50px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"}),e[2]||(e[2]=s("p",null,"Por Favor Aguarde...",-1))])])])):(h(),b("div",oe,[s("div",ae,[s("div",se,[e[8]||(e[8]=s("div",{class:"font-semibold text-xl"},"Inventário de Stock",-1)),t(j,{value:c.value.data,paginator:!0,rows:g.value,totalRecords:C.value,dataKey:"id",lazy:!0,rowHover:!0,loading:u.value,first:(p.value-1)*g.value,onPage:B,showGridlines:""},{header:o(()=>[s("div",re,[t(w(I),{to:"/stock/inventories/create"},{default:o(()=>[t(x,{label:"Voltar",class:"mr-2 mb-2"},{default:o(()=>e[3]||(e[3]=[i("Novo Registro"),s("i",{class:"pi pi-plus"},null,-1)])),_:1})]),_:1}),t(F,null,{default:o(()=>[t(N,null,{default:o(()=>e[4]||(e[4]=[s("i",{class:"pi pi-search"},null,-1)])),_:1}),t(T,{modelValue:m.value,"onUpdate:modelValue":e[0]||(e[0]=a=>m.value=a),placeholder:"Pesquisa"},null,8,["modelValue"])]),_:1})])]),empty:o(()=>e[5]||(e[5]=[i("Nenhuma registro encontrado. ")])),loading:o(()=>e[6]||(e[6]=[i(" Carregando, por favor espere. ")])),default:o(()=>[t(n,{header:"ID",style:{"min-width":"12rem"}},{body:o(({data:a})=>[i(d(a.id),1)]),_:1}),t(n,{header:"REF",style:{"min-width":"12rem"}},{body:o(({data:a})=>[i(d(a.ref),1)]),_:1}),t(n,{header:"Centro de Stock",style:{"min-width":"12rem"}},{body:o(({data:a})=>[i(d(a.stockcenter.name),1)]),_:1}),t(n,{header:"Produtos Inventariados",style:{"min-width":"12rem"}},{body:o(({data:a})=>[i(d(a.products_number),1)]),_:1}),t(n,{header:"Data",dataType:"date",style:{"min-width":"10rem"}},{body:o(({data:a})=>[i(d(w(X)(a.created_at).format("DD-MM-YYYY H:mm")),1)]),_:1}),t(n,{header:"Ações",style:{"min-width":"12rem"}},{body:o(({data:a})=>[t(w(I),{class:"m-3",to:"/stock/inventories/"+a.id},{default:o(()=>e[7]||(e[7]=[s("i",{class:"pi pi-eye"},null,-1)])),_:2},1032,["to"])]),_:1})]),_:1},8,["value","rows","totalRecords","loading","first"])])])])),t(M,{header:"Confirmação",visible:y.value,"onUpdate:visible":e[1]||(e[1]=a=>y.value=a),style:{width:"350px"},modal:!0},{footer:o(()=>[t(x,{label:"Não",icon:"pi pi-times",onClick:$,class:"p-button-text"}),t(x,{label:"Sim",icon:"pi pi-check",onClick:S,class:"p-button-text",autofocus:""})]),default:o(()=>[e[9]||(e[9]=s("div",{class:"flex align-items-center justify-content-center"},[s("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}}),s("span",null,"Tem certeza que deseja proceder?")],-1))]),_:1},8,["visible"])],64)}}};export{Ve as default};
