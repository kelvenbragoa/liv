import{s as X}from"./index-BT5gVeel.js";import{s as Y}from"./index-CbvAuPmc.js";import{s as Z}from"./index-CNws5RX1.js";import{s as ee}from"./index-CO3D0mg-.js";import{s as te}from"./index-CdjsaD8D.js";import{s as oe}from"./index-fxF4LGIo.js";import{s as ae}from"./index-DMGRHROc.js";import{u as se,r as a,w as re,d as le,c as V,b as o,a as s,e as r,f as n,j as U,n as j,t as c,k as ie,l as ne,F as de,i as ce,o as b,g as d}from"./app-B9cZWdjo.js";import{u as ue}from"./vee-validate-DQ5lNg-R.js";import{d as me}from"./moment-CQ1ixRO1.js";import{c as pe,a as I}from"./index.esm-CQT6nZnd.js";import"./index-CR_y7IXr.js";import"./index-Dkx1qGmb.js";import"./index-DHkFrFnV.js";import"./index-SEIbjF04.js";import"./index--UORYyyv.js";import"./index-B9mqRw-6.js";import"./index-DfzsLlCx.js";import"./index-B5V9gpnN.js";import"./index-CjvAw6xr.js";import"./index-CkXTqi73.js";import"./index-BjElDEMC.js";import"./index-BYe3dd5Z.js";import"./index-DMxd_77c.js";import"./index-CckUs2qJ.js";import"./index-C-s22LPj.js";import"./index-D2x_oI8N.js";const fe={key:0,class:"flex flex-col md:flex-row gap-12 min-h-screen items-center justify-center"},ve={class:"w-full"},ye={class:"flex flex-col gap-4 text-center"},ge={key:1,class:"flex flex-col md:flex-row gap-12"},be={class:"w-full"},_e={class:"card flex flex-col gap-4"},xe={class:"w-full"},ke={class:"flex flex-col gap-2"},he={id:"reference-help",class:"p-error"},Ce={class:"flex flex-col gap-2"},we={id:"stock_center_id-help",class:"p-error"},et={__name:"CreateInventories",setup(Se){const _=ce(),u=se();a(null);const f=a(!0),x=a(!1),k=a(!1);let D=a(null);const $=a(""),B=a(null),z=a(1);a(15),a(0);const h=a(!1);a(null);const p=a(!1),N=a([]),C=a([]),L=pe({reference:I().required().trim().label("Codigo"),stock_center_id:I().required().trim().label("CentrodeStock")}),{defineField:q,handleSubmit:M,resetForm:Ve,errors:v,setErrors:R}=ue({validationSchema:L}),[w]=q("reference"),[y]=q("stock_center_id"),F=a();function T(){_&&_.back()}const E=()=>{h.value=!1},S=M(i=>{const e=C.value.map(l=>({id:l.id,product_id:l.product.id,stock_center_id:l.stock_center_id,quantity:l.transferQuantity}));i.stockcenterproducts=e,F.value!=null&&(i.image=F.value),p.value=!0,axios.post("/api/inventories",i,{headers:{"Content-Type":"multipart/form-data"}}).then(l=>{_.back(),u.add({severity:"success",summary:"Successo",detail:"Categoria criado com sucesso",life:3e3})}).catch(l=>{p.value=!1,u.add({severity:"error",summary:"Erro",detail:`${l.response.data.message}`,life:3e3}),l.response.data.errors&&R(l.response.data.errors)}).finally(()=>{p.value=!1})}),P=async(i=1)=>{axios.get("/api/inventories/create",{params:{query:$.value}}).then(e=>{N.value=e.data.stockcenters,f.value=!1}).catch(e=>{f.value=!1,u.add({severity:"error",summary:`${e}`,detail:"Message Detail",life:3e3}),T()})},W=()=>{x.value=!0,axios.delete(`/api/inventories/${D.value}`).then(()=>{B.value.data=B.value.data.filter(i=>i.id!==D.value),E(),u.add({severity:"success",summary:"Sucesso",detail:"Sucesso ao apagar",life:3e3})}).catch(i=>{u.add({severity:"error",summary:"Erro",detail:`${i}`,life:3e3}),x.value=!1}).finally(()=>{x.value=!1})},A=i=>{k.value=!0,axios.get(`/api/auxiliar-product/${i}`).then(e=>{C.value=e.data.stockcenterproducts,k.value=!1}).catch(e=>{u.add({severity:"error",summary:`${e}`,detail:"Message Detail",life:3e3}),k.value=!1})},G=me(()=>{P(z.value)},300);return re($,G),le(()=>{P()}),(i,e)=>{const l=ae,g=oe,Q=te,H=ee,m=Z,K=Y,O=X;return b(),V(de,null,[f.value?(b(),V("div",fe,[o("div",ve,[o("div",ye,[s(l,{style:{width:"50px",height:"50px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"}),e[5]||(e[5]=o("p",null,"Por Favor Aguarde...",-1))])])])):(b(),V("div",ge,[o("div",be,[o("div",_e,[o("div",xe,[s(g,{label:"Voltar",class:"mr-2 mb-2",onClick:T},{default:r(()=>e[6]||(e[6]=[o("i",{class:"pi pi-angle-left"},null,-1),d(" Voltar")])),_:1})]),e[13]||(e[13]=o("div",{class:"font-semibold text-xl"},"Centro de Stock",-1)),e[14]||(e[14]=o("small",{class:"p-error"},"Os campos marcados * sao considerados campos obrigatorios.",-1)),o("form",{onSubmit:e[3]||(e[3]=(...t)=>n(S)&&n(S)(...t))},[o("div",ke,[e[7]||(e[7]=o("label",{for:"reference"},"REF",-1)),s(Q,{modelValue:n(w),"onUpdate:modelValue":e[0]||(e[0]=t=>U(w)?w.value=t:null),id:"reference",placeholder:"Nome",class:j({"p-invalid":n(v).reference}),type:"text"},null,8,["modelValue","class"]),o("small",he,c(n(v).reference),1)]),o("div",Ce,[e[8]||(e[8]=o("label",{for:"stock_center_id"},"Centro de Sock ",-1)),s(H,{modelValue:n(y),"onUpdate:modelValue":e[1]||(e[1]=t=>U(y)?y.value=t:null),options:N.value,optionLabel:"name",optionValue:"id",placeholder:"Selecionar",class:j({"p-invalid":n(v).stock_center_id}),onChange:e[2]||(e[2]=t=>A(n(y)))},null,8,["modelValue","options","class"]),o("small",we,c(n(v).stock_center_id),1)]),e[12]||(e[12]=o("hr",null,null,-1)),s(K,{value:C.value,dataKey:"id",rowHover:!0,loading:f.value,showGridlines:""},{header:r(()=>e[9]||(e[9]=[])),empty:r(()=>e[10]||(e[10]=[d("Nenhuma registro encontrado. ")])),loading:r(()=>e[11]||(e[11]=[d(" Carregando, por favor espere. ")])),default:r(()=>[s(m,{header:"ID",style:{"min-width":"12rem"}},{body:r(({data:t})=>[d(c(t.id),1)]),_:1}),s(m,{header:"Nome",style:{"min-width":"12rem"}},{body:r(({data:t})=>[d(c(t.product.name),1)]),_:1}),s(m,{header:"Categoria",style:{"min-width":"12rem"}},{body:r(({data:t})=>[d(c(t.product.category.name),1)]),_:1}),s(m,{header:"SubCategoria",style:{"min-width":"12rem"}},{body:r(({data:t})=>[d(c(t.product.subcategory.name),1)]),_:1}),s(m,{header:"Quantidade Esperado",style:{"min-width":"12rem"}},{body:r(({data:t})=>[d(c(t.quantity),1)]),_:1}),s(m,{header:"Contado",dataType:"number",style:{"min-width":"10rem"}},{body:r(({data:t})=>[s(Q,{modelValue:t.transferQuantity,"onUpdate:modelValue":J=>t.transferQuantity=J,placeholder:"Quantidade",min:0,max:t.quantity,type:"number",style:{width:"100%"}},null,8,["modelValue","onUpdate:modelValue","max"])]),_:1})]),_:1},8,["value","loading"]),s(g,{label:"Submeter",class:"mr-2 mb-2 mt-2",onClick:n(S),disabled:p.value},null,8,["onClick","disabled"]),p.value?(b(),ie(l,{key:0,style:{width:"35px",height:"35px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"})):ne("",!0)],32)])])])),s(O,{header:"Confirmação",visible:h.value,"onUpdate:visible":e[4]||(e[4]=t=>h.value=t),style:{width:"350px"},modal:!0},{footer:r(()=>[s(g,{label:"Não",icon:"pi pi-times",onClick:E,class:"p-button-text"}),s(g,{label:"Sim",icon:"pi pi-check",onClick:W,class:"p-button-text",autofocus:""})]),default:r(()=>[e[15]||(e[15]=o("div",{class:"flex align-items-center justify-content-center"},[o("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}}),o("span",null,"Tem certeza que deseja proceder?")],-1))]),_:1},8,["visible"])],64)}}};export{et as default};
