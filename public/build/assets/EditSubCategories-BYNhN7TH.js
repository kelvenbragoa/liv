import{s as O}from"./index-CfiEnxX1.js";import{s as Q}from"./index-Bk30-qWL.js";import{s as G}from"./index-Bs78HhBk.js";import{s as H}from"./index-DiJnOeRa.js";import{s as J}from"./index-BsOBtNrI.js";import{u as K,r as s,w as X,d as Y,c as h,b as a,a as l,e as k,f as t,j as F,n as E,t as P,k as Z,l as ee,F as ae,i as se,o as v,g as te}from"./app-DuEcIjVO.js";import{c as oe,a as T,u as le}from"./index.esm-CoCtg6d4.js";import{d as ie}from"./moment-CQ1ixRO1.js";import"./index-C3g2dbQI.js";import"./index-BxH2F_K0.js";import"./index-VzDczUdc.js";import"./index-ufbaLfoF.js";import"./index-DjjqVgqq.js";import"./index-BCezfRIN.js";const re={key:0,class:"flex flex-col md:flex-row gap-12 min-h-screen items-center justify-center"},ne={class:"w-full"},ue={class:"flex flex-col gap-4 text-center"},de={key:1,class:"flex flex-col md:flex-row gap-12"},ce={class:"w-full"},me={class:"card flex flex-col gap-4"},pe={class:"w-full"},fe={class:"flex flex-col gap-2"},ve={id:"name-help",class:"p-error"},ge={class:"flex flex-col gap-2"},be={id:"category_id-help",class:"p-error"},Te={__name:"EditSubCategories",setup(ye){const r=se(),n=K();s(null);const g=s(!0),b=s(!1);let C=s(null);const S=s(""),u=s(null),j=s(1);s(15),s(0);const y=s(!1),V=s(null),d=s(!1),q=oe({category_id:T().required().trim().label("Department"),name:T().required().trim().label("Name")}),{defineField:x,handleSubmit:R,resetForm:xe,errors:c,setErrors:U}=le({validationSchema:q}),[m]=x("category_id"),[p]=x("name"),[z]=x("_method"),D=s();function $(){r&&r.back()}const w=()=>{y.value=!1},_=R(o=>{D.value!=null&&(o.image=D.value),d.value=!0,axios.post(`/api/subcategories/${r.currentRoute.value.params.id}`,o,{headers:{"Content-Type":"multipart/form-data"}}).then(e=>{r.back(),n.add({severity:"success",summary:"Successo",detail:"Categoria criado com sucesso",life:3e3})}).catch(e=>{d.value=!1,n.add({severity:"error",summary:"Erro}",detail:`${e.response.data.message}`,life:3e3}),e.response.data.errors&&U(e.response.data.errors)}).finally(()=>{d.value=!1})}),B=async(o=1)=>{axios.get(`/api/subcategories/${r.currentRoute.value.params.id}/edit`,{params:{query:S.value}}).then(e=>{u.value=e.data.subcategory,p.value=u.value.name,m.value=u.value.category_id,V.value=e.data.categories,z.value="put",g.value=!1}).catch(e=>{g.value=!1,n.add({severity:"error",summary:`${e}`,detail:"Message Detail",life:3e3}),$()})},L=()=>{b.value=!0,axios.delete(`/api/subcategories/${C.value}`).then(()=>{u.value.data=u.value.data.filter(o=>o.id!==C.value),w(),n.add({severity:"success",summary:"Sucesso",detail:"Sucesso ao apagar",life:3e3})}).catch(o=>{n.add({severity:"error",summary:"Erro",detail:`${o}`,life:3e3}),b.value=!1}).finally(()=>{b.value=!1})},I=ie(()=>{B(j.value)},300);return X(S,I),Y(()=>{B()}),(o,e)=>{const N=J,f=H,M=G,W=Q,A=O;return v(),h(ae,null,[g.value?(v(),h("div",re,[a("div",ne,[a("div",ue,[l(N,{style:{width:"50px",height:"50px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"}),e[4]||(e[4]=a("p",null,"Por Favor Aguarde...",-1))])])])):(v(),h("div",de,[a("div",ce,[a("div",me,[a("div",pe,[l(f,{label:"Voltar",class:"mr-2 mb-2",onClick:$},{default:k(()=>e[5]||(e[5]=[a("i",{class:"pi pi-angle-left"},null,-1),te(" Voltar")])),_:1})]),e[8]||(e[8]=a("div",{class:"font-semibold text-xl"},"Categoria",-1)),e[9]||(e[9]=a("small",{class:"p-error"},"Os campos marcados * sao considerados campos obrigatorios.",-1)),a("form",{onSubmit:e[2]||(e[2]=(...i)=>t(_)&&t(_)(...i))},[a("div",fe,[e[6]||(e[6]=a("label",{for:"name1"},"Nome",-1)),l(M,{modelValue:t(p),"onUpdate:modelValue":e[0]||(e[0]=i=>F(p)?p.value=i:null),id:"name",placeholder:"Nome da SubCategoria",class:E({"p-invalid":t(c).name}),type:"text"},null,8,["modelValue","class"]),a("small",ve,P(t(c).name),1)]),a("div",ge,[e[7]||(e[7]=a("label",{for:"name1"},"Categoria",-1)),l(W,{modelValue:t(m),"onUpdate:modelValue":e[1]||(e[1]=i=>F(m)?m.value=i:null),options:V.value,optionLabel:"name",optionValue:"id",placeholder:"Selecionar",class:E({"p-invalid":t(c).category_id})},null,8,["modelValue","options","class"]),a("small",be,P(t(c).category_id),1)]),l(f,{label:"Submeter",class:"mr-2 mb-2",onClick:t(_),disabled:d.value},null,8,["onClick","disabled"]),d.value?(v(),Z(N,{key:0,style:{width:"35px",height:"35px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"})):ee("",!0)],32)])])])),l(A,{header:"Confirmação",visible:y.value,"onUpdate:visible":e[3]||(e[3]=i=>y.value=i),style:{width:"350px"},modal:!0},{footer:k(()=>[l(f,{label:"Não",icon:"pi pi-times",onClick:w,class:"p-button-text"}),l(f,{label:"Sim",icon:"pi pi-check",onClick:L,class:"p-button-text",autofocus:""})]),default:k(()=>[e[10]||(e[10]=a("div",{class:"flex align-items-center justify-content-center"},[a("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}}),a("span",null,"Tem certeza que deseja proceder?")],-1))]),_:1},8,["visible"])],64)}}};export{Te as default};
