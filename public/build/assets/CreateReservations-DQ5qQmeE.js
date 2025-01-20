import{s as A}from"./index-ChpK7CEE.js";import{s as O}from"./index-Dgxy-sQW.js";import{s as Q}from"./index-ClEOQRzL.js";import{s as G}from"./index-BGfFvUok.js";import{s as H}from"./index-BXlgmuzs.js";import{u as J,r as s,w as K,d as X,c as _,b as a,a as l,e as h,f as t,j as F,n as P,t as T,k as Y,l as Z,F as ee,i as ae,o as u,g as se}from"./app-CTLQrJ3V.js";import{u as te}from"./vee-validate-Dazyykwj.js";import{d as oe}from"./moment-CQ1ixRO1.js";import{c as le,a as j}from"./index.esm-CQT6nZnd.js";import"./index-Cb5f9LtL.js";import"./index-ChSeYl5S.js";import"./index-BpMkn2hP.js";import"./index-BQLASpwo.js";import"./index-WNV5WMvS.js";import"./index-fobXNM8m.js";const re={key:0,class:"flex flex-col md:flex-row gap-12 min-h-screen items-center justify-center"},ie={class:"w-full"},ne={class:"flex flex-col gap-4 text-center"},de={key:1,class:"flex flex-col md:flex-row gap-12"},ce={class:"w-full"},ue={class:"card flex flex-col gap-4"},me={class:"w-full"},pe={class:"flex flex-col gap-2"},fe={id:"name-help",class:"p-error"},ve={class:"flex flex-col gap-2"},ge={id:"category_id-help",class:"p-error"},je={__name:"CreateReservations",setup(xe){const m=ae(),i=J();s(null);const p=s(!0),f=s(!1);let C=s(null);const k=s(""),v=s(null),q=s(1);s(15),s(0);const g=s(!1),S=s(null),n=s(!1),E=le({category_id:j().required().trim().label("Categoria"),name:j().required().trim().label("Name")}),{defineField:V,handleSubmit:U,resetForm:be,errors:d,setErrors:z}=te({validationSchema:E}),[x]=V("category_id"),[b]=V("name"),w=s();function D(){m&&m.back()}const B=()=>{g.value=!1},y=U(o=>{w.value!=null&&(o.image=w.value),n.value=!0,axios.post("/api/reservations",o,{headers:{"Content-Type":"multipart/form-data"}}).then(e=>{m.back(),i.add({severity:"success",summary:"Successo",detail:"SubCategoria criado com sucesso",life:3e3})}).catch(e=>{n.value=!1,i.add({severity:"error",summary:"Erro}",detail:`${e.response.data.message}`,life:3e3}),e.response.data.errors&&z(e.response.data.errors)}).finally(()=>{n.value=!1})}),$=async(o=1)=>{axios.get("/api/reservations/create",{params:{query:k.value}}).then(e=>{v.value=e.data.categories,S.value=e.data.categories,p.value=!1}).catch(e=>{p.value=!1,i.add({severity:"error",summary:`${e}`,detail:"Message Detail",life:3e3}),D()})},L=()=>{f.value=!0,axios.delete(`/api/reservations/${C.value}`).then(()=>{v.value.data=v.value.data.filter(o=>o.id!==C.value),B(),i.add({severity:"success",summary:"Sucesso",detail:"Sucesso ao apagar",life:3e3})}).catch(o=>{i.add({severity:"error",summary:"Erro",detail:`${o}`,life:3e3}),f.value=!1}).finally(()=>{f.value=!1})},R=oe(()=>{$(q.value)},300);return K(k,R),X(()=>{$()}),(o,e)=>{const N=H,c=G,I=Q,M=O,W=A;return u(),_(ee,null,[p.value?(u(),_("div",re,[a("div",ie,[a("div",ne,[l(N,{style:{width:"50px",height:"50px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"}),e[4]||(e[4]=a("p",null,"Por Favor Aguarde...",-1))])])])):(u(),_("div",de,[a("div",ce,[a("div",ue,[a("div",me,[l(c,{label:"Voltar",class:"mr-2 mb-2",onClick:D},{default:h(()=>e[5]||(e[5]=[a("i",{class:"pi pi-angle-left"},null,-1),se(" Voltar")])),_:1})]),e[8]||(e[8]=a("div",{class:"font-semibold text-xl"},"SubCategoria",-1)),e[9]||(e[9]=a("small",{class:"p-error"},"Os campos marcados * sao considerados campos obrigatorios.",-1)),a("form",{onSubmit:e[2]||(e[2]=(...r)=>t(y)&&t(y)(...r))},[a("div",pe,[e[6]||(e[6]=a("label",{for:"name1"},"Nome",-1)),l(I,{modelValue:t(b),"onUpdate:modelValue":e[0]||(e[0]=r=>F(b)?b.value=r:null),id:"name",placeholder:"Nome da SubCategoria",class:P({"p-invalid":t(d).name}),type:"text"},null,8,["modelValue","class"]),a("small",fe,T(t(d).name),1)]),a("div",ve,[e[7]||(e[7]=a("label",{for:"name1"},"Categoria",-1)),l(M,{modelValue:t(x),"onUpdate:modelValue":e[1]||(e[1]=r=>F(x)?x.value=r:null),options:S.value,optionLabel:"name",optionValue:"id",placeholder:"Selecionar",class:P({"p-invalid":t(d).category_id})},null,8,["modelValue","options","class"]),a("small",ge,T(t(d).category_id),1)]),l(c,{label:"Submeter",class:"mr-2 mb-2",onClick:t(y),disabled:n.value},null,8,["onClick","disabled"]),n.value?(u(),Y(N,{key:0,style:{width:"35px",height:"35px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"})):Z("",!0)],32)])])])),l(W,{header:"Confirmação",visible:g.value,"onUpdate:visible":e[3]||(e[3]=r=>g.value=r),style:{width:"350px"},modal:!0},{footer:h(()=>[l(c,{label:"Não",icon:"pi pi-times",onClick:B,class:"p-button-text"}),l(c,{label:"Sim",icon:"pi pi-check",onClick:L,class:"p-button-text",autofocus:""})]),default:h(()=>[e[10]||(e[10]=a("div",{class:"flex align-items-center justify-content-center"},[a("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}}),a("span",null,"Tem certeza que deseja proceder?")],-1))]),_:1},8,["visible"])],64)}}};export{je as default};
