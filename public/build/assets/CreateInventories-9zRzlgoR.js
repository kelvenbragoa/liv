import{s as X}from"./index-DZzgqojr.js";import{s as Y}from"./index-BbUY9bRs.js";import{s as Z}from"./index-QCOzRf7T.js";import{s as ee}from"./index-DD-U4v7T.js";import{s as te}from"./index-Dm3dn45L.js";import{s as ae}from"./index-Bd6cy2Fc.js";import{s as oe}from"./index-CICQ2Kqq.js";import{u as se,r as o,w as re,o as le,c as V,a,b as s,d as l,g as n,l as U,n as I,t as u,j as ie,i as ne,F as de,k as ue,e as _,f as d}from"./app-D1nUGxLA.js";import{u as ce}from"./vee-validate-DljxmkwH.js";import{d as me}from"./moment-CQ1ixRO1.js";import{c as pe,a as j}from"./index.esm-CQT6nZnd.js";import"./index-CHGEY9zo.js";import"./index-DwlFL-Oo.js";import"./index-CFHSrHZ1.js";import"./index-CXePd_xE.js";import"./index-BHebE77i.js";import"./index-5JrAQTwL.js";import"./index-BhFTt8Zr.js";import"./index-BgzLGhx3.js";import"./index-JH0S6CzT.js";import"./index-iqk3e9fu.js";import"./index-B864ig6-.js";import"./index-DYTNX45P.js";import"./index-DAxFVNqt.js";import"./index-DPhrP3qA.js";import"./index-D_DqedVK.js";import"./index-Cbng0420.js";const fe={key:0,class:"flex flex-col md:flex-row gap-12 min-h-screen items-center justify-center"},ve={class:"w-full"},ye={class:"flex flex-col gap-4 text-center"},ge={key:1,class:"flex flex-col md:flex-row gap-12"},be={class:"w-full"},_e={class:"card flex flex-col gap-4"},xe={class:"w-full"},ke={class:"flex flex-col gap-2"},he={id:"reference-help",class:"p-error"},Ce={class:"flex flex-col gap-2"},we={id:"stock_center_id-help",class:"p-error"},et={__name:"CreateInventories",setup(Se){const x=ue(),c=se();o(null);const f=o(!0),k=o(!1),h=o(!1);let D=o(null);const $=o(""),B=o(null),z=o(1);o(15),o(0);const C=o(!1);o(null);const p=o(!1),q=o([]),v=o([]),L=pe({reference:j().required().trim().label("Codigo"),stock_center_id:j().required().trim().label("CentrodeStock")}),{defineField:N,handleSubmit:M,resetForm:Ve,errors:y,setErrors:R}=ce({validationSchema:L}),[w]=N("reference"),[g]=N("stock_center_id"),E=o();function F(){x&&x.back()}const Q=()=>{C.value=!1},S=M(i=>{const e=v.value.map(r=>({id:r.id,product_id:r.product.id,stock_center_id:r.stock_center_id,quantity:r.transferQuantity}));i.stockcenterproducts=e,E.value!=null&&(i.image=E.value),p.value=!0,axios.post("/api/inventories",i,{headers:{"Content-Type":"multipart/form-data"}}).then(r=>{x.back(),c.add({severity:"success",summary:"Successo",detail:"Categoria criado com sucesso",life:3e3})}).catch(r=>{p.value=!1,c.add({severity:"error",summary:"Erro",detail:`${r.response.data.message}`,life:3e3}),r.response.data.errors&&R(r.response.data.errors)}).finally(()=>{p.value=!1})}),T=async(i=1)=>{axios.get("/api/inventories/create",{params:{query:$.value}}).then(e=>{q.value=e.data.stockcenters,f.value=!1}).catch(e=>{f.value=!1,c.add({severity:"error",summary:`${e}`,detail:"Message Detail",life:3e3}),F()})},W=()=>{k.value=!0,axios.delete(`/api/inventories/${D.value}`).then(()=>{B.value.data=B.value.data.filter(i=>i.id!==D.value),Q(),c.add({severity:"success",summary:"Sucesso",detail:"Sucesso ao apagar",life:3e3})}).catch(i=>{c.add({severity:"error",summary:"Erro",detail:`${i}`,life:3e3}),k.value=!1}).finally(()=>{k.value=!1})},A=i=>{h.value=!0,axios.get(`/api/auxiliar-product/${i}`).then(e=>{v.value=e.data.stockcenterproducts,v.value.forEach(r=>{r.transferQuantity=r.quantity}),h.value=!1}).catch(e=>{c.add({severity:"error",summary:`${e}`,detail:"Message Detail",life:3e3}),h.value=!1})},G=me(()=>{T(z.value)},300);return re($,G),le(()=>{T()}),(i,e)=>{const r=oe,b=ae,P=te,H=ee,m=Z,K=Y,O=X;return _(),V(de,null,[f.value?(_(),V("div",fe,[a("div",ve,[a("div",ye,[s(r,{style:{width:"50px",height:"50px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"}),e[5]||(e[5]=a("p",null,"Por Favor Aguarde...",-1))])])])):(_(),V("div",ge,[a("div",be,[a("div",_e,[a("div",xe,[s(b,{label:"Voltar",class:"mr-2 mb-2",onClick:F},{default:l(()=>e[6]||(e[6]=[a("i",{class:"pi pi-angle-left"},null,-1),d(" Voltar")])),_:1})]),e[13]||(e[13]=a("div",{class:"font-semibold text-xl"},"Inventário",-1)),e[14]||(e[14]=a("small",{class:"p-error"},"Os campos marcados * sao considerados campos obrigatorios.",-1)),a("form",{onSubmit:e[3]||(e[3]=(...t)=>n(S)&&n(S)(...t))},[a("div",ke,[e[7]||(e[7]=a("label",{for:"reference"},"REF",-1)),s(P,{modelValue:n(w),"onUpdate:modelValue":e[0]||(e[0]=t=>U(w)?w.value=t:null),id:"reference",placeholder:"Nome",class:I({"p-invalid":n(y).reference}),type:"text"},null,8,["modelValue","class"]),a("small",he,u(n(y).reference),1)]),a("div",Ce,[e[8]||(e[8]=a("label",{for:"stock_center_id"},"Centro de Sock ",-1)),s(H,{modelValue:n(g),"onUpdate:modelValue":e[1]||(e[1]=t=>U(g)?g.value=t:null),options:q.value,optionLabel:"name",optionValue:"id",placeholder:"Selecionar",class:I({"p-invalid":n(y).stock_center_id}),onChange:e[2]||(e[2]=t=>A(n(g)))},null,8,["modelValue","options","class"]),a("small",we,u(n(y).stock_center_id),1)]),e[12]||(e[12]=a("hr",null,null,-1)),s(K,{value:v.value,dataKey:"id",rowHover:!0,loading:f.value,showGridlines:""},{header:l(()=>e[9]||(e[9]=[])),empty:l(()=>e[10]||(e[10]=[d("Nenhuma registro encontrado. ")])),loading:l(()=>e[11]||(e[11]=[d(" Carregando, por favor espere. ")])),default:l(()=>[s(m,{header:"ID",style:{"min-width":"12rem"}},{body:l(({data:t})=>[d(u(t.id),1)]),_:1}),s(m,{header:"Nome",style:{"min-width":"12rem"}},{body:l(({data:t})=>[d(u(t.product.name),1)]),_:1}),s(m,{header:"Categoria",style:{"min-width":"12rem"}},{body:l(({data:t})=>[d(u(t.product.category.name),1)]),_:1}),s(m,{header:"SubCategoria",style:{"min-width":"12rem"}},{body:l(({data:t})=>[d(u(t.product.subcategory.name),1)]),_:1}),s(m,{header:"Quantidade Esperado",style:{"min-width":"12rem"}},{body:l(({data:t})=>[d(u(t.quantity),1)]),_:1}),s(m,{header:"Contado",dataType:"number",style:{"min-width":"10rem"}},{body:l(({data:t})=>[s(P,{modelValue:t.transferQuantity,"onUpdate:modelValue":J=>t.transferQuantity=J,placeholder:"Quantidade",min:0,max:t.quantity,type:"number",style:{width:"100%"}},null,8,["modelValue","onUpdate:modelValue","max"])]),_:1})]),_:1},8,["value","loading"]),s(b,{label:"Submeter",class:"mr-2 mb-2 mt-2",onClick:n(S),disabled:p.value},null,8,["onClick","disabled"]),p.value?(_(),ie(r,{key:0,style:{width:"35px",height:"35px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"})):ne("",!0)],32)])])])),s(O,{header:"Confirmação",visible:C.value,"onUpdate:visible":e[4]||(e[4]=t=>C.value=t),style:{width:"350px"},modal:!0},{footer:l(()=>[s(b,{label:"Não",icon:"pi pi-times",onClick:Q,class:"p-button-text"}),s(b,{label:"Sim",icon:"pi pi-check",onClick:W,class:"p-button-text",autofocus:""})]),default:l(()=>[e[15]||(e[15]=a("div",{class:"flex align-items-center justify-content-center"},[a("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}}),a("span",null,"Tem certeza que deseja proceder?")],-1))]),_:1},8,["visible"])],64)}}};export{et as default};
