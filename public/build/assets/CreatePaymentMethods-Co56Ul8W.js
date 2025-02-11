import{s as z}from"./index-DgFzkDdR.js";import{s as U}from"./index-DRQrLLY9.js";import{s as I}from"./index-BORxrjYa.js";import{s as L}from"./index-C4Pb4f0M.js";import{u as R,r as s,w as W,o as A,c as y,a,b as o,d as b,g as l,l as O,n as Q,t as G,j as H,i as J,F as K,k as X,e as m,f as Y}from"./app-DjjNWAyZ.js";import{u as Z}from"./vee-validate-CqMsdXBQ.js";import{d as ee}from"./moment-CQ1ixRO1.js";import{c as ae,a as se}from"./index.esm-CQT6nZnd.js";import"./index-CsBO25G4.js";import"./index-fpmgf2cM.js";import"./index-BfKxmIB5.js";const te={key:0,class:"flex flex-col md:flex-row gap-12 min-h-screen items-center justify-center"},oe={class:"w-full"},le={class:"flex flex-col gap-4 text-center"},re={key:1,class:"flex flex-col md:flex-row gap-12"},ie={class:"w-full"},ne={class:"card flex flex-col gap-4"},de={class:"w-full"},me={class:"flex flex-col gap-2"},ce={id:"name-help",class:"p-error"},we={__name:"CreatePaymentMethods",setup(ue){const c=X(),r=R();s(null);const u=s(!1),p=s(!1);let h=s(null);const k=s(""),f=s(null),B=s(1);s(15),s(0);const v=s(!1),V=s(null),i=s(!1),$=ae({name:se().required().trim().label("Name")}),{defineField:N,handleSubmit:F,resetForm:pe,errors:_,setErrors:P}=Z({validationSchema:$}),[g]=N("name"),C=s();function S(){c&&c.back()}const w=()=>{v.value=!1},x=F(t=>{C.value!=null&&(t.image=C.value),i.value=!0,axios.post("/api/paymentmethods",t,{headers:{"Content-Type":"multipart/form-data"}}).then(e=>{c.back(),r.add({severity:"success",summary:"Successo",detail:"Mesa criado com sucesso",life:3e3})}).catch(e=>{i.value=!1,r.add({severity:"error",summary:"Erro}",detail:`${e.response.data.message}`,life:3e3}),e.response.data.errors&&P(e.response.data.errors)}).finally(()=>{i.value=!1})}),M=async(t=1)=>{axios.get("/api/paymentmethods/create",{params:{query:k.value}}).then(e=>{f.value=e.data.customer,V.value=e.data.departments,u.value=!1}).catch(e=>{u.value=!1,r.add({severity:"error",summary:`${e}`,detail:"Message Detail",life:3e3}),S()})},T=()=>{p.value=!0,axios.delete(`/api/paymentmethods/${h.value}`).then(()=>{f.value.data=f.value.data.filter(t=>t.id!==h.value),w(),r.add({severity:"success",summary:"Sucesso",detail:"Sucesso ao apagar",life:3e3})}).catch(t=>{r.add({severity:"error",summary:"Erro",detail:`${t}`,life:3e3}),p.value=!1}).finally(()=>{p.value=!1})},j=ee(()=>{M(B.value)},300);return W(k,j),A(()=>{}),(t,e)=>{const D=L,d=I,E=U,q=z;return m(),y(K,null,[u.value?(m(),y("div",te,[a("div",oe,[a("div",le,[o(D,{style:{width:"50px",height:"50px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"}),e[3]||(e[3]=a("p",null,"Por Favor Aguarde...",-1))])])])):(m(),y("div",re,[a("div",ie,[a("div",ne,[a("div",de,[o(d,{label:"Voltar",class:"mr-2 mb-2",onClick:S},{default:b(()=>e[4]||(e[4]=[a("i",{class:"pi pi-angle-left"},null,-1),Y(" Voltar")])),_:1})]),e[6]||(e[6]=a("div",{class:"font-semibold text-xl"},"Mesa",-1)),e[7]||(e[7]=a("small",{class:"p-error"},"Os campos marcados * sao considerados campos obrigatorios.",-1)),a("form",{onSubmit:e[1]||(e[1]=(...n)=>l(x)&&l(x)(...n))},[a("div",me,[e[5]||(e[5]=a("label",{for:"name"},"Nome",-1)),o(E,{modelValue:l(g),"onUpdate:modelValue":e[0]||(e[0]=n=>O(g)?g.value=n:null),id:"name",placeholder:"Nome",class:Q({"p-invalid":l(_).name}),type:"text"},null,8,["modelValue","class"]),a("small",ce,G(l(_).name),1)]),o(d,{label:"Submeter",class:"mr-2 mb-2",onClick:l(x),disabled:i.value},null,8,["onClick","disabled"]),i.value?(m(),H(D,{key:0,style:{width:"35px",height:"35px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"})):J("",!0)],32)])])])),o(q,{header:"Confirmação",visible:v.value,"onUpdate:visible":e[2]||(e[2]=n=>v.value=n),style:{width:"350px"},modal:!0},{footer:b(()=>[o(d,{label:"Não",icon:"pi pi-times",onClick:w,class:"p-button-text"}),o(d,{label:"Sim",icon:"pi pi-check",onClick:T,class:"p-button-text",autofocus:""})]),default:b(()=>[e[8]||(e[8]=a("div",{class:"flex align-items-center justify-content-center"},[a("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}}),a("span",null,"Tem certeza que deseja proceder?")],-1))]),_:1},8,["visible"])],64)}}};export{we as default};
