import{s as Me}from"./index-CUci0WoB.js";import{s as $e,a as Re,b as Ye}from"./index-CUoLjE6E.js";import{s as je,a as Se}from"./index-DcetoGFb.js";import{s as Ve}from"./index-Dq5bgZBB.js";import{s as Ie}from"./index-Cr0Jv0T9.js";import{s as Ee}from"./index-BASYQPsC.js";import{s as Fe}from"./index-CBbRjeXB.js";import{s as He}from"./index-C1nyz07X.js";import{u as Ne,r as o,w as Ue,o as qe,c as f,a as s,b as l,t as r,d as i,e as a,F as Be,f as p,g as b,h as me,R as Le,i as ce,j as Ae,k as Ge}from"./app-Bwv1cyBD.js";import"./index.esm-CQT6nZnd.js";import{d as Qe,h}from"./moment-CQ1ixRO1.js";import"./index-CvK3qtn_.js";import"./index-ymNQG0Kp.js";import"./index-4Uskouns.js";import"./index-CO-XQsTQ.js";import"./index-CQMZs5Ck.js";import"./index-zBF9hD0Q.js";import"./index-D4TiRL6n.js";import"./index-Do3qICCh.js";import"./index-nHdEkoLA.js";import"./index-BSQDodLt.js";import"./index-C6FYskjm.js";import"./index-D_PeK2P6.js";import"./index-DGfi-yUt.js";import"./index-pl8n-hUv.js";import"./index-CpY7MtMX.js";import"./index-D86JgmNv.js";import"./index-Cpbh94E9.js";import"./index-D4z42Bon.js";import"./index-DIbN-xYr.js";import"./index-Cnrh9zzG.js";const ze={key:0,class:"flex flex-col md:flex-row gap-12 min-h-screen items-center justify-center"},Ke={class:"w-full"},Oe={class:"flex flex-col gap-4 text-center"},We={key:1},Je={class:"w-full"},Xe={class:"mb-4"},Ze={class:"grid grid-cols-12 gap-8 mb-3"},et={class:"col-span-12 lg:col-span-6 xl:col-span-3"},tt={class:"card mb-0"},at={class:"flex justify-between mb-4"},st={class:"text-surface-900 dark:text-surface-0 font-medium text-xl"},lt={class:"col-span-12 lg:col-span-6 xl:col-span-3"},ot={class:"card mb-0"},it={class:"flex justify-between mb-4"},rt={class:"text-surface-900 dark:text-surface-0 font-medium text-xl"},dt={class:"col-span-12 lg:col-span-6 xl:col-span-3"},nt={class:"card mb-0"},ut={class:"flex justify-between mb-4"},mt={class:"text-surface-900 dark:text-surface-0 font-medium text-xl"},ct={class:"col-span-12 lg:col-span-6 xl:col-span-3"},pt={class:"card mb-0"},vt={class:"flex justify-between mb-4"},ft={class:"flex justify-between mb-4"},bt={class:"text-surface-900 dark:text-surface-0 font-medium text-xl"},yt={class:"text-surface-900 dark:text-surface-0 font-medium text-xl"},gt={class:"col-span-12 lg:col-span-6 xl:col-span-3"},ht={class:"card mb-0"},_t={class:"flex justify-between mb-4"},xt={class:"flex justify-between mb-4"},wt={class:"text-surface-900 dark:text-surface-0 font-medium text-xl"},kt={class:"text-surface-900 dark:text-surface-0 font-medium text-xl"},Dt={class:"col-span-12 lg:col-span-6 xl:col-span-3"},Tt={class:"card mb-0"},Pt={class:"flex justify-between mb-4"},Ct={class:"flex justify-between mb-4"},Mt={class:"text-surface-900 dark:text-surface-0 font-medium text-xl"},$t={class:"text-surface-900 dark:text-surface-0 font-medium text-xl"},Rt=["onClick"],Yt=["onClick"],jt=["src"],St={key:1},Vt=["src"],pa={__name:"Dashboard",setup(It){const k=Ge(),v=Ne();o(null);const D=o(!0),T=o(!1),u=o(""),P=o(!1),C=o(!1),M=o(!1),B=o([]),L=o([]),A=o([]),G=o(0),Q=o(0),z=o(0),$=o(!1);let K=o(null);const pe=o(""),O=o(null),R=o(1),Y=o(1),j=o(1),ve=o(1),m=o(100);o(0);const S=o(!1);o(null),o([]),o(0),o(0),o([]),o(!1),o(1);const V=o(!1),_=o(null);o(0);const W=o(0),J=o(0),X=o(0),Z=o(0),ee=o(0),te=o(0),ae=o(0),se=o(0),le=o(0),oe=o(0);o("Aberto"),o([]);const I=o(!1),E=o(null);o(!1);const ie=o(!1),re=n=>{E.value=n,I.value=!0},F=o(!1);function fe(){T.value=!0,H(),N(),q(),U(),T.value=!1}function be(){k&&k.back()}const de=()=>{S.value=!1};function ne(){V.value=!0,k.back()}const H=async(n=1)=>{u.value&&(u.value=u.value.toLocaleDateString("en-CA")),axios.get("/api/cashregisters/dailydashboard",{params:{date:u.value}}).then(e=>{W.value=e.data.cash_register,J.value=e.data.total_sales,X.value=e.data.total_orders,ee.value=e.data.total_tables,se.value=e.data.total_quick_sell,Z.value=e.data.average_ticket,te.value=e.data.total_quick_sell_amount,ae.value=e.data.total_tables_amount,le.value=e.data.total_payments,oe.value=e.data.total_payments_amount,D.value=!1}).catch(e=>{D.value=!1,v.add({severity:"error",summary:"Erro",detail:`${e.response.data.message}`,life:3e3}),be()})},N=async(n=1)=>{axios.get(`/api/daily/paymentreport?page=${n}`,{params:{date:u.value}}).then(e=>{A.value=e.data,z.value=e.data.total,P.value=!1}).catch(e=>{P.value=!1,v.add({severity:"error",summary:"Erro",detail:`${e.response.data.message}`,life:3e3})})},U=async(n=1)=>{axios.get(`/api/daily/tablesellreport?page=${n}`,{params:{date:u.value}}).then(e=>{L.value=e.data,Q.value=e.data.total,M.value=!1}).catch(e=>{M.value=!1,v.add({severity:"error",summary:"Erro",detail:`${e.response.data.message}`,life:3e3})})},q=async(n=1)=>{axios.get(`/api/daily/quicksellreport?page=${n}`,{params:{date:u.value}}).then(e=>{B.value=e.data,G.value=e.data.total,C.value=!1}).catch(e=>{C.value=!1,v.add({severity:"error",summary:"Erro",detail:`${e.response.data.message}`,life:3e3})})},ye=()=>{$.value=!0,axios.delete(`/api/tables/${K.value}`).then(()=>{O.value.data=O.value.data.filter(n=>n.id!==K.value),de(),v.add({severity:"success",summary:"Sucesso",detail:"Sucesso ao apagar",life:3e3})}).catch(n=>{v.add({severity:"error",summary:"Erro",detail:`${n}`,life:3e3}),$.value=!1}).finally(()=>{$.value=!1})},ge=n=>{R.value=n.page+1,m.value=n.rows,q(R.value)},he=n=>{Y.value=n.page+1,m.value=n.rows,U(Y.value)},_e=n=>{j.value=n.page+1,m.value=n.rows,N(j.value)},xe=Qe(()=>{H(ve.value)},300);function ue(){const n=document.querySelector("iframe");n&&(n.contentWindow.focus(),n.contentWindow.print())}return Ue(pe,xe),qe(()=>{H(),N(),q(),U()}),(n,e)=>{const we=He,ke=Fe,c=Ee,x=Re,De=Ye,d=Ie,y=Ve,w=je,Te=Se,Pe=$e,g=Me;return p(),f(Be,null,[D.value?(p(),f("div",ze,[s("div",Ke,[s("div",Oe,[l(we,{style:{width:"50px",height:"50px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"}),e[7]||(e[7]=s("p",null,"Por Favor Aguarde...",-1))])])])):(p(),f("div",We,[s("div",Je,[s("div",Xe,[e[8]||(e[8]=s("label",{for:"date",class:"block text-sm font-medium text-gray-700"},"Selecione a Data:",-1)),l(ke,{modelValue:u.value,"onUpdate:modelValue":e[0]||(e[0]=t=>u.value=t),dateFormat:"yy-mm-dd",showIcon:"",class:"mt-1",placeholder:"Escolha uma data"},null,8,["modelValue"])]),l(c,{label:"Gerar Relatório",icon:"pi pi-chart-line",class:"p-button-primary mb-2",onClick:fe,disabled:T.value},null,8,["disabled"])]),s("div",Ze,[s("div",et,[s("div",tt,[s("div",at,[s("div",null,[e[9]||(e[9]=s("span",{class:"block text-muted-color font-medium mb-4"},"Total de Vendas",-1)),s("div",st,r(J.value)+" MT",1)]),e[10]||(e[10]=s("div",{class:"flex items-center justify-center bg-blue-100 dark:bg-blue-400/10 rounded-border",style:{width:"2.5rem",height:"2.5rem"}},[s("i",{class:"pi pi-shopping-cart text-blue-500 !text-xl"})],-1))])])]),s("div",lt,[s("div",ot,[s("div",it,[s("div",null,[e[11]||(e[11]=s("span",{class:"block text-muted-color font-medium mb-4"},"Pedidos",-1)),s("div",rt,r(X.value),1)]),e[12]||(e[12]=s("div",{class:"flex items-center justify-center bg-blue-100 dark:bg-blue-400/10 rounded-border",style:{width:"2.5rem",height:"2.5rem"}},[s("i",{class:"pi pi-shopping-cart text-blue-500 !text-xl"})],-1))])])]),s("div",dt,[s("div",nt,[s("div",ut,[s("div",null,[e[13]||(e[13]=s("span",{class:"block text-muted-color font-medium mb-4"},"Ticket Médio",-1)),s("div",mt,r(Z.value)+" MT",1)]),e[14]||(e[14]=s("div",{class:"flex items-center justify-center bg-blue-100 dark:bg-blue-400/10 rounded-border",style:{width:"2.5rem",height:"2.5rem"}},[s("i",{class:"pi pi-shopping-cart text-blue-500 !text-xl"})],-1))])])]),s("div",ct,[s("div",pt,[s("div",vt,[s("div",null,[e[15]||(e[15]=s("span",{class:"block text-muted-color font-medium mb-4"},"Total Mesas Abertas",-1)),s("div",ft,[s("span",bt,r(ee.value),1),s("span",yt,r(ae.value)+" MT",1)])]),e[16]||(e[16]=s("div",{class:"flex items-center justify-center bg-blue-100 dark:bg-blue-400/10 rounded-border",style:{width:"2.5rem",height:"2.5rem"}},[s("i",{class:"pi pi-shopping-cart text-blue-500 !text-xl"})],-1))])])]),s("div",gt,[s("div",ht,[s("div",_t,[s("div",null,[e[17]||(e[17]=s("span",{class:"block text-muted-color font-medium mb-4"},"Total Pedidos/Venda Rápido",-1)),s("div",xt,[s("span",wt,r(se.value),1),s("span",kt,r(te.value)+" MT",1)])]),e[18]||(e[18]=s("div",{class:"flex items-center justify-center bg-blue-100 dark:bg-blue-400/10 rounded-border",style:{width:"2.5rem",height:"2.5rem"}},[s("i",{class:"pi pi-shopping-cart text-blue-500 !text-xl"})],-1))])])]),s("div",Dt,[s("div",Tt,[s("div",Pt,[s("div",null,[e[20]||(e[20]=s("span",{class:"block text-muted-color font-medium mb-4"},"Total Pagamentos",-1)),s("div",Ct,[s("span",Mt,r(le.value),1),e[19]||(e[19]=i("- - ")),s("span",$t,r(oe.value)+" MT",1)])]),e[21]||(e[21]=s("div",{class:"flex items-center justify-center bg-blue-100 dark:bg-blue-400/10 rounded-border",style:{width:"2.5rem",height:"2.5rem"}},[s("i",{class:"pi pi-shopping-cart text-blue-500 !text-xl"})],-1))])])])]),l(Pe,{value:"0"},{default:a(()=>[l(De,null,{default:a(()=>[l(x,{value:"0"},{default:a(()=>e[22]||(e[22]=[i("Vendas Rápidas")])),_:1}),l(x,{value:"1"},{default:a(()=>e[23]||(e[23]=[i("Vendas em Mesas")])),_:1}),l(x,{value:"2"},{default:a(()=>e[24]||(e[24]=[i("Pagamentos Efetuados")])),_:1}),l(x,{value:"3"},{default:a(()=>e[25]||(e[25]=[i("Caixas")])),_:1})]),_:1}),l(Te,null,{default:a(()=>[l(w,{value:"0"},{default:a(()=>[l(y,{value:B.value.data,paginator:!0,rows:m.value,totalRecords:G.value,dataKey:"id",lazy:!0,rowHover:!0,loading:C.value,first:(R.value-1)*m.value,onPage:ge,showGridlines:""},{header:a(()=>e[26]||(e[26]=[])),empty:a(()=>e[27]||(e[27]=[i("Nenhuma registro encontrado. ")])),loading:a(()=>e[28]||(e[28]=[i(" Carregando, por favor espere. ")])),default:a(()=>[l(d,{header:"ID",style:{"min-width":"12rem"}},{body:a(({data:t})=>[i(" #"+r(t.id),1)]),_:1}),l(d,{header:"Pedido",style:{"min-width":"12rem"}},{body:a(({data:t})=>e[29]||(e[29]=[i(" Pedido Rápido ")])),_:1}),l(d,{header:"Garçom",style:{"min-width":"12rem"}},{body:a(({data:t})=>[i(r(t.user.name),1)]),_:1}),l(d,{header:"Estado",style:{"min-width":"12rem"}},{body:a(({data:t})=>[i(r(t.status.name),1)]),_:1}),l(d,{header:"Itens",style:{"min-width":"12rem"}},{body:a(({data:t})=>[i(r(t.itens.length),1)]),_:1}),l(d,{header:"Valor",style:{"min-width":"12rem"}},{body:a(({data:t})=>[i(r(t.total)+" MT ",1)]),_:1}),l(d,{header:"Data",dataType:"date",style:{"min-width":"10rem"}},{body:a(({data:t})=>[i(r(b(h)(t.created_at).format("DD-MM-YYYY H:mm")),1)]),_:1}),l(d,{header:"Ações",style:{"min-width":"12rem"}},{body:a(({data:t})=>[s("a",{class:"m-3",href:"#",onClick:me(Ce=>re(t),["prevent"])},e[30]||(e[30]=[s("i",{class:"pi pi-eye"},null,-1)]),8,Rt)]),_:1})]),_:1},8,["value","rows","totalRecords","loading","first"])]),_:1}),l(w,{value:"1"},{default:a(()=>[l(y,{value:L.value.data,paginator:!0,rows:m.value,totalRecords:Q.value,dataKey:"id",lazy:!0,rowHover:!0,loading:M.value,first:(Y.value-1)*m.value,onPage:he,showGridlines:""},{header:a(()=>e[31]||(e[31]=[])),empty:a(()=>e[32]||(e[32]=[i("Nenhuma registro encontrado. ")])),loading:a(()=>e[33]||(e[33]=[i(" Carregando, por favor espere. ")])),default:a(()=>[l(d,{header:"ID",style:{"min-width":"12rem"}},{body:a(({data:t})=>[i(" #"+r(t.id),1)]),_:1}),l(d,{header:"Pedido",style:{"min-width":"12rem"}},{body:a(({data:t})=>[i(r(t.table.name),1)]),_:1}),l(d,{header:"Garçom",style:{"min-width":"12rem"}},{body:a(({data:t})=>[i(r(t.user.name),1)]),_:1}),l(d,{header:"Estado",style:{"min-width":"12rem"}},{body:a(({data:t})=>[i(r(t.status.name),1)]),_:1}),l(d,{header:"Itens",style:{"min-width":"12rem"}},{body:a(({data:t})=>[i(r(t.itens.length),1)]),_:1}),l(d,{header:"Valor",style:{"min-width":"12rem"}},{body:a(({data:t})=>[i(r(t.total)+" MT ",1)]),_:1}),l(d,{header:"Data",dataType:"date",style:{"min-width":"10rem"}},{body:a(({data:t})=>[i(r(b(h)(t.created_at).format("DD-MM-YYYY H:mm")),1)]),_:1}),l(d,{header:"Ações",style:{"min-width":"12rem"}},{body:a(({data:t})=>[s("a",{class:"m-3",href:"#",onClick:me(Ce=>re(t),["prevent"])},e[34]||(e[34]=[s("i",{class:"pi pi-eye"},null,-1)]),8,Yt)]),_:1})]),_:1},8,["value","rows","totalRecords","loading","first"])]),_:1}),l(w,{value:"2"},{default:a(()=>[l(y,{value:A.value.data,paginator:!0,rows:m.value,totalRecords:z.value,dataKey:"id",lazy:!0,rowHover:!0,loading:P.value,first:(j.value-1)*m.value,onPage:_e,showGridlines:""},{header:a(()=>e[35]||(e[35]=[])),empty:a(()=>e[36]||(e[36]=[i("Nenhuma registro encontrado. ")])),loading:a(()=>e[37]||(e[37]=[i(" Carregando, por favor espere. ")])),default:a(()=>[l(d,{header:"ID",style:{"min-width":"12rem"}},{body:a(({data:t})=>[i(r(t.id),1)]),_:1}),l(d,{header:"Venda",style:{"min-width":"12rem"}},{body:a(({data:t})=>[i(" #"+r(t.order_id),1)]),_:1}),l(d,{header:"Pedido",style:{"min-width":"12rem"}},{body:a(({data:t})=>[i(r(t.order.table_id==null?"Pedido Rápido":t.order.table.name),1)]),_:1}),l(d,{header:"Metodo de Pagamento",style:{"min-width":"12rem"}},{body:a(({data:t})=>[i(r(t.method.name),1)]),_:1}),l(d,{header:"Valor",style:{"min-width":"12rem"}},{body:a(({data:t})=>[i(r(t.amount)+" MT ",1)]),_:1}),l(d,{header:"Data",dataType:"date",style:{"min-width":"10rem"}},{body:a(({data:t})=>[i(r(b(h)(t.created_at).format("DD-MM-YYYY H:mm")),1)]),_:1})]),_:1},8,["value","rows","totalRecords","loading","first"])]),_:1}),l(w,{value:"3"},{default:a(()=>[l(y,{value:W.value,paginator:!0,rows:15,dataKey:"id",lazy:!1,rowHover:!0,showGridlines:""},{header:a(()=>e[38]||(e[38]=[])),empty:a(()=>e[39]||(e[39]=[i("Nenhuma registro encontrado. ")])),loading:a(()=>e[40]||(e[40]=[i(" Carregando, por favor espere. ")])),default:a(()=>[l(d,{header:"ID",style:{"min-width":"12rem"}},{body:a(({data:t})=>[i(" #"+r(t.id),1)]),_:1}),l(d,{header:"Usuário",style:{"min-width":"12rem"}},{body:a(({data:t})=>[i(r(t.user.name),1)]),_:1}),l(d,{header:"Valor",style:{"min-width":"12rem"}},{body:a(({data:t})=>[i(r(t.order_itens_total)+" MT ",1)]),_:1}),l(d,{header:"Valor Final Declarado",style:{"min-width":"12rem"}},{body:a(({data:t})=>[i(r(t.closing_balance)+" MT ",1)]),_:1}),l(d,{header:"Estado",style:{"min-width":"12rem"}},{body:a(({data:t})=>[i(r(t.status.name),1)]),_:1}),l(d,{header:"Abertura",dataType:"date",style:{"min-width":"10rem"}},{body:a(({data:t})=>[i(r(b(h)(t.opened_at).format("DD-MM-YYYY H:mm")),1)]),_:1}),l(d,{header:"Fechado as",dataType:"date",style:{"min-width":"10rem"}},{body:a(({data:t})=>[i(r(t.closed_at?b(h)(t.closed_at).format("DD-MM-YYYY H:mm"):"-"),1)]),_:1}),l(d,{header:"Ações",style:{"min-width":"12rem"}},{body:a(({data:t})=>[l(b(Le),{class:"m-3",to:"/admin/cashregisters/"+t.id},{default:a(()=>e[41]||(e[41]=[s("i",{class:"pi pi-eye"},null,-1)])),_:2},1032,["to"])]),_:1})]),_:1},8,["value"])]),_:1})]),_:1})]),_:1})])),l(g,{header:"Confirmação",visible:S.value,"onUpdate:visible":e[1]||(e[1]=t=>S.value=t),style:{width:"350px"},modal:!0},{footer:a(()=>[l(c,{label:"Não",icon:"pi pi-times",onClick:de,class:"p-button-text"}),l(c,{label:"Sim",icon:"pi pi-check",onClick:ye,class:"p-button-text",autofocus:""})]),default:a(()=>[e[42]||(e[42]=s("div",{class:"flex align-items-center justify-content-center"},[s("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}}),s("span",null,"Tem certeza que deseja proceder?")],-1))]),_:1},8,["visible"]),l(g,{header:"Open File",visible:F.value,"onUpdate:visible":e[3]||(e[3]=t=>F.value=t),style:{width:"30vw"}},{default:a(()=>[e[43]||(e[43]=s("p",null,"Here you can manage your files or perform specific actions.",-1)),l(c,{label:"Close",onClick:e[2]||(e[2]=t=>F.value=!1)})]),_:1},8,["visible"]),l(g,{visible:V.value,"onUpdate:visible":e[4]||(e[4]=t=>V.value=t),header:"Recibo",modal:!0,style:{width:"600px"},closable:!1},{footer:a(()=>[l(c,{label:"Imprimir",icon:"pi pi-print",onClick:ue}),l(c,{label:"Fechar",icon:"pi pi-times",class:"p-button-text",onClick:ne})]),default:a(()=>[_.value?(p(),f("iframe",{key:0,src:_.value,style:{width:"100%",height:"500px"},frameborder:"0"},null,8,jt)):ce("",!0)]),_:1},8,["visible"]),l(g,{visible:I.value,"onUpdate:visible":e[5]||(e[5]=t=>I.value=t),header:"Itens do Pedido #",modal:!0,style:{width:"50vw"}},{default:a(()=>[E.value?(p(),Ae(y,{key:0,value:E.value.itens,responsiveLayout:"scroll"},{default:a(()=>[l(d,{header:"Produto",style:{"min-width":"12rem"}},{body:a(({data:t})=>[i(r(t.product.name),1)]),_:1}),l(d,{header:"Quantidade",style:{"min-width":"8rem"}},{body:a(({data:t})=>[i(r(t.quantity),1)]),_:1}),l(d,{header:"Preço Unitário",style:{"min-width":"8rem"}},{body:a(({data:t})=>[i(r(t.price)+" MT",1)]),_:1}),l(d,{header:"Total",style:{"min-width":"8rem"}},{body:a(({data:t})=>[i(r(t.total)+" MT",1)]),_:1})]),_:1},8,["value"])):(p(),f("p",St,"Nenhum item encontrado para este pedido."))]),_:1},8,["visible"]),l(g,{visible:ie.value,"onUpdate:visible":e[6]||(e[6]=t=>ie.value=t),header:"Relatório",modal:!0,style:{width:"600px"},closable:!1},{footer:a(()=>[l(c,{label:"Imprimir",icon:"pi pi-print",onClick:ue}),l(c,{label:"Fechar",icon:"pi pi-times",class:"p-button-text",onClick:ne})]),default:a(()=>[_.value?(p(),f("iframe",{key:0,src:_.value,style:{width:"100%",height:"500px"},frameborder:"0"},null,8,Vt)):ce("",!0)]),_:1},8,["visible"])],64)}}};export{pa as default};
