import{s as q}from"./index-BiHTuues.js";import{s as z}from"./index-ondRFn1A.js";import{s as U}from"./index-CyTT0Vwr.js";import{s as A,a as E}from"./index-D9ivNX4o.js";import{s as H}from"./index-B5V1ONCG.js";import{s as L}from"./index-DZp2ByZM.js";import{s as G}from"./index-CKMdMxEn.js";import{u as K,r as i,w as Q,o as W,c as w,a as s,b as t,e as a,F as J,f as k,g as v,R as D,d as r,t as d,h as O,k as X}from"./app-BOyYmBzo.js";import{d as Z,h as ee}from"./moment-CQ1ixRO1.js";import"./index-Cc1UoU5f.js";import"./index-CkryDDHF.js";import"./index-BXRDs3kp.js";import"./index-BoNlvawM.js";import"./index-CiLCGkO0.js";import"./index-DiwBnWPK.js";import"./index-CRM2fMZb.js";import"./index-CML5C4T5.js";import"./index-1HcuzsbS.js";import"./index-CgxObtEZ.js";import"./index-D4iFNsOV.js";import"./index-CJq0LAY_.js";import"./index-w_o5PfkI.js";import"./index-7eUFddD6.js";import"./index-CTi1xqL8.js";import"./index-Ctrez2Wv.js";const te={key:0,class:"flex flex-col md:flex-row gap-12 min-h-screen items-center justify-center"},ae={class:"w-full"},oe={class:"flex flex-col gap-4 text-center"},se={key:1,class:"flex flex-col md:flex-row gap-12"},le={class:"w-full"},ie={class:"card flex flex-col gap-4"},re={class:"flex justify-between"},ne=["onClick"],je={__name:"IndexSubCategories",setup(de){const C=X(),g=K();i(null);const u=i(!0),y=i(!1);let b=i(null);const m=i(""),p=i(null),c=i(1),x=i(15),$=i(0),f=i(!1);function S(){C&&C.back()}const P=()=>{f.value=!1},B=l=>{f.value=!0,b.value=l},_=async(l=1)=>{axios.get(`/api/subcategories?page=${l}`,{params:{query:m.value}}).then(e=>{p.value=e.data,$.value=e.data.total,u.value=!1}).catch(e=>{u.value=!1,g.add({severity:"error",summary:`${e}`,detail:"Message Detail",life:3e3}),S()})},I=()=>{y.value=!0,axios.delete(`/api/subcategories/${b.value}`).then(()=>{p.value.data=p.value.data.filter(l=>l.id!==b.value),P(),g.add({severity:"success",summary:"Sucesso",detail:"Sucesso ao apagar",life:3e3})}).catch(l=>{g.add({severity:"error",summary:"Erro",detail:`${l}`,life:3e3}),y.value=!1}).finally(()=>{y.value=!1})},N=l=>{c.value=l.page+1,x.value=l.rows,_(c.value)},R=Z(()=>{_(c.value)},300);return Q(m,R),W(()=>{_()}),(l,e)=>{const V=G,h=L,T=A,M=H,j=E,n=U,F=z,Y=q;return k(),w(J,null,[u.value?(k(),w("div",te,[s("div",ae,[s("div",oe,[t(V,{style:{width:"50px",height:"50px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"}),e[2]||(e[2]=s("p",null,"Por Favor Aguarde...",-1))])])])):(k(),w("div",se,[s("div",le,[s("div",ie,[e[11]||(e[11]=s("div",{class:"font-semibold text-xl"},"SubCategorias",-1)),t(F,{value:p.value.data,paginator:!0,rows:x.value,totalRecords:$.value,dataKey:"id",lazy:!0,rowHover:!0,loading:u.value,first:(c.value-1)*x.value,onPage:N,showGridlines:""},{header:a(()=>[s("div",re,[t(v(D),{to:"/stock/subcategories/create"},{default:a(()=>[t(h,{label:"Voltar",class:"mr-2 mb-2"},{default:a(()=>e[3]||(e[3]=[r("Novo Registro"),s("i",{class:"pi pi-plus"},null,-1)])),_:1})]),_:1}),t(j,null,{default:a(()=>[t(T,null,{default:a(()=>e[4]||(e[4]=[s("i",{class:"pi pi-search"},null,-1)])),_:1}),t(M,{modelValue:m.value,"onUpdate:modelValue":e[0]||(e[0]=o=>m.value=o),placeholder:"Pesquisa"},null,8,["modelValue"])]),_:1})])]),empty:a(()=>e[5]||(e[5]=[r("Nenhuma registro encontrado. ")])),loading:a(()=>e[6]||(e[6]=[r(" Carregando, por favor espere. ")])),default:a(()=>[t(n,{header:"ID",style:{"min-width":"12rem"}},{body:a(({data:o})=>[r(d(o.id),1)]),_:1}),t(n,{header:"Nome",style:{"min-width":"12rem"}},{body:a(({data:o})=>[r(d(o.name),1)]),_:1}),t(n,{header:"Categoria",style:{"min-width":"12rem"}},{body:a(({data:o})=>[r(d(o.category.name),1)]),_:1}),t(n,{header:"Departamento",style:{"min-width":"12rem"}},{body:a(({data:o})=>[r(d(o.category.department.name),1)]),_:1}),t(n,{header:"Data",dataType:"date",style:{"min-width":"10rem"}},{body:a(({data:o})=>[r(d(v(ee)(o.created_at).format("DD-MM-YYYY H:mm")),1)]),_:1}),t(n,{header:"Ações",style:{"min-width":"12rem"}},{body:a(({data:o})=>[t(v(D),{class:"m-3",to:"/admin/subcategories/"+o.id+"/edit"},{default:a(()=>e[7]||(e[7]=[s("i",{class:"pi pi-file-edit"},null,-1)])),_:2},1032,["to"]),e[10]||(e[10]=r()),t(v(D),{class:"m-3",to:"/admin/subcategories/"+o.id},{default:a(()=>e[8]||(e[8]=[s("i",{class:"pi pi-eye"},null,-1)])),_:2},1032,["to"]),s("a",{class:"m-3",href:"#",onClick:O(ue=>B(o.id),["prevent"])},e[9]||(e[9]=[s("i",{class:"pi pi-trash"},null,-1)]),8,ne)]),_:1})]),_:1},8,["value","rows","totalRecords","loading","first"])])])])),t(Y,{header:"Confirmação",visible:f.value,"onUpdate:visible":e[1]||(e[1]=o=>f.value=o),style:{width:"350px"},modal:!0},{footer:a(()=>[t(h,{label:"Não",icon:"pi pi-times",onClick:P,class:"p-button-text"}),t(h,{label:"Sim",icon:"pi pi-check",onClick:I,class:"p-button-text",autofocus:""})]),default:a(()=>[e[12]||(e[12]=s("div",{class:"flex align-items-center justify-content-center"},[s("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}}),s("span",null,"Tem certeza que deseja proceder?")],-1))]),_:1},8,["visible"])],64)}}};export{je as default};
