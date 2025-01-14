import{s as O}from"./index-CfiEnxX1.js";import{s as Q}from"./index-Bk30-qWL.js";import{s as G}from"./index-Bs78HhBk.js";import{s as H}from"./index-DiJnOeRa.js";import{s as J}from"./index-BsOBtNrI.js";import{u as K,r as s,w as X,d as Y,c as h,b as a,a as l,e as k,f as t,j as F,n as E,t as P,k as Z,l as ee,F as ae,i as se,o as v,g as te}from"./app-DuEcIjVO.js";import{c as oe,a as R,u as le}from"./index.esm-CoCtg6d4.js";import{d as re}from"./moment-CQ1ixRO1.js";import"./index-C3g2dbQI.js";import"./index-BxH2F_K0.js";import"./index-VzDczUdc.js";import"./index-ufbaLfoF.js";import"./index-DjjqVgqq.js";import"./index-BCezfRIN.js";const ie={key:0,class:"flex flex-col md:flex-row gap-12 min-h-screen items-center justify-center"},ne={class:"w-full"},de={class:"flex flex-col gap-4 text-center"},ue={key:1,class:"flex flex-col md:flex-row gap-12"},ce={class:"w-full"},me={class:"card flex flex-col gap-4"},pe={class:"w-full"},fe={class:"flex flex-col gap-2"},ve={id:"name-help",class:"p-error"},ge={class:"flex flex-col gap-2"},ye={id:"category_id-help",class:"p-error"},Re={__name:"EditReservations",setup(xe){const i=se(),n=K();s(null);const g=s(!0),y=s(!1);let C=s(null);const S=s(""),d=s(null),T=s(1);s(15),s(0);const x=s(!1),V=s(null),u=s(!1),j=oe({category_id:R().required().trim().label("Department"),name:R().required().trim().label("Name")}),{defineField:b,handleSubmit:q,resetForm:be,errors:c,setErrors:U}=le({validationSchema:j}),[m]=b("category_id"),[p]=b("name"),[z]=b("_method"),D=s();function $(){i&&i.back()}const w=()=>{x.value=!1},_=q(o=>{D.value!=null&&(o.image=D.value),u.value=!0,axios.post(`/api/reservations/${i.currentRoute.value.params.id}`,o,{headers:{"Content-Type":"multipart/form-data"}}).then(e=>{i.back(),n.add({severity:"success",summary:"Successo",detail:"Categoria criado com sucesso",life:3e3})}).catch(e=>{u.value=!1,n.add({severity:"error",summary:"Erro}",detail:`${e.response.data.message}`,life:3e3}),e.response.data.errors&&U(e.response.data.errors)}).finally(()=>{u.value=!1})}),B=async(o=1)=>{axios.get(`/api/reservations/${i.currentRoute.value.params.id}/edit`,{params:{query:S.value}}).then(e=>{d.value=e.data.subcategory,p.value=d.value.name,m.value=d.value.category_id,V.value=e.data.categories,z.value="put",g.value=!1}).catch(e=>{g.value=!1,n.add({severity:"error",summary:`${e}`,detail:"Message Detail",life:3e3}),$()})},L=()=>{y.value=!0,axios.delete(`/api/reservations/${C.value}`).then(()=>{d.value.data=d.value.data.filter(o=>o.id!==C.value),w(),n.add({severity:"success",summary:"Sucesso",detail:"Sucesso ao apagar",life:3e3})}).catch(o=>{n.add({severity:"error",summary:"Erro",detail:`${o}`,life:3e3}),y.value=!1}).finally(()=>{y.value=!1})},I=re(()=>{B(T.value)},300);return X(S,I),Y(()=>{B()}),(o,e)=>{const N=J,f=H,M=G,W=Q,A=O;return v(),h(ae,null,[g.value?(v(),h("div",ie,[a("div",ne,[a("div",de,[l(N,{style:{width:"50px",height:"50px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"}),e[4]||(e[4]=a("p",null,"Por Favor Aguarde...",-1))])])])):(v(),h("div",ue,[a("div",ce,[a("div",me,[a("div",pe,[l(f,{label:"Voltar",class:"mr-2 mb-2",onClick:$},{default:k(()=>e[5]||(e[5]=[a("i",{class:"pi pi-angle-left"},null,-1),te(" Voltar")])),_:1})]),e[8]||(e[8]=a("div",{class:"font-semibold text-xl"},"Categoria",-1)),e[9]||(e[9]=a("small",{class:"p-error"},"Os campos marcados * sao considerados campos obrigatorios.",-1)),a("form",{onSubmit:e[2]||(e[2]=(...r)=>t(_)&&t(_)(...r))},[a("div",fe,[e[6]||(e[6]=a("label",{for:"name1"},"Nome",-1)),l(M,{modelValue:t(p),"onUpdate:modelValue":e[0]||(e[0]=r=>F(p)?p.value=r:null),id:"name",placeholder:"Nome da SubCategoria",class:E({"p-invalid":t(c).name}),type:"text"},null,8,["modelValue","class"]),a("small",ve,P(t(c).name),1)]),a("div",ge,[e[7]||(e[7]=a("label",{for:"name1"},"Categoria",-1)),l(W,{modelValue:t(m),"onUpdate:modelValue":e[1]||(e[1]=r=>F(m)?m.value=r:null),options:V.value,optionLabel:"name",optionValue:"id",placeholder:"Selecionar",class:E({"p-invalid":t(c).category_id})},null,8,["modelValue","options","class"]),a("small",ye,P(t(c).category_id),1)]),l(f,{label:"Submeter",class:"mr-2 mb-2",onClick:t(_),disabled:u.value},null,8,["onClick","disabled"]),u.value?(v(),Z(N,{key:0,style:{width:"35px",height:"35px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"})):ee("",!0)],32)])])])),l(A,{header:"Confirmação",visible:x.value,"onUpdate:visible":e[3]||(e[3]=r=>x.value=r),style:{width:"350px"},modal:!0},{footer:k(()=>[l(f,{label:"Não",icon:"pi pi-times",onClick:w,class:"p-button-text"}),l(f,{label:"Sim",icon:"pi pi-check",onClick:L,class:"p-button-text",autofocus:""})]),default:k(()=>[e[10]||(e[10]=a("div",{class:"flex align-items-center justify-content-center"},[a("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}}),a("span",null,"Tem certeza que deseja proceder?")],-1))]),_:1},8,["visible"])],64)}}};export{Re as default};
