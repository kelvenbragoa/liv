import{s as G}from"./index-CfiEnxX1.js";import{s as H}from"./index-Bk30-qWL.js";import{s as J}from"./index-Bs78HhBk.js";import{s as K}from"./index-DiJnOeRa.js";import{s as X}from"./index-BsOBtNrI.js";import{u as Y,r as s,w as Z,d as ee,c as $,b as a,a as t,e as D,f as l,j as x,n as b,t as y,k as ae,l as le,F as se,i as oe,o as _,g as te}from"./app-DuEcIjVO.js";import{c as ie,a as B,u as re}from"./index.esm-CoCtg6d4.js";import{d as ne}from"./moment-CQ1ixRO1.js";import"./index-C3g2dbQI.js";import"./index-BxH2F_K0.js";import"./index-VzDczUdc.js";import"./index-ufbaLfoF.js";import"./index-DjjqVgqq.js";import"./index-BCezfRIN.js";const de={key:0,class:"flex flex-col md:flex-row gap-12 min-h-screen items-center justify-center"},ue={class:"w-full"},me={class:"flex flex-col gap-4 text-center"},ce={key:1,class:"flex flex-col md:flex-row gap-12"},pe={class:"w-full"},fe={class:"card flex flex-col gap-4"},ve={class:"w-full"},ge={class:"flex flex-col gap-2"},xe={id:"name-help",class:"p-error"},be={class:"flex flex-col gap-2"},ye={id:"email-help",class:"p-error"},_e={class:"flex flex-col gap-2"},he={id:"password-help",class:"p-error"},we={class:"flex flex-col gap-2"},ke={id:"role_id-help",class:"p-error"},Le={__name:"EditUsers",setup(Ve){const d=oe(),u=Y();s(null);const h=s(!0),w=s(!1);let E=s(null);const N=s(""),n=s(null),R=s(1);s(15),s(0);const k=s(!1),P=s(null),m=s(!1),z=ie({role_id:B().required().trim().label("Categoria"),name:B().required().trim().label("Name"),email:B().required().trim().label("Email")}),{defineField:c,handleSubmit:L,resetForm:Ce,errors:i,setErrors:I}=re({validationSchema:z}),[p]=c("role_id"),[f]=c("name"),[v]=c("email"),[V]=c("password"),[M]=c("_method"),U=s();function F(){d&&d.back()}const q=()=>{k.value=!1},C=L(r=>{U.value!=null&&(r.image=U.value),m.value=!0,axios.post(`/api/users/${d.currentRoute.value.params.id}`,r,{headers:{"Content-Type":"multipart/form-data"}}).then(e=>{d.back(),u.add({severity:"success",summary:"Successo",detail:"Categoria criado com sucesso",life:3e3})}).catch(e=>{m.value=!1,u.add({severity:"error",summary:"Erro}",detail:`${e.response.data.message}`,life:3e3}),e.response.data.errors&&I(e.response.data.errors)}).finally(()=>{m.value=!1})}),T=async(r=1)=>{axios.get(`/api/users/${d.currentRoute.value.params.id}/edit`,{params:{query:N.value}}).then(e=>{n.value=e.data.user,f.value=n.value.name,v.value=n.value.email,p.value=n.value.role_id,P.value=e.data.roles,M.value="put",h.value=!1}).catch(e=>{h.value=!1,u.add({severity:"error",summary:`${e}`,detail:"Message Detail",life:3e3}),F()})},W=()=>{w.value=!0,axios.delete(`/api/users/${E.value}`).then(()=>{n.value.data=n.value.data.filter(r=>r.id!==E.value),q(),u.add({severity:"success",summary:"Sucesso",detail:"Sucesso ao apagar",life:3e3})}).catch(r=>{u.add({severity:"error",summary:"Erro",detail:`${r}`,life:3e3}),w.value=!1}).finally(()=>{w.value=!1})},A=ne(()=>{T(R.value)},300);return Z(N,A),ee(()=>{T()}),(r,e)=>{const j=X,g=K,S=J,O=H,Q=G;return _(),$(se,null,[h.value?(_(),$("div",de,[a("div",ue,[a("div",me,[t(j,{style:{width:"50px",height:"50px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"}),e[6]||(e[6]=a("p",null,"Por Favor Aguarde...",-1))])])])):(_(),$("div",ce,[a("div",pe,[a("div",fe,[a("div",ve,[t(g,{label:"Voltar",class:"mr-2 mb-2",onClick:F},{default:D(()=>e[7]||(e[7]=[a("i",{class:"pi pi-angle-left"},null,-1),te(" Voltar")])),_:1})]),e[12]||(e[12]=a("div",{class:"font-semibold text-xl"},"Usuário",-1)),e[13]||(e[13]=a("small",{class:"p-error"},"Os campos marcados * sao considerados campos obrigatorios.",-1)),a("form",{onSubmit:e[4]||(e[4]=(...o)=>l(C)&&l(C)(...o))},[a("div",ge,[e[8]||(e[8]=a("label",{for:"name1"},"Nome",-1)),t(S,{modelValue:l(f),"onUpdate:modelValue":e[0]||(e[0]=o=>x(f)?f.value=o:null),id:"name",placeholder:"Nome",class:b({"p-invalid":l(i).name}),type:"text"},null,8,["modelValue","class"]),a("small",xe,y(l(i).name),1)]),a("div",be,[e[9]||(e[9]=a("label",{for:"email"},"Email",-1)),t(S,{modelValue:l(v),"onUpdate:modelValue":e[1]||(e[1]=o=>x(v)?v.value=o:null),id:"email",placeholder:"Email",class:b({"p-invalid":l(i).email}),type:"email",readonly:""},null,8,["modelValue","class"]),a("small",ye,y(l(i).email),1)]),a("div",_e,[e[10]||(e[10]=a("label",{for:"password"},"Password",-1)),t(S,{modelValue:l(V),"onUpdate:modelValue":e[2]||(e[2]=o=>x(V)?V.value=o:null),id:"password",placeholder:"Password",class:b({"p-invalid":l(i).password}),type:"password"},null,8,["modelValue","class"]),a("small",he,y(l(i).name),1)]),a("div",we,[e[11]||(e[11]=a("label",{for:"name1"},"Previlégio",-1)),t(O,{modelValue:l(p),"onUpdate:modelValue":e[3]||(e[3]=o=>x(p)?p.value=o:null),options:P.value,optionLabel:"name",optionValue:"id",placeholder:"Selecionar",class:b({"p-invalid":l(i).role_id})},null,8,["modelValue","options","class"]),a("small",ke,y(l(i).role_id),1)]),t(g,{label:"Submeter",class:"mr-2 mb-2",onClick:l(C),disabled:m.value},null,8,["onClick","disabled"]),m.value?(_(),ae(j,{key:0,style:{width:"35px",height:"35px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"})):le("",!0)],32)])])])),t(Q,{header:"Confirmação",visible:k.value,"onUpdate:visible":e[5]||(e[5]=o=>k.value=o),style:{width:"350px"},modal:!0},{footer:D(()=>[t(g,{label:"Não",icon:"pi pi-times",onClick:q,class:"p-button-text"}),t(g,{label:"Sim",icon:"pi pi-check",onClick:W,class:"p-button-text",autofocus:""})]),default:D(()=>[e[14]||(e[14]=a("div",{class:"flex align-items-center justify-content-center"},[a("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}}),a("span",null,"Tem certeza que deseja proceder?")],-1))]),_:1},8,["visible"])],64)}}};export{Le as default};
