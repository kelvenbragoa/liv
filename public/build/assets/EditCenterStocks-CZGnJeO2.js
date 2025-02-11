import{s as O}from"./index-DgFzkDdR.js";import{s as Q}from"./index-DPXoA4HO.js";import{s as G}from"./index-DRQrLLY9.js";import{s as H}from"./index-BORxrjYa.js";import{s as J}from"./index-C4Pb4f0M.js";import{u as K,r as t,w as X,o as Y,c as k,a,b as l,d as h,g as s,l as F,n as E,t as P,j as Z,i as ee,F as ae,k as te,e as v,f as se}from"./app-DjjNWAyZ.js";import{u as oe}from"./vee-validate-CqMsdXBQ.js";import{d as le}from"./moment-CQ1ixRO1.js";import{c as re,a as R}from"./index.esm-CQT6nZnd.js";import"./index-CsBO25G4.js";import"./index-fpmgf2cM.js";import"./index-B3ELJNnu.js";import"./index-BfKxmIB5.js";import"./index-D0L_gmj7.js";import"./index--CR5sDXX.js";const ie={key:0,class:"flex flex-col md:flex-row gap-12 min-h-screen items-center justify-center"},ne={class:"w-full"},de={class:"flex flex-col gap-4 text-center"},me={key:1,class:"flex flex-col md:flex-row gap-12"},ue={class:"w-full"},ce={class:"card flex flex-col gap-4"},pe={class:"w-full"},fe={class:"flex flex-col gap-2"},ve={id:"name-help",class:"p-error"},ge={class:"flex flex-col gap-2"},xe={id:"department_id-help",class:"p-error"},Te={__name:"EditCenterStocks",setup(be){const i=te(),n=K();t(null);const g=t(!0),x=t(!1);let C=t(null);const S=t(""),d=t(null),T=t(1);t(15),t(0);const b=t(!1),D=t(null),m=t(!1),j=re({department_id:R().required().trim().label("Department"),name:R().required().trim().label("Name")}),{defineField:y,handleSubmit:q,resetForm:ye,errors:u,setErrors:U}=oe({validationSchema:j}),[c]=y("department_id"),[p]=y("name"),[z]=y("_method"),V=t();function $(){i&&i.back()}const w=()=>{b.value=!1},_=q(o=>{V.value!=null&&(o.image=V.value),m.value=!0,axios.post(`/api/centerstocks/${i.currentRoute.value.params.id}`,o,{headers:{"Content-Type":"multipart/form-data"}}).then(e=>{i.back(),n.add({severity:"success",summary:"Successo",detail:"Categoria criado com sucesso",life:3e3})}).catch(e=>{m.value=!1,n.add({severity:"error",summary:"Erro}",detail:`${e.response.data.message}`,life:3e3}),e.response.data.errors&&U(e.response.data.errors)}).finally(()=>{m.value=!1})}),B=async(o=1)=>{axios.get(`/api/centerstocks/${i.currentRoute.value.params.id}/edit`,{params:{query:S.value}}).then(e=>{d.value=e.data.category,p.value=d.value.name,c.value=d.value.department_id,D.value=e.data.departments,z.value="put",g.value=!1}).catch(e=>{g.value=!1,n.add({severity:"error",summary:`${e}`,detail:"Message Detail",life:3e3}),$()})},L=()=>{x.value=!0,axios.delete(`/api/centerstocks/${C.value}`).then(()=>{d.value.data=d.value.data.filter(o=>o.id!==C.value),w(),n.add({severity:"success",summary:"Sucesso",detail:"Sucesso ao apagar",life:3e3})}).catch(o=>{n.add({severity:"error",summary:"Erro",detail:`${o}`,life:3e3}),x.value=!1}).finally(()=>{x.value=!1})},I=le(()=>{B(T.value)},300);return X(S,I),Y(()=>{B()}),(o,e)=>{const N=J,f=H,M=G,W=Q,A=O;return v(),k(ae,null,[g.value?(v(),k("div",ie,[a("div",ne,[a("div",de,[l(N,{style:{width:"50px",height:"50px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"}),e[4]||(e[4]=a("p",null,"Por Favor Aguarde...",-1))])])])):(v(),k("div",me,[a("div",ue,[a("div",ce,[a("div",pe,[l(f,{label:"Voltar",class:"mr-2 mb-2",onClick:$},{default:h(()=>e[5]||(e[5]=[a("i",{class:"pi pi-angle-left"},null,-1),se(" Voltar")])),_:1})]),e[8]||(e[8]=a("div",{class:"font-semibold text-xl"},"Categoria",-1)),e[9]||(e[9]=a("small",{class:"p-error"},"Os campos marcados * sao considerados campos obrigatorios.",-1)),a("form",{onSubmit:e[2]||(e[2]=(...r)=>s(_)&&s(_)(...r))},[a("div",fe,[e[6]||(e[6]=a("label",{for:"name1"},"Nome",-1)),l(M,{modelValue:s(p),"onUpdate:modelValue":e[0]||(e[0]=r=>F(p)?p.value=r:null),id:"name",placeholder:"Nome da categoria",class:E({"p-invalid":s(u).name}),type:"text"},null,8,["modelValue","class"]),a("small",ve,P(s(u).name),1)]),a("div",ge,[e[7]||(e[7]=a("label",{for:"name1"},"Departamento Responsável",-1)),l(W,{modelValue:s(c),"onUpdate:modelValue":e[1]||(e[1]=r=>F(c)?c.value=r:null),options:D.value,optionLabel:"name",optionValue:"id",placeholder:"Selecionar",class:E({"p-invalid":s(u).department_id})},null,8,["modelValue","options","class"]),a("small",xe,P(s(u).department_id),1)]),l(f,{label:"Submeter",class:"mr-2 mb-2",onClick:s(_),disabled:m.value},null,8,["onClick","disabled"]),m.value?(v(),Z(N,{key:0,style:{width:"35px",height:"35px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"})):ee("",!0)],32)])])])),l(A,{header:"Confirmação",visible:b.value,"onUpdate:visible":e[3]||(e[3]=r=>b.value=r),style:{width:"350px"},modal:!0},{footer:h(()=>[l(f,{label:"Não",icon:"pi pi-times",onClick:w,class:"p-button-text"}),l(f,{label:"Sim",icon:"pi pi-check",onClick:L,class:"p-button-text",autofocus:""})]),default:h(()=>[e[10]||(e[10]=a("div",{class:"flex align-items-center justify-content-center"},[a("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}}),a("span",null,"Tem certeza que deseja proceder?")],-1))]),_:1},8,["visible"])],64)}}};export{Te as default};
