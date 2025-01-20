import{s as O}from"./index-ChpK7CEE.js";import{s as Q}from"./index-ClEOQRzL.js";import{s as G}from"./index-BGfFvUok.js";import{s as H}from"./index-BXlgmuzs.js";import{u as J,r as s,w as K,d as X,c as $,b as l,a as t,e as B,f as a,j as y,n as h,t as _,k as Y,l as Z,F as ee,i as le,o as k,g as ae}from"./app-CTLQrJ3V.js";import{u as se}from"./vee-validate-Dazyykwj.js";import{d as oe}from"./moment-CQ1ixRO1.js";import{c as te,a as V}from"./index.esm-CQT6nZnd.js";import"./index-Cb5f9LtL.js";import"./index-ChSeYl5S.js";import"./index-BQLASpwo.js";const ie={key:0,class:"flex flex-col md:flex-row gap-12 min-h-screen items-center justify-center"},re={class:"w-full"},ne={class:"flex flex-col gap-4 text-center"},de={key:1,class:"flex flex-col md:flex-row gap-12"},me={class:"w-full"},ue={class:"card flex flex-col gap-4"},ce={class:"w-full"},pe={class:"flex flex-col gap-2"},fe={id:"name-help",class:"p-error"},ve={class:"flex flex-col gap-2"},be={id:"mobile-help",class:"p-error"},xe={class:"flex flex-col gap-2"},ge={id:"email-help",class:"p-error"},ye={class:"flex flex-col gap-2"},he={id:"address-help",class:"p-error"},Fe={__name:"EditCustomers",setup(_e){const d=le(),m=J();s(null);const C=s(!0),S=s(!1);let E=s(null);const N=s(""),n=s(null),j=s(1);s(15),s(0);const w=s(!1);s(null);const u=s(!1),R=te({email:V().required().trim().label("Email"),name:V().required().trim().label("Name"),mobile:V().required().trim().label("mobile"),address:V().required().trim().label("address")}),{defineField:c,handleSubmit:z,resetForm:ke,errors:i,setErrors:I}=se({validationSchema:R}),[p]=c("email"),[f]=c("name"),[v]=c("mobile"),[b]=c("address"),[L]=c("_method"),T=s();function q(){d&&d.back()}const F=()=>{w.value=!1},D=z(r=>{T.value!=null&&(r.image=T.value),u.value=!0,axios.post(`/api/customers/${d.currentRoute.value.params.id}`,r,{headers:{"Content-Type":"multipart/form-data"}}).then(e=>{d.back(),m.add({severity:"success",summary:"Successo",detail:"Categoria criado com sucesso",life:3e3})}).catch(e=>{u.value=!1,m.add({severity:"error",summary:"Erro}",detail:`${e.response.data.message}`,life:3e3}),e.response.data.errors&&I(e.response.data.errors)}).finally(()=>{u.value=!1})}),U=async(r=1)=>{axios.get(`/api/customers/${d.currentRoute.value.params.id}/edit`,{params:{query:N.value}}).then(e=>{n.value=e.data.customer,f.value=n.value.name,v.value=n.value.mobile,p.value=n.value.email,b.value=n.value.address,L.value="put",C.value=!1}).catch(e=>{C.value=!1,m.add({severity:"error",summary:`${e}`,detail:"Message Detail",life:3e3}),q()})},M=()=>{S.value=!0,axios.delete(`/api/customers/${E.value}`).then(()=>{n.value.data=n.value.data.filter(r=>r.id!==E.value),F(),m.add({severity:"success",summary:"Sucesso",detail:"Sucesso ao apagar",life:3e3})}).catch(r=>{m.add({severity:"error",summary:"Erro",detail:`${r}`,life:3e3}),S.value=!1}).finally(()=>{S.value=!1})},W=oe(()=>{U(j.value)},300);return K(N,W),X(()=>{U()}),(r,e)=>{const P=H,x=G,g=Q,A=O;return k(),$(ee,null,[C.value?(k(),$("div",ie,[l("div",re,[l("div",ne,[t(P,{style:{width:"50px",height:"50px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"}),e[6]||(e[6]=l("p",null,"Por Favor Aguarde...",-1))])])])):(k(),$("div",de,[l("div",me,[l("div",ue,[l("div",ce,[t(x,{label:"Voltar",class:"mr-2 mb-2",onClick:q},{default:B(()=>e[7]||(e[7]=[l("i",{class:"pi pi-angle-left"},null,-1),ae(" Voltar")])),_:1})]),e[12]||(e[12]=l("div",{class:"font-semibold text-xl"},"Cliente",-1)),e[13]||(e[13]=l("small",{class:"p-error"},"Os campos marcados * sao considerados campos obrigatorios.",-1)),l("form",{onSubmit:e[4]||(e[4]=(...o)=>a(D)&&a(D)(...o))},[l("div",pe,[e[8]||(e[8]=l("label",{for:"name"},"Nome",-1)),t(g,{modelValue:a(f),"onUpdate:modelValue":e[0]||(e[0]=o=>y(f)?f.value=o:null),id:"name",placeholder:"Nome",class:h({"p-invalid":a(i).name}),type:"text"},null,8,["modelValue","class"]),l("small",fe,_(a(i).name),1)]),l("div",ve,[e[9]||(e[9]=l("label",{for:"mobile"},"Telefone",-1)),t(g,{modelValue:a(v),"onUpdate:modelValue":e[1]||(e[1]=o=>y(v)?v.value=o:null),id:"mobile",placeholder:"Telefone",class:h({"p-invalid":a(i).mobile}),type:"text"},null,8,["modelValue","class"]),l("small",be,_(a(i).mobile),1)]),l("div",xe,[e[10]||(e[10]=l("label",{for:"email"},"Email",-1)),t(g,{modelValue:a(p),"onUpdate:modelValue":e[2]||(e[2]=o=>y(p)?p.value=o:null),id:"email",placeholder:"Email",class:h({"p-invalid":a(i).email}),type:"email"},null,8,["modelValue","class"]),l("small",ge,_(a(i).email),1)]),l("div",ye,[e[11]||(e[11]=l("label",{for:"address"},"Endereço",-1)),t(g,{modelValue:a(b),"onUpdate:modelValue":e[3]||(e[3]=o=>y(b)?b.value=o:null),id:"address",placeholder:"Endereço",class:h({"p-invalid":a(i).address}),type:"text"},null,8,["modelValue","class"]),l("small",he,_(a(i).address),1)]),t(x,{label:"Submeter",class:"mr-2 mb-2",onClick:a(D),disabled:u.value},null,8,["onClick","disabled"]),u.value?(k(),Y(P,{key:0,style:{width:"35px",height:"35px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"})):Z("",!0)],32)])])])),t(A,{header:"Confirmação",visible:w.value,"onUpdate:visible":e[5]||(e[5]=o=>w.value=o),style:{width:"350px"},modal:!0},{footer:B(()=>[t(x,{label:"Não",icon:"pi pi-times",onClick:F,class:"p-button-text"}),t(x,{label:"Sim",icon:"pi pi-check",onClick:M,class:"p-button-text",autofocus:""})]),default:B(()=>[e[14]||(e[14]=l("div",{class:"flex align-items-center justify-content-center"},[l("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}}),l("span",null,"Tem certeza que deseja proceder?")],-1))]),_:1},8,["visible"])],64)}}};export{Fe as default};
