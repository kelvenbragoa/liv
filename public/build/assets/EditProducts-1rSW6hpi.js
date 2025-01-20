import{s as H}from"./index-ChpK7CEE.js";import{s as J}from"./index-Dgxy-sQW.js";import{s as K}from"./index-ClEOQRzL.js";import{s as X}from"./index-BGfFvUok.js";import{s as Y}from"./index-BXlgmuzs.js";import{u as Z,r as l,w as ee,d as ae,c as B,b as a,a as r,e as N,f as s,j as x,n as h,t as V,k as le,l as se,F as oe,i as te,o as k,g as re}from"./app-CTLQrJ3V.js";import{u as ie}from"./vee-validate-Dazyykwj.js";import{d as ne}from"./moment-CQ1ixRO1.js";import{c as ue,a as S}from"./index.esm-CQT6nZnd.js";import"./index-Cb5f9LtL.js";import"./index-ChSeYl5S.js";import"./index-BpMkn2hP.js";import"./index-BQLASpwo.js";import"./index-WNV5WMvS.js";import"./index-fobXNM8m.js";const de={key:0,class:"flex flex-col md:flex-row gap-12 min-h-screen items-center justify-center"},ce={class:"w-full"},me={class:"flex flex-col gap-4 text-center"},pe={key:1,class:"flex flex-col md:flex-row gap-12"},fe={class:"w-full"},ve={class:"card flex flex-col gap-4"},ge={class:"w-full"},_e={class:"flex flex-col gap-2"},be={id:"name-help",class:"p-error"},ye={class:"flex flex-col gap-2"},xe={id:"price-help",class:"p-error"},he={class:"flex flex-col gap-2"},Ve={id:"category_id-help",class:"p-error"},ke={class:"flex flex-col gap-2"},Se={id:"sub_category_id-help",class:"p-error"},We={__name:"EditProducts",setup(Ce){const d=te(),c=Z();l(null);const C=l(!0),P=l(!1);let q=l(null);const z=l(""),n=l(null),M=l(1);l(15),l(0);const $=l(!1),F=l(null),D=l(null),f=l(null),m=l(!1),W=ue({category_id:S().required().trim().label("Categoria"),name:S().required().trim().label("Name"),price:S().required().trim().label("Preco"),sub_category_id:S().required().trim().label("Name")}),{defineField:p,handleSubmit:A,resetForm:Pe,errors:i,setErrors:I}=ie({validationSchema:W}),[u]=p("category_id"),[v]=p("sub_category_id"),[g]=p("price"),[_]=p("name"),[O]=p("_method"),U=l();function E(){d&&d.back()}const T=()=>{$.value=!1},w=A(o=>{U.value!=null&&(o.image=U.value),m.value=!0,axios.post(`/api/products/${d.currentRoute.value.params.id}`,o,{headers:{"Content-Type":"multipart/form-data"}}).then(e=>{d.back(),c.add({severity:"success",summary:"Successo",detail:"Produto criado com sucesso",life:3e3})}).catch(e=>{m.value=!1,c.add({severity:"error",summary:"Erro}",detail:`${e.response.data.message}`,life:3e3}),e.response.data.errors&&I(e.response.data.errors)}).finally(()=>{m.value=!1})}),j=async(o=1)=>{axios.get(`/api/products/${d.currentRoute.value.params.id}/edit`,{params:{query:z.value}}).then(e=>{n.value=e.data.product,_.value=n.value.name,g.value=n.value.price,u.value=n.value.category_id,v.value=n.value.sub_category_id,F.value=e.data.categories,D.value=e.data.sub_categories,f.value=D.value.filter(b=>b.category_id===u.value),O.value="put",C.value=!1}).catch(e=>{C.value=!1,c.add({severity:"error",summary:`${e}`,detail:"Message Detail",life:3e3}),E()})},Q=()=>{P.value=!0,axios.delete(`/api/products/${q.value}`).then(()=>{n.value.data=n.value.data.filter(o=>o.id!==q.value),T(),c.add({severity:"success",summary:"Sucesso",detail:"Sucesso ao apagar",life:3e3})}).catch(o=>{c.add({severity:"error",summary:"Erro",detail:`${o}`,life:3e3}),P.value=!1}).finally(()=>{P.value=!1})};return ne(()=>{j(M.value)},300),ee(u,o=>{o?f.value=D.value.filter(e=>e.category_id===o):f.value=[]}),ae(()=>{j()}),(o,e)=>{const b=Y,y=X,L=K,R=J,G=H;return k(),B(oe,null,[C.value?(k(),B("div",de,[a("div",ce,[a("div",me,[r(b,{style:{width:"50px",height:"50px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"}),e[6]||(e[6]=a("p",null,"Por Favor Aguarde...",-1))])])])):(k(),B("div",pe,[a("div",fe,[a("div",ve,[a("div",ge,[r(y,{label:"Voltar",class:"mr-2 mb-2",onClick:E},{default:N(()=>e[7]||(e[7]=[a("i",{class:"pi pi-angle-left"},null,-1),re(" Voltar")])),_:1})]),e[12]||(e[12]=a("div",{class:"font-semibold text-xl"},"Produto",-1)),e[13]||(e[13]=a("small",{class:"p-error"},"Os campos marcados * sao considerados campos obrigatorios.",-1)),a("form",{onSubmit:e[4]||(e[4]=(...t)=>s(w)&&s(w)(...t))},[a("div",_e,[e[8]||(e[8]=a("label",{for:"name1"},"Nome",-1)),r(L,{modelValue:s(_),"onUpdate:modelValue":e[0]||(e[0]=t=>x(_)?_.value=t:null),id:"name",placeholder:"Nome da Produto",class:h({"p-invalid":s(i).name}),type:"text"},null,8,["modelValue","class"]),a("small",be,V(s(i).name),1)]),a("div",ye,[e[9]||(e[9]=a("label",{for:"name1"},"Preço",-1)),r(L,{modelValue:s(g),"onUpdate:modelValue":e[1]||(e[1]=t=>x(g)?g.value=t:null),id:"price",placeholder:"Preço",class:h({"p-invalid":s(i).price}),type:"number"},null,8,["modelValue","class"]),a("small",xe,V(s(i).price),1)]),a("div",he,[e[10]||(e[10]=a("label",{for:"name1"},"Categoria",-1)),r(R,{modelValue:s(u),"onUpdate:modelValue":e[2]||(e[2]=t=>x(u)?u.value=t:null),options:F.value,optionLabel:"name",optionValue:"id",placeholder:"Selecionar",class:h({"p-invalid":s(i).category_id})},null,8,["modelValue","options","class"]),a("small",Ve,V(s(i).category_id),1)]),a("div",ke,[e[11]||(e[11]=a("label",{for:"name1"},"SubCategoria",-1)),r(R,{modelValue:s(v),"onUpdate:modelValue":e[3]||(e[3]=t=>x(v)?v.value=t:null),options:f.value,optionLabel:"name",optionValue:"id",placeholder:"Selecionar",class:h({"p-invalid":s(i).sub_category_id})},null,8,["modelValue","options","class"]),a("small",Se,V(s(i).sub_category_id),1)]),r(y,{label:"Submeter",class:"mr-2 mb-2",onClick:s(w),disabled:m.value},null,8,["onClick","disabled"]),m.value?(k(),le(b,{key:0,style:{width:"35px",height:"35px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"})):se("",!0)],32)])])])),r(G,{header:"Confirmação",visible:$.value,"onUpdate:visible":e[5]||(e[5]=t=>$.value=t),style:{width:"350px"},modal:!0},{footer:N(()=>[r(y,{label:"Não",icon:"pi pi-times",onClick:T,class:"p-button-text"}),r(y,{label:"Sim",icon:"pi pi-check",onClick:Q,class:"p-button-text",autofocus:""})]),default:N(()=>[e[14]||(e[14]=a("div",{class:"flex align-items-center justify-content-center"},[a("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}}),a("span",null,"Tem certeza que deseja proceder?")],-1))]),_:1},8,["visible"])],64)}}};export{We as default};
