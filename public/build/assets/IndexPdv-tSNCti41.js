import{s as ne}from"./index-Blg0g7ZE.js";import{s as re}from"./index-DGN_ZkN5.js";import{s as de}from"./index-Cmf-wXl7.js";import{s as ce}from"./index-C2JZyL0-.js";import{s as ue}from"./index-CZDSej2Y.js";import{s as pe}from"./index-C4ZiZXc7.js";import{s as me}from"./index-40E2B1WF.js";import{s as ve}from"./index-BEqwVNsF.js";import{u as fe,r as l,w as he,o as ge,aQ as ye,c as v,a as t,b as s,e as i,F as Z,a5 as be,f,t as n,d,g as U,n as A,R as _e,i as xe,k as we}from"./app-VcD5h-lA.js";import{d as ke,h as H}from"./moment-CQ1ixRO1.js";import"./index-Br9Ss3Jv.js";import"./index-CP5K0wi-.js";import"./index-CmyUASEE.js";import"./index-BD-pWD7f.js";import"./index-g-uVXGlr.js";import"./index-Dg58uWRM.js";import"./index-Cb1MB9fk.js";import"./index-BhVUBHf4.js";import"./index-BsuBziKF.js";import"./index-Dm6CZD5Z.js";import"./index-CfT4ZI5Y.js";import"./index-C9X_DuRB.js";import"./index-BFZ_z6Bf.js";import"./index-DrZRt0gv.js";import"./index-CSbmkCeQ.js";import"./index-DQc4vd5U.js";import"./index-BuO52acJ.js";const Ce={key:0,class:"flex flex-col md:flex-row gap-12 min-h-screen items-center justify-center"},Re={class:"w-full"},De={class:"flex flex-col gap-4 text-center"},Me={key:1},Se={class:"mb-2"},$e={key:0},Ie={key:1},Ve={class:"grid grid-cols-12 gap-8"},Pe={class:"flex justify-between mb-4"},Te={class:"text-surface-900 dark:text-surface-0 font-medium text-xl"},Fe={class:"flex justify-between mb-4"},Ue={class:"text-primary font-medium"},Ne={class:"flex justify-between mb-4"},qe={class:"p-fluid"},Be={class:"field"},Le={class:"p-fluid"},je={class:"field"},Qe={class:"p-fluid"},Ye=["onClick"],Ee=["onClick"],Ze={class:"p-4"},Ae=["src"],_a={__name:"IndexPdv",setup(He){let N;const x=we(),p=fe();l(null);const w=l(!0);l(!1),l(null);const V=l(""),P=l(null),k=l(1),T=l(15),O=l(0);l(!1);const y=l(null),b=l(!1),_=l(!1),C=l(0),q=l(0),R=l(0),D=l(!1),B=l([]),L=l(0),j=l([]),M=l(!1),F=l(null);function W(){const o=document.querySelector("iframe");o&&(o.contentWindow.focus(),o.contentWindow.print())}function z(){M.value=!1}const G=[{label:"Venda Rápida",items:[{label:"Inicar Venda Rápida",icon:"pi pi-fw pi-shopping-cart",command:()=>{x.push("/waiter/pdv/quicksell")}},{label:"Lista",icon:"pi pi-fw pi-list",command:()=>{D.value=!0}}]},{label:"Caixa",items:[{label:"Abertura de caixa",icon:"pi pi-fw pi-unlock",command:()=>{b.value=!0}},{label:"Fecho de caixa",icon:"pi pi-fw pi-lock",command:()=>{_.value=!0}},{label:"Relatório de caixa",icon:"pi pi-fw pi-check",command:()=>{x.push("/waiter/cashregisters/dashboard")}}]}];function J(){x&&x.back()}const K=()=>{b.value=!1},X=()=>{_.value=!1},ee=()=>{D.value=!1};function Q(o){switch(o){case 1:return"red";case 2:return"red";case 3:return"warn";case 4:return"danger";case 5:return"info";case 6:return"info"}}function ae(o){switch(o){case 1:return"success";case 2:return"danger";case 3:return"warn";case 4:return"danger";case 5:return"info";case 6:return"info"}}function se(o){axios.post(`/api/getquickreceipt/${o}`,{},{responseType:"blob"}).then(e=>{const m=new Blob([e.data],{type:"application/pdf"});F.value=URL.createObjectURL(m),M.value=!0,openPrintReceipt.value=!1,p.add({severity:"success",summary:"Successo",detail:"Consumo Impresso com sucesso!",life:3e3})}).catch(async e=>{var h,$;console.log(e),w.value=!1;let m="Ocorreu um erro inesperado.";if(e.response&&e.response.data instanceof Blob)try{const g=await e.response.data.text(),c=JSON.parse(g);m=c.message||c.error||m}catch(g){console.error("Erro ao processar o blob:",g)}else($=(h=e.response)==null?void 0:h.data)!=null&&$.message&&(m=e.response.data.message);p.add({severity:"error",summary:"Erro",detail:m,life:3e3}),e.response.data.errors&&setErrors(e.response.data.errors)})}const S=async(o=1)=>{axios.get(`/api/pdv?page=${o}`,{params:{query:V.value}}).then(e=>{P.value=e.data.tables,O.value=P.value.total,y.value=e.data.cash_register,R.value=e.data.totalcash,w.value=!1}).catch(e=>{w.value=!1,p.add({severity:"error",summary:`${e}`,detail:"Message Detail",life:3e3}),J()})};function te(){axios.post("/api/cashregisters/open",{opening_balance:C.value}).then(o=>{b.value=!1,C.value=null,y.value=o.data,p.add({severity:"success",summary:"Sucesso",detail:"Caixa aberto com sucesso!",life:3e3})}).catch(o=>{p.add({severity:"error",summary:"Erro",detail:`Falha ao abrir o caixa. ${o.response.data.message}`,life:3e3}),console.error(o)})}const Y=async(o=1)=>{axios.get(`/api/pdvquicksellslist?page=${o}`,{params:{query:V.value}}).then(e=>{B.value=e.data.quicksells,L.value=e.data.quicksells.total}).catch(e=>{p.add({severity:"error",summary:`${e}`,detail:"Message Detail",life:3e3})})};function oe(){axios.post("/api/cashregisters/close",{closing_balance:q.value}).then(o=>{_.value=!1,q.value=null,y.value=null,p.add({severity:"success",summary:"Sucesso",detail:"Caixa Fechado com sucesso!",life:3e3})}).catch(o=>{p.add({severity:"error",summary:"Erro",detail:`Falha ao fechar o caixa. ${o.response.data.message}`,life:3e3}),console.error(o)})}const ie=o=>{k.value=o.page+1,T.value=o.rows,S(k.value)},le=ke(()=>{S(k.value)},300);return he(V,le),ge(()=>{S(),Y(),N=setInterval(()=>{S()},3e4),Y()}),ye(()=>{clearInterval(N)}),(o,e)=>{const m=ve,h=me,$=pe,g=ue,c=ce,I=de,r=re,E=ne;return f(),v(Z,null,[w.value?(f(),v("div",Ce,[t("div",Re,[t("div",De,[s(m,{style:{width:"50px",height:"50px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"}),e[7]||(e[7]=t("p",null,"Por Favor Aguarde...",-1))])])])):(f(),v("div",Me,[t("div",Se,[s($,{model:G},{end:i(()=>[t("span",null,"Total Venda Hoje: "+n(R.value)+" MT",1),e[8]||(e[8]=d(" | ")),y.value?(f(),v("span",$e,[s(h,{value:"Caixa Iniciado ás: "+U(H)(y.value.created_at).format("DD-MM-YYYY H:mm"),severity:"success"},null,8,["value"])])):(f(),v("span",Ie,[s(h,{value:"Caixa Não Iniciado",severity:"danger"})]))]),_:1})]),t("div",Ve,[(f(!0),v(Z,null,be(P.value.data,(a,u)=>(f(),v("div",{class:"col-span-12 lg:col-span-6 xl:col-span-3",key:a.id},[s(U(_e),{to:"/waiter/pdv/"+a.id+"/categories"},{default:i(()=>[t("div",{class:A(["card mb-0",{"bg-green-100":a.table_status_id===1,"bg-red-100":a.table_status_id===2}])},[t("div",Pe,[t("div",null,[t("div",Te,n(a.name),1)]),t("div",{class:A(["flex items-center justify-center rounded-full",`bg-${Q(a.status_id)}-100`,`dark:bg-${Q(a.status_id)}-400/10`]),style:{width:"2.5rem",height:"2.5rem"}},e[9]||(e[9]=[t("i",{class:"pi pi-list text-blue-500 !text-xl","aria-label":"Carrinho de Compras"},null,-1)]),2)]),t("div",Fe,[t("span",Ue,"Capacidade: "+n(a.capacity),1),t("span",null,[s(h,{value:a.status.name,severity:ae(a.table_status_id)},null,8,["value","severity"])])]),t("div",Ne,[t("small",null,"Consumo: "+n(a.last_order!=null?a.last_order.total:0)+" MT",1),t("small",null,n(a.last_order!=null?a.last_order.user.name:""),1)])],2)]),_:2},1032,["to"])]))),128))])])),s(I,{header:"Abertura de Caixa",visible:b.value,"onUpdate:visible":e[1]||(e[1]=a=>b.value=a),style:{width:"400px"},modal:!0},{footer:i(()=>[s(c,{label:"Cancelar",icon:"pi pi-times",onClick:K,class:"p-button-text"}),s(c,{label:"Abrir Caixa",icon:"pi pi-check",onClick:te,autofocus:""})]),default:i(()=>[t("div",qe,[t("div",Be,[e[10]||(e[10]=t("label",{for:"opening_balance"},"Saldo Inicial (MZN)",-1)),s(g,{modelValue:C.value,"onUpdate:modelValue":e[0]||(e[0]=a=>C.value=a),inputId:"opening_balance",mode:"currency",currency:"MZN",locale:"pt-MZ",min:-1,placeholder:"0.00"},null,8,["modelValue"])])])]),_:1},8,["visible"]),s(I,{header:"Fechamento de Caixa",visible:_.value,"onUpdate:visible":e[3]||(e[3]=a=>_.value=a),style:{width:"400px"},modal:!0},{footer:i(()=>[s(c,{label:"Cancelar",icon:"pi pi-times",onClick:X,class:"p-button-text"}),s(c,{label:"Fechar Caixa",icon:"pi pi-check",onClick:oe,autofocus:""})]),default:i(()=>[t("div",Le,[t("div",je,[e[11]||(e[11]=t("label",{for:"closing_balance"},"Saldo Final (MZN)",-1)),s(g,{modelValue:R.value,"onUpdate:modelValue":e[2]||(e[2]=a=>R.value=a),inputId:"closing_balance",mode:"currency",currency:"MZN",locale:"pt-MZ",min:-1,placeholder:"0.00"},null,8,["modelValue"])])])]),_:1},8,["visible"]),s(I,{header:"Lista de Vendas Rápidas",visible:D.value,"onUpdate:visible":e[5]||(e[5]=a=>D.value=a),style:{width:"90vw",maxWidth:"940px"},modal:!0},{footer:i(()=>[s(c,{label:"Cancelar",icon:"pi pi-times",onClick:ee,class:"p-button-text"})]),default:i(()=>[t("div",Qe,[s(E,{expandedRows:j.value,"onUpdate:expandedRows":e[4]||(e[4]=a=>j.value=a),paginator:!0,rows:T.value,totalRecords:L.value,dataKey:"id",lazy:!0,rowHover:!0,loading:o.isLoadingQuickSell,first:(k.value-1)*T.value,onPage:ie,showGridlines:"",value:B.value.data,tableStyle:"min-width: 60rem"},{expansion:i(a=>[t("div",Ze,[t("h5",null,"Orders for #"+n(a.data.id)+" ("+n(a.data.itens.length)+")",1),s(E,{value:a.data.itens},{default:i(()=>[s(r,{field:"id",header:"Id",sortable:""}),s(r,{header:"Produto",style:{"min-width":"12rem"}},{body:i(({data:u})=>[d(n(u.product.name),1)]),_:1}),s(r,{header:"Quantidade",style:{"min-width":"12rem"}},{body:i(({data:u})=>[d(n(u.quantity),1)]),_:1}),s(r,{header:"Preço",style:{"min-width":"12rem"}},{body:i(({data:u})=>[d(n(u.price)+" MT ",1)]),_:1}),s(r,{header:"Total",style:{"min-width":"12rem"}},{body:i(({data:u})=>[d(n(u.total)+" MT ",1)]),_:1})]),_:2},1032,["value"])])]),default:i(()=>[s(r,{expander:"",style:{width:"5rem"}}),s(r,{header:"Ações",style:{"min-width":"12rem"}},{body:i(({data:a})=>[t("i",{class:"pi pi-trash m-4",onClick:u=>o.confirmDelete(a.id)},null,8,Ye),t("i",{class:"pi pi-print m-4",onClick:u=>se(a.id)},null,8,Ee)]),_:1}),s(r,{header:"ID",style:{"min-width":"12rem"}},{body:i(({data:a})=>[d(" #"+n(a.id),1)]),_:1}),s(r,{header:"Valor",style:{"min-width":"12rem"}},{body:i(({data:a})=>[d(n(a.total)+" MT ",1)]),_:1}),s(r,{header:"Pedido",style:{"min-width":"12rem"}},{body:i(({data:a})=>e[12]||(e[12]=[d(" Pedido Rápido ")])),_:1}),s(r,{header:"Garçom",style:{"min-width":"12rem"}},{body:i(({data:a})=>[d(n(a.user.name),1)]),_:1}),s(r,{header:"Estado",style:{"min-width":"12rem"}},{body:i(({data:a})=>[d(n(a.status.name),1)]),_:1}),s(r,{header:"Itens",style:{"min-width":"12rem"}},{body:i(({data:a})=>[d(n(a.itens.length),1)]),_:1}),s(r,{header:"Data",dataType:"date",style:{"min-width":"10rem"}},{body:i(({data:a})=>[d(n(U(H)(a.created_at).format("DD-MM-YYYY H:mm")),1)]),_:1})]),_:1},8,["expandedRows","rows","totalRecords","loading","first","value"])])]),_:1},8,["visible"]),s(I,{visible:M.value,"onUpdate:visible":e[6]||(e[6]=a=>M.value=a),header:"Recibo",modal:!0,style:{width:"600px"},closable:!1},{footer:i(()=>[s(c,{label:"Imprimir",icon:"pi pi-print",onClick:W}),s(c,{label:"Fechar",icon:"pi pi-times",class:"p-button-text",onClick:z})]),default:i(()=>[F.value?(f(),v("iframe",{key:0,src:F.value,style:{width:"100%",height:"500px"},frameborder:"0"},null,8,Ae)):xe("",!0)]),_:1},8,["visible"])],64)}}};export{_a as default};
