import{s as Z}from"./index-BT5gVeel.js";import{s as ee}from"./index-CbvAuPmc.js";import{s as te}from"./index-CNws5RX1.js";import{s as oe}from"./index-CO3D0mg-.js";import{s as ae}from"./index-CdjsaD8D.js";import{s as se}from"./index-fxF4LGIo.js";import{s as re}from"./index-DMGRHROc.js";import{u as le,r as s,w as ie,d as ne,c as F,b as o,a,e as l,f as r,j as k,n as b,t as d,k as de,l as ce,F as ue,i as me,o as x,g as u}from"./app-B9cZWdjo.js";import{u as pe}from"./vee-validate-DQ5lNg-R.js";import{d as fe}from"./moment-CQ1ixRO1.js";import{c as _e,a as h}from"./index.esm-CQT6nZnd.js";import"./index-CR_y7IXr.js";import"./index-Dkx1qGmb.js";import"./index-DHkFrFnV.js";import"./index-SEIbjF04.js";import"./index--UORYyyv.js";import"./index-B9mqRw-6.js";import"./index-DfzsLlCx.js";import"./index-B5V9gpnN.js";import"./index-CjvAw6xr.js";import"./index-CkXTqi73.js";import"./index-BjElDEMC.js";import"./index-BYe3dd5Z.js";import"./index-DMxd_77c.js";import"./index-CckUs2qJ.js";import"./index-C-s22LPj.js";import"./index-D2x_oI8N.js";const ve={key:0,class:"flex flex-col md:flex-row gap-12 min-h-screen items-center justify-center"},ge={class:"w-full"},ye={class:"flex flex-col gap-4 text-center"},ke={key:1,class:"flex flex-col md:flex-row gap-12"},be={class:"w-full"},xe={class:"card flex flex-col gap-4"},he={class:"w-full"},Ce={class:"flex flex-col gap-2"},Se={id:"reference-help",class:"p-error"},Ve={class:"flex flex-col gap-2"},we={id:"transfer_date-help",class:"p-error"},De={class:"flex flex-col gap-2"},$e={id:"stock_center_origin_id-help",class:"p-error"},Be={class:"flex flex-col gap-2"},qe={id:"stock_center_destination_id-help",class:"p-error"},lt={__name:"CreateStockTransfers",setup(Te){const C=me(),m=le();s(null);const _=s(!0),S=s(!1),V=s(!1);let P=s(null);const Q=s(""),E=s(null),R=s(1);s(15),s(0);const w=s(!1);s(null);const f=s(!1),D=s([]),$=s([]),O=_e({reference:h().required().trim().label("Codigo"),transfer_date:h().required().trim().label("transfer"),stock_center_origin_id:h().required().trim().label("CentrodeStock"),stock_center_destination_id:h().required().trim().label("CentrodeStock")}),{defineField:v,handleSubmit:W,resetForm:Ne,errors:c,setErrors:A}=pe({validationSchema:O}),[B]=v("reference"),[q]=v("transfer_date"),[g]=v("stock_center_origin_id"),[T]=v("stock_center_destination_id"),j=s();function L(){C&&C.back()}const z=()=>{w.value=!1},N=W(n=>{const e=$.value.map(i=>({id:i.id,product_id:i.product.id,stock_center_id:i.stock_center_id,quantity:i.transferQuantity}));n.stockcenterproducts=e,j.value!=null&&(n.image=j.value),f.value=!0,axios.post("/api/stocktransfers",n,{headers:{"Content-Type":"multipart/form-data"}}).then(i=>{C.back(),m.add({severity:"success",summary:"Successo",detail:"Categoria criado com sucesso",life:3e3})}).catch(i=>{f.value=!1,m.add({severity:"error",summary:"Erro",detail:`${i.response.data.message}`,life:3e3}),i.response.data.errors&&A(i.response.data.errors)}).finally(()=>{f.value=!1})}),I=async(n=1)=>{axios.get("/api/stocktransfers/create",{params:{query:Q.value}}).then(e=>{D.value=e.data.stockcenters,_.value=!1}).catch(e=>{_.value=!1,m.add({severity:"error",summary:`${e}`,detail:"Message Detail",life:3e3}),L()})},G=()=>{S.value=!0,axios.delete(`/api/stocktransfers/${P.value}`).then(()=>{E.value.data=E.value.data.filter(n=>n.id!==P.value),z(),m.add({severity:"success",summary:"Sucesso",detail:"Sucesso ao apagar",life:3e3})}).catch(n=>{m.add({severity:"error",summary:"Erro",detail:`${n}`,life:3e3}),S.value=!1}).finally(()=>{S.value=!1})},H=n=>{V.value=!0,axios.get(`/api/auxiliar-product/${n}`).then(e=>{$.value=e.data.stockcenterproducts,V.value=!1}).catch(e=>{m.add({severity:"error",summary:`${e}`,detail:"Message Detail",life:3e3}),V.value=!1})},K=fe(()=>{I(R.value)},300);return ie(Q,K),ne(()=>{I()}),(n,e)=>{const i=re,y=se,U=ae,M=oe,p=te,J=ee,X=Z;return x(),F(ue,null,[_.value?(x(),F("div",ve,[o("div",ge,[o("div",ye,[a(i,{style:{width:"50px",height:"50px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"}),e[7]||(e[7]=o("p",null,"Por Favor Aguarde...",-1))])])])):(x(),F("div",ke,[o("div",be,[o("div",xe,[o("div",he,[a(y,{label:"Voltar",class:"mr-2 mb-2",onClick:L},{default:l(()=>e[8]||(e[8]=[o("i",{class:"pi pi-angle-left"},null,-1),u(" Voltar")])),_:1})]),e[17]||(e[17]=o("div",{class:"font-semibold text-xl"},"Centro de Stock",-1)),e[18]||(e[18]=o("small",{class:"p-error"},"Os campos marcados * sao considerados campos obrigatorios.",-1)),o("form",{onSubmit:e[5]||(e[5]=(...t)=>r(N)&&r(N)(...t))},[o("div",Ce,[e[9]||(e[9]=o("label",{for:"reference"},"REF",-1)),a(U,{modelValue:r(B),"onUpdate:modelValue":e[0]||(e[0]=t=>k(B)?B.value=t:null),id:"reference",placeholder:"Nome",class:b({"p-invalid":r(c).reference}),type:"text"},null,8,["modelValue","class"]),o("small",Se,d(r(c).reference),1)]),o("div",Ve,[e[10]||(e[10]=o("label",{for:"transfer_date"},"Data",-1)),a(U,{modelValue:r(q),"onUpdate:modelValue":e[1]||(e[1]=t=>k(q)?q.value=t:null),id:"transfer_date",placeholder:"Codigo",class:b({"p-invalid":r(c).transfer_date}),type:"date"},null,8,["modelValue","class"]),o("small",we,d(r(c).transfer_date),1)]),o("div",De,[e[11]||(e[11]=o("label",{for:"stock_center_origin_id"},"Centro de Sock de Origem",-1)),a(M,{modelValue:r(g),"onUpdate:modelValue":e[2]||(e[2]=t=>k(g)?g.value=t:null),options:D.value,optionLabel:"name",optionValue:"id",placeholder:"Selecionar",class:b({"p-invalid":r(c).stock_center_origin_id}),onChange:e[3]||(e[3]=t=>H(r(g)))},null,8,["modelValue","options","class"]),o("small",$e,d(r(c).stock_center_origin_id),1)]),o("div",Be,[e[12]||(e[12]=o("label",{for:"stock_center_destination_id"},"Centro de Sock de Destino",-1)),a(M,{modelValue:r(T),"onUpdate:modelValue":e[4]||(e[4]=t=>k(T)?T.value=t:null),options:D.value,optionLabel:"name",optionValue:"id",placeholder:"Selecionar",class:b({"p-invalid":r(c).stock_center_destination_id})},null,8,["modelValue","options","class"]),o("small",qe,d(r(c).stock_center_destination_id),1)]),e[16]||(e[16]=o("hr",null,null,-1)),a(J,{value:$.value,dataKey:"id",rowHover:!0,loading:_.value,showGridlines:""},{header:l(()=>e[13]||(e[13]=[])),empty:l(()=>e[14]||(e[14]=[u("Nenhuma registro encontrado. ")])),loading:l(()=>e[15]||(e[15]=[u(" Carregando, por favor espere. ")])),default:l(()=>[a(p,{header:"ID",style:{"min-width":"12rem"}},{body:l(({data:t})=>[u(d(t.id),1)]),_:1}),a(p,{header:"Nome",style:{"min-width":"12rem"}},{body:l(({data:t})=>[u(d(t.product.name),1)]),_:1}),a(p,{header:"Categoria",style:{"min-width":"12rem"}},{body:l(({data:t})=>[u(d(t.product.category.name),1)]),_:1}),a(p,{header:"SubCategoria",style:{"min-width":"12rem"}},{body:l(({data:t})=>[u(d(t.product.subcategory.name),1)]),_:1}),a(p,{header:"Stock",style:{"min-width":"12rem"}},{body:l(({data:t})=>[u(d(t.quantity),1)]),_:1}),a(p,{header:"Quantidade a Transferir",dataType:"number",style:{"min-width":"10rem"}},{body:l(({data:t})=>[a(U,{modelValue:t.transferQuantity,"onUpdate:modelValue":Y=>t.transferQuantity=Y,placeholder:"Quantidade",min:0,max:t.quantity,type:"number",style:{width:"100%"}},null,8,["modelValue","onUpdate:modelValue","max"])]),_:1})]),_:1},8,["value","loading"]),a(y,{label:"Submeter",class:"mr-2 mb-2 mt-2",onClick:r(N),disabled:f.value},null,8,["onClick","disabled"]),f.value?(x(),de(i,{key:0,style:{width:"35px",height:"35px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"})):ce("",!0)],32)])])])),a(X,{header:"Confirmação",visible:w.value,"onUpdate:visible":e[6]||(e[6]=t=>w.value=t),style:{width:"350px"},modal:!0},{footer:l(()=>[a(y,{label:"Não",icon:"pi pi-times",onClick:z,class:"p-button-text"}),a(y,{label:"Sim",icon:"pi pi-check",onClick:G,class:"p-button-text",autofocus:""})]),default:l(()=>[e[19]||(e[19]=o("div",{class:"flex align-items-center justify-content-center"},[o("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}}),o("span",null,"Tem certeza que deseja proceder?")],-1))]),_:1},8,["visible"])],64)}}};export{lt as default};
