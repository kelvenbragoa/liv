import{s as P}from"./index-B9ptCq3a.js";import{s as M}from"./index-CFQgdBt-.js";import{s as V}from"./index-CZLO797_.js";import{u as $,r as a,w as N,d as Y,c as f,b as t,a as n,e as v,g as l,t as i,f as j,F as E,o as g,i as F}from"./app-hipc22d0.js";import{d as I,h as T}from"./moment-CQ1ixRO1.js";import"./index-BWujQi6O.js";import"./index-Bk8W5zav.js";import"./index-D6SvesWT.js";const q={key:0,class:"flex flex-col md:flex-row gap-12 min-h-screen items-center justify-center"},z={class:"w-full"},R={class:"flex flex-col gap-4 text-center"},U={key:1,class:"flex flex-col md:flex-row gap-12"},A={class:"w-full"},H={class:"card flex flex-col gap-4"},L={class:"w-full"},te={__name:"ShowPayments",setup(Q){const r=F(),u=$();a(null);const d=a(!0),c=a(!1);let x=a(null);const y=a(""),s=a(null),_=a(1);a(15),a(0);const m=a(!1);function b(){r&&r.back()}const D=()=>{m.value=!1},k=async(o=1)=>{axios.get(`/api/payments/${r.currentRoute.value.params.id}`,{params:{query:y.value}}).then(e=>{s.value=e.data,d.value=!1}).catch(e=>{d.value=!1,u.add({severity:"error",summary:`${e}`,detail:"Message Detail",life:3e3}),b()})},w=()=>{c.value=!0,axios.delete(`/api/payments/${x.value}`).then(()=>{s.value.data=s.value.data.filter(o=>o.id!==x.value),D(),u.add({severity:"success",summary:"Sucesso",detail:"Sucesso ao apagar",life:3e3})}).catch(o=>{u.add({severity:"error",summary:"Erro",detail:`${o}`,life:3e3}),c.value=!1}).finally(()=>{c.value=!1})},h=I(()=>{k(_.value)},300);return N(y,h),Y(()=>{k()}),(o,e)=>{const B=V,p=M,C=P;return g(),f(E,null,[d.value?(g(),f("div",q,[t("div",z,[t("div",R,[n(B,{style:{width:"50px",height:"50px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"}),e[1]||(e[1]=t("p",null,"Por Favor Aguarde...",-1))])])])):(g(),f("div",U,[t("div",A,[t("div",H,[t("div",L,[n(p,{label:"Voltar",class:"mr-2 mb-2",onClick:b},{default:v(()=>e[2]||(e[2]=[t("i",{class:"pi pi-angle-left"},null,-1),l(" Voltar")])),_:1})]),e[8]||(e[8]=t("div",{class:"font-semibold text-xl"},"Pagamento",-1)),t("p",null,[e[3]||(e[3]=t("strong",null,"ID:",-1)),l(" #"+i(s.value.id),1)]),t("p",null,[e[4]||(e[4]=t("strong",null,"ID da Encomenda:",-1)),l(" #"+i(s.value.order_id),1)]),t("p",null,[e[5]||(e[5]=t("strong",null,"Metodo de Pagamento:",-1)),l(" "+i(s.value.method.name),1)]),t("p",null,[e[6]||(e[6]=t("strong",null,"Valor:",-1)),l(" MZN"+i(s.value.amount),1)]),t("p",null,[e[7]||(e[7]=t("strong",null,"Data:",-1)),l(" "+i(j(T)(s.value.created_at).format("DD-MM-YYYY H:mm")),1)])])])])),n(C,{header:"Confirmação",visible:m.value,"onUpdate:visible":e[0]||(e[0]=S=>m.value=S),style:{width:"350px"},modal:!0},{footer:v(()=>[n(p,{label:"Não",icon:"pi pi-times",onClick:D,class:"p-button-text"}),n(p,{label:"Sim",icon:"pi pi-check",onClick:w,class:"p-button-text",autofocus:""})]),default:v(()=>[e[9]||(e[9]=t("div",{class:"flex align-items-center justify-content-center"},[t("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}}),t("span",null,"Tem certeza que deseja proceder?")],-1))]),_:1},8,["visible"])],64)}}};export{te as default};
