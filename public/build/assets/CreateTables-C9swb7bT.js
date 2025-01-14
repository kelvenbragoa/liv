import{s as W}from"./index-CfiEnxX1.js";import{s as A}from"./index-Bs78HhBk.js";import{s as O}from"./index-DiJnOeRa.js";import{s as Q}from"./index-BsOBtNrI.js";import{u as G,r as s,w as H,d as J,c as h,b as a,a as o,e as k,f as t,j as N,n as F,t as T,k as K,l as X,F as Y,i as Z,o as u,g as ee}from"./app-DuEcIjVO.js";import{c as ae,a as P,u as se}from"./index.esm-CoCtg6d4.js";import{d as te}from"./moment-CQ1ixRO1.js";import"./index-C3g2dbQI.js";import"./index-BxH2F_K0.js";import"./index-ufbaLfoF.js";const le={key:0,class:"flex flex-col md:flex-row gap-12 min-h-screen items-center justify-center"},oe={class:"w-full"},ie={class:"flex flex-col gap-4 text-center"},re={key:1,class:"flex flex-col md:flex-row gap-12"},ne={class:"w-full"},ce={class:"card flex flex-col gap-4"},de={class:"w-full"},ue={class:"flex flex-col gap-2"},me={id:"name-help",class:"p-error"},pe={class:"flex flex-col gap-2"},fe={id:"capacity-help",class:"p-error"},De={__name:"CreateTables",setup(ve){const m=Z(),r=G();s(null);const p=s(!1),f=s(!1);let _=s(null);const C=s(""),v=s(null),j=s(1);s(15),s(0);const y=s(!1),q=s(null),n=s(!1),E=ae({name:P().required().trim().label("Name"),capacity:P().required().trim().label("capacity")}),{defineField:S,handleSubmit:M,resetForm:ye,errors:c,setErrors:U}=se({validationSchema:E}),[g]=S("name"),[x]=S("capacity"),V=s();function w(){m&&m.back()}const D=()=>{y.value=!1},b=M(l=>{V.value!=null&&(l.image=V.value),n.value=!0,axios.post("/api/tables",l,{headers:{"Content-Type":"multipart/form-data"}}).then(e=>{m.back(),r.add({severity:"success",summary:"Successo",detail:"Mesa criado com sucesso",life:3e3})}).catch(e=>{n.value=!1,r.add({severity:"error",summary:"Erro}",detail:`${e.response.data.message}`,life:3e3}),e.response.data.errors&&U(e.response.data.errors)}).finally(()=>{n.value=!1})}),z=async(l=1)=>{axios.get("/api/tables/create",{params:{query:C.value}}).then(e=>{v.value=e.data.customer,q.value=e.data.departments,p.value=!1}).catch(e=>{p.value=!1,r.add({severity:"error",summary:`${e}`,detail:"Message Detail",life:3e3}),w()})},I=()=>{f.value=!0,axios.delete(`/api/tables/${_.value}`).then(()=>{v.value.data=v.value.data.filter(l=>l.id!==_.value),D(),r.add({severity:"success",summary:"Sucesso",detail:"Sucesso ao apagar",life:3e3})}).catch(l=>{r.add({severity:"error",summary:"Erro",detail:`${l}`,life:3e3}),f.value=!1}).finally(()=>{f.value=!1})},L=te(()=>{z(j.value)},300);return H(C,L),J(()=>{}),(l,e)=>{const B=Q,d=O,$=A,R=W;return u(),h(Y,null,[p.value?(u(),h("div",le,[a("div",oe,[a("div",ie,[o(B,{style:{width:"50px",height:"50px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"}),e[4]||(e[4]=a("p",null,"Por Favor Aguarde...",-1))])])])):(u(),h("div",re,[a("div",ne,[a("div",ce,[a("div",de,[o(d,{label:"Voltar",class:"mr-2 mb-2",onClick:w},{default:k(()=>e[5]||(e[5]=[a("i",{class:"pi pi-angle-left"},null,-1),ee(" Voltar")])),_:1})]),e[8]||(e[8]=a("div",{class:"font-semibold text-xl"},"Mesa",-1)),e[9]||(e[9]=a("small",{class:"p-error"},"Os campos marcados * sao considerados campos obrigatorios.",-1)),a("form",{onSubmit:e[2]||(e[2]=(...i)=>t(b)&&t(b)(...i))},[a("div",ue,[e[6]||(e[6]=a("label",{for:"name"},"Nome",-1)),o($,{modelValue:t(g),"onUpdate:modelValue":e[0]||(e[0]=i=>N(g)?g.value=i:null),id:"name",placeholder:"Nome",class:F({"p-invalid":t(c).name}),type:"text"},null,8,["modelValue","class"]),a("small",me,T(t(c).name),1)]),a("div",pe,[e[7]||(e[7]=a("label",{for:"capacity"},"Capacidade",-1)),o($,{modelValue:t(x),"onUpdate:modelValue":e[1]||(e[1]=i=>N(x)?x.value=i:null),id:"capacity",placeholder:"Capacidade máxima",class:F({"p-invalid":t(c).capacity}),type:"number"},null,8,["modelValue","class"]),a("small",fe,T(t(c).capacity),1)]),o(d,{label:"Submeter",class:"mr-2 mb-2",onClick:t(b),disabled:n.value},null,8,["onClick","disabled"]),n.value?(u(),K(B,{key:0,style:{width:"35px",height:"35px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"})):X("",!0)],32)])])])),o(R,{header:"Confirmação",visible:y.value,"onUpdate:visible":e[3]||(e[3]=i=>y.value=i),style:{width:"350px"},modal:!0},{footer:k(()=>[o(d,{label:"Não",icon:"pi pi-times",onClick:D,class:"p-button-text"}),o(d,{label:"Sim",icon:"pi pi-check",onClick:I,class:"p-button-text",autofocus:""})]),default:k(()=>[e[10]||(e[10]=a("div",{class:"flex align-items-center justify-content-center"},[a("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}}),a("span",null,"Tem certeza que deseja proceder?")],-1))]),_:1},8,["visible"])],64)}}};export{De as default};
