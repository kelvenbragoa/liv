import{s as Pe}from"./index-BJJ-OsvO.js";import{s as Me,a as Ce,b as $e}from"./index-CT-pUluE.js";import{s as Ye,a as je}from"./index-DTctOSiO.js";import{s as Re}from"./index-DXbsMo1C.js";import{s as Se}from"./index-BcYdEjWp.js";import{s as Ve}from"./index-CcBbMGt9.js";import{s as Ee}from"./index-Bsdv0qY-.js";import{s as Ie}from"./index-Bl9jFz_u.js";import{u as Fe,r as o,w as He,o as Ne,c as y,a,b as l,t as r,d as s,F as qe,e as v,f as i,g as f,h as de,R as Be,i as Le,j as Ue,k as Ae}from"./app-L2THM-FG.js";import"./index.esm-CQT6nZnd.js";import{d as Ge,h as g}from"./moment-CQ1ixRO1.js";import"./index-Bl8Aketx.js";import"./index-X43G0N_J.js";import"./index-DwRTU3ah.js";import"./index-D6Oi8uCm.js";import"./index-DqCU2giF.js";import"./index-DgYlpl0B.js";import"./index-D9kamBqm.js";import"./index-BGMEvhLv.js";import"./index-C9lQtzdC.js";import"./index-UVibvsrW.js";import"./index-B_NzwAjo.js";import"./index-isXouK9s.js";import"./index-7v74UMdl.js";import"./index-DYROI-YF.js";import"./index-BdvzmJf3.js";import"./index-D1dfumP5.js";import"./index-DVU4zrFp.js";import"./index-DFQLH-FA.js";import"./index-eiIRJ8om.js";import"./index-4E79wXMc.js";const Qe={key:0,class:"flex flex-col md:flex-row gap-12 min-h-screen items-center justify-center"},ze={class:"w-full"},Ke={class:"flex flex-col gap-4 text-center"},Oe={key:1},We={class:"w-full"},Je={class:"mb-4"},Xe={class:"grid grid-cols-12 gap-8 mb-3"},Ze={class:"col-span-12 lg:col-span-6 xl:col-span-3"},et={class:"card mb-0"},tt={class:"flex justify-between mb-4"},at={class:"text-surface-900 dark:text-surface-0 font-medium text-xl"},st={class:"col-span-12 lg:col-span-6 xl:col-span-3"},lt={class:"card mb-0"},ot={class:"flex justify-between mb-4"},it={class:"text-surface-900 dark:text-surface-0 font-medium text-xl"},rt={class:"col-span-12 lg:col-span-6 xl:col-span-3"},dt={class:"card mb-0"},nt={class:"flex justify-between mb-4"},ut={class:"text-surface-900 dark:text-surface-0 font-medium text-xl"},mt={class:"col-span-12 lg:col-span-6 xl:col-span-3"},ct={class:"card mb-0"},pt={class:"flex justify-between mb-4"},vt={class:"flex justify-between mb-4"},ft={class:"text-surface-900 dark:text-surface-0 font-medium text-xl"},bt={class:"text-surface-900 dark:text-surface-0 font-medium text-xl"},yt={class:"col-span-12 lg:col-span-6 xl:col-span-3"},gt={class:"card mb-0"},_t={class:"flex justify-between mb-4"},ht={class:"flex justify-between mb-4"},xt={class:"text-surface-900 dark:text-surface-0 font-medium text-xl"},wt={class:"text-surface-900 dark:text-surface-0 font-medium text-xl"},kt={class:"col-span-12 lg:col-span-6 xl:col-span-3"},Dt={class:"card mb-0"},Tt={class:"flex justify-between mb-4"},Pt={class:"flex justify-between mb-4"},Mt={class:"text-surface-900 dark:text-surface-0 font-medium text-xl"},Ct={class:"text-surface-900 dark:text-surface-0 font-medium text-xl"},$t=["onClick"],Yt=["onClick"],jt=["src"],Rt={key:1},ma={__name:"Dashboard",setup(St){const w=Ae(),c=Fe();o(null);const k=o(!0),D=o(!1),u=o(""),T=o(!1),P=o(!1),M=o(!1),B=o([]),L=o([]),U=o([]),A=o(0),G=o(0),Q=o(0),C=o(!1);let z=o(null);const ne=o(""),K=o(null),$=o(1),Y=o(1),j=o(1),ue=o(1),m=o(100);o(0);const R=o(!1);o(null),o([]),o(0),o(0),o([]),o(!1),o(1);const S=o(!1),O=o(null);o(0);const W=o(0),J=o(0),X=o(0),Z=o(0),ee=o(0),te=o(0),ae=o(0),se=o(0),le=o(0),oe=o(0);o("Aberto"),o([]);const V=o(!1),E=o(null),ie=n=>{E.value=n,V.value=!0},I=o(!1);function me(){D.value=!0,F(),H(),q(),N(),D.value=!1}function ce(){w&&w.back()}const re=()=>{R.value=!1};function pe(){const n=document.querySelector("iframe");n&&(n.contentWindow.focus(),n.contentWindow.print())}function ve(){S.value=!0,w.back()}const F=async(n=1)=>{u.value&&(u.value=u.value.toLocaleDateString("en-CA")),axios.get("/api/cashregisters/dailydashboard",{params:{date:u.value}}).then(e=>{W.value=e.data.cash_register,J.value=e.data.total_sales,X.value=e.data.total_orders,ee.value=e.data.total_tables,se.value=e.data.total_quick_sell,Z.value=e.data.average_ticket,te.value=e.data.total_quick_sell_amount,ae.value=e.data.total_tables_amount,le.value=e.data.total_payments,oe.value=e.data.total_payments_amount,k.value=!1}).catch(e=>{k.value=!1,c.add({severity:"error",summary:"Erro",detail:`${e.response.data.message}`,life:3e3}),ce()})},H=async(n=1)=>{axios.get(`/api/daily/paymentreport?page=${n}`,{params:{date:u.value}}).then(e=>{U.value=e.data,Q.value=e.data.total,T.value=!1}).catch(e=>{T.value=!1,c.add({severity:"error",summary:"Erro",detail:`${e.response.data.message}`,life:3e3})})},N=async(n=1)=>{axios.get(`/api/daily/tablesellreport?page=${n}`,{params:{date:u.value}}).then(e=>{L.value=e.data,G.value=e.data.total,M.value=!1}).catch(e=>{M.value=!1,c.add({severity:"error",summary:"Erro",detail:`${e.response.data.message}`,life:3e3})})},q=async(n=1)=>{axios.get(`/api/daily/quicksellreport?page=${n}`,{params:{date:u.value}}).then(e=>{B.value=e.data,A.value=e.data.total,P.value=!1}).catch(e=>{P.value=!1,c.add({severity:"error",summary:"Erro",detail:`${e.response.data.message}`,life:3e3})})},fe=()=>{C.value=!0,axios.delete(`/api/tables/${z.value}`).then(()=>{K.value.data=K.value.data.filter(n=>n.id!==z.value),re(),c.add({severity:"success",summary:"Sucesso",detail:"Sucesso ao apagar",life:3e3})}).catch(n=>{c.add({severity:"error",summary:"Erro",detail:`${n}`,life:3e3}),C.value=!1}).finally(()=>{C.value=!1})},be=n=>{$.value=n.page+1,m.value=n.rows,q($.value)},ye=n=>{Y.value=n.page+1,m.value=n.rows,N(Y.value)},ge=n=>{j.value=n.page+1,m.value=n.rows,H(j.value)},_e=Ge(()=>{F(ue.value)},300);return He(ne,_e),Ne(()=>{F(),H(),q(),N()}),(n,e)=>{const he=Ie,xe=Ee,p=Ve,_=Ce,we=$e,d=Se,b=Re,h=Ye,ke=je,De=Me,x=Pe;return v(),y(qe,null,[k.value?(v(),y("div",Qe,[a("div",ze,[a("div",Ke,[l(he,{style:{width:"50px",height:"50px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"}),e[6]||(e[6]=a("p",null,"Por Favor Aguarde...",-1))])])])):(v(),y("div",Oe,[a("div",We,[a("div",Je,[e[7]||(e[7]=a("label",{for:"date",class:"block text-sm font-medium text-gray-700"},"Selecione a Data:",-1)),l(xe,{modelValue:u.value,"onUpdate:modelValue":e[0]||(e[0]=t=>u.value=t),dateFormat:"yy-mm-dd",showIcon:"",class:"mt-1",placeholder:"Escolha uma data"},null,8,["modelValue"])]),l(p,{label:"Gerar Relatório",icon:"pi pi-chart-line",class:"p-button-primary mb-2",onClick:me,disabled:D.value},null,8,["disabled"])]),a("div",Xe,[a("div",Ze,[a("div",et,[a("div",tt,[a("div",null,[e[8]||(e[8]=a("span",{class:"block text-muted-color font-medium mb-4"},"Total de Vendas",-1)),a("div",at,r(J.value)+" MT",1)]),e[9]||(e[9]=a("div",{class:"flex items-center justify-center bg-blue-100 dark:bg-blue-400/10 rounded-border",style:{width:"2.5rem",height:"2.5rem"}},[a("i",{class:"pi pi-shopping-cart text-blue-500 !text-xl"})],-1))])])]),a("div",st,[a("div",lt,[a("div",ot,[a("div",null,[e[10]||(e[10]=a("span",{class:"block text-muted-color font-medium mb-4"},"Pedidos",-1)),a("div",it,r(X.value),1)]),e[11]||(e[11]=a("div",{class:"flex items-center justify-center bg-blue-100 dark:bg-blue-400/10 rounded-border",style:{width:"2.5rem",height:"2.5rem"}},[a("i",{class:"pi pi-shopping-cart text-blue-500 !text-xl"})],-1))])])]),a("div",rt,[a("div",dt,[a("div",nt,[a("div",null,[e[12]||(e[12]=a("span",{class:"block text-muted-color font-medium mb-4"},"Ticket Médio",-1)),a("div",ut,r(Z.value)+" MT",1)]),e[13]||(e[13]=a("div",{class:"flex items-center justify-center bg-blue-100 dark:bg-blue-400/10 rounded-border",style:{width:"2.5rem",height:"2.5rem"}},[a("i",{class:"pi pi-shopping-cart text-blue-500 !text-xl"})],-1))])])]),a("div",mt,[a("div",ct,[a("div",pt,[a("div",null,[e[14]||(e[14]=a("span",{class:"block text-muted-color font-medium mb-4"},"Total Mesas Abertas",-1)),a("div",vt,[a("span",ft,r(ee.value),1),a("span",bt,r(ae.value)+" MT",1)])]),e[15]||(e[15]=a("div",{class:"flex items-center justify-center bg-blue-100 dark:bg-blue-400/10 rounded-border",style:{width:"2.5rem",height:"2.5rem"}},[a("i",{class:"pi pi-shopping-cart text-blue-500 !text-xl"})],-1))])])]),a("div",yt,[a("div",gt,[a("div",_t,[a("div",null,[e[16]||(e[16]=a("span",{class:"block text-muted-color font-medium mb-4"},"Total Pedidos/Venda Rápido",-1)),a("div",ht,[a("span",xt,r(se.value),1),a("span",wt,r(te.value)+" MT",1)])]),e[17]||(e[17]=a("div",{class:"flex items-center justify-center bg-blue-100 dark:bg-blue-400/10 rounded-border",style:{width:"2.5rem",height:"2.5rem"}},[a("i",{class:"pi pi-shopping-cart text-blue-500 !text-xl"})],-1))])])]),a("div",kt,[a("div",Dt,[a("div",Tt,[a("div",null,[e[18]||(e[18]=a("span",{class:"block text-muted-color font-medium mb-4"},"Total Pagamentos",-1)),a("div",Pt,[a("span",Mt,r(le.value),1),a("span",Ct,r(oe.value)+" MT",1)])]),e[19]||(e[19]=a("div",{class:"flex items-center justify-center bg-blue-100 dark:bg-blue-400/10 rounded-border",style:{width:"2.5rem",height:"2.5rem"}},[a("i",{class:"pi pi-shopping-cart text-blue-500 !text-xl"})],-1))])])])]),l(De,{value:"0"},{default:s(()=>[l(we,null,{default:s(()=>[l(_,{value:"0"},{default:s(()=>e[20]||(e[20]=[i("Vendas Rápidas")])),_:1}),l(_,{value:"1"},{default:s(()=>e[21]||(e[21]=[i("Vendas em Mesas")])),_:1}),l(_,{value:"2"},{default:s(()=>e[22]||(e[22]=[i("Pagamentos Efetuados")])),_:1}),l(_,{value:"3"},{default:s(()=>e[23]||(e[23]=[i("Caixas")])),_:1})]),_:1}),l(ke,null,{default:s(()=>[l(h,{value:"0"},{default:s(()=>[l(b,{value:B.value.data,paginator:!0,rows:m.value,totalRecords:A.value,dataKey:"id",lazy:!0,rowHover:!0,loading:P.value,first:($.value-1)*m.value,onPage:be,showGridlines:""},{header:s(()=>e[24]||(e[24]=[])),empty:s(()=>e[25]||(e[25]=[i("Nenhuma registro encontrado. ")])),loading:s(()=>e[26]||(e[26]=[i(" Carregando, por favor espere. ")])),default:s(()=>[l(d,{header:"ID",style:{"min-width":"12rem"}},{body:s(({data:t})=>[i(" #"+r(t.id),1)]),_:1}),l(d,{header:"Pedido",style:{"min-width":"12rem"}},{body:s(({data:t})=>e[27]||(e[27]=[i(" Pedido Rápido ")])),_:1}),l(d,{header:"Garçom",style:{"min-width":"12rem"}},{body:s(({data:t})=>[i(r(t.user.name),1)]),_:1}),l(d,{header:"Estado",style:{"min-width":"12rem"}},{body:s(({data:t})=>[i(r(t.status.name),1)]),_:1}),l(d,{header:"Itens",style:{"min-width":"12rem"}},{body:s(({data:t})=>[i(r(t.itens.length),1)]),_:1}),l(d,{header:"Valor",style:{"min-width":"12rem"}},{body:s(({data:t})=>[i(r(t.total)+" MT ",1)]),_:1}),l(d,{header:"Data",dataType:"date",style:{"min-width":"10rem"}},{body:s(({data:t})=>[i(r(f(g)(t.created_at).format("DD-MM-YYYY H:mm")),1)]),_:1}),l(d,{header:"Ações",style:{"min-width":"12rem"}},{body:s(({data:t})=>[a("a",{class:"m-3",href:"#",onClick:de(Te=>ie(t),["prevent"])},e[28]||(e[28]=[a("i",{class:"pi pi-eye"},null,-1)]),8,$t)]),_:1})]),_:1},8,["value","rows","totalRecords","loading","first"])]),_:1}),l(h,{value:"1"},{default:s(()=>[l(b,{value:L.value.data,paginator:!0,rows:m.value,totalRecords:G.value,dataKey:"id",lazy:!0,rowHover:!0,loading:M.value,first:(Y.value-1)*m.value,onPage:ye,showGridlines:""},{header:s(()=>e[29]||(e[29]=[])),empty:s(()=>e[30]||(e[30]=[i("Nenhuma registro encontrado. ")])),loading:s(()=>e[31]||(e[31]=[i(" Carregando, por favor espere. ")])),default:s(()=>[l(d,{header:"ID",style:{"min-width":"12rem"}},{body:s(({data:t})=>[i(" #"+r(t.id),1)]),_:1}),l(d,{header:"Pedido",style:{"min-width":"12rem"}},{body:s(({data:t})=>[i(r(t.table.name),1)]),_:1}),l(d,{header:"Garçom",style:{"min-width":"12rem"}},{body:s(({data:t})=>[i(r(t.user.name),1)]),_:1}),l(d,{header:"Estado",style:{"min-width":"12rem"}},{body:s(({data:t})=>[i(r(t.status.name),1)]),_:1}),l(d,{header:"Itens",style:{"min-width":"12rem"}},{body:s(({data:t})=>[i(r(t.itens.length),1)]),_:1}),l(d,{header:"Valor",style:{"min-width":"12rem"}},{body:s(({data:t})=>[i(r(t.total)+" MT ",1)]),_:1}),l(d,{header:"Data",dataType:"date",style:{"min-width":"10rem"}},{body:s(({data:t})=>[i(r(f(g)(t.created_at).format("DD-MM-YYYY H:mm")),1)]),_:1}),l(d,{header:"Ações",style:{"min-width":"12rem"}},{body:s(({data:t})=>[a("a",{class:"m-3",href:"#",onClick:de(Te=>ie(t),["prevent"])},e[32]||(e[32]=[a("i",{class:"pi pi-eye"},null,-1)]),8,Yt)]),_:1})]),_:1},8,["value","rows","totalRecords","loading","first"])]),_:1}),l(h,{value:"2"},{default:s(()=>[l(b,{value:U.value.data,paginator:!0,rows:m.value,totalRecords:Q.value,dataKey:"id",lazy:!0,rowHover:!0,loading:T.value,first:(j.value-1)*m.value,onPage:ge,showGridlines:""},{header:s(()=>e[33]||(e[33]=[])),empty:s(()=>e[34]||(e[34]=[i("Nenhuma registro encontrado. ")])),loading:s(()=>e[35]||(e[35]=[i(" Carregando, por favor espere. ")])),default:s(()=>[l(d,{header:"ID",style:{"min-width":"12rem"}},{body:s(({data:t})=>[i(r(t.id),1)]),_:1}),l(d,{header:"Venda",style:{"min-width":"12rem"}},{body:s(({data:t})=>[i(" #"+r(t.order_id),1)]),_:1}),l(d,{header:"Pedido",style:{"min-width":"12rem"}},{body:s(({data:t})=>[i(r(t.order.table_id==null?"Pedido Rápido":t.order.table.name),1)]),_:1}),l(d,{header:"Metodo de Pagamento",style:{"min-width":"12rem"}},{body:s(({data:t})=>[i(r(t.method.name),1)]),_:1}),l(d,{header:"Valor",style:{"min-width":"12rem"}},{body:s(({data:t})=>[i(r(t.amount)+" MT ",1)]),_:1}),l(d,{header:"Data",dataType:"date",style:{"min-width":"10rem"}},{body:s(({data:t})=>[i(r(f(g)(t.created_at).format("DD-MM-YYYY H:mm")),1)]),_:1})]),_:1},8,["value","rows","totalRecords","loading","first"])]),_:1}),l(h,{value:"3"},{default:s(()=>[l(b,{value:W.value,paginator:!0,rows:15,dataKey:"id",lazy:!1,rowHover:!0,showGridlines:""},{header:s(()=>e[36]||(e[36]=[])),empty:s(()=>e[37]||(e[37]=[i("Nenhuma registro encontrado. ")])),loading:s(()=>e[38]||(e[38]=[i(" Carregando, por favor espere. ")])),default:s(()=>[l(d,{header:"ID",style:{"min-width":"12rem"}},{body:s(({data:t})=>[i(" #"+r(t.id),1)]),_:1}),l(d,{header:"Usuário",style:{"min-width":"12rem"}},{body:s(({data:t})=>[i(r(t.user.name),1)]),_:1}),l(d,{header:"Valor",style:{"min-width":"12rem"}},{body:s(({data:t})=>[i(r(t.order_itens_total)+" MT ",1)]),_:1}),l(d,{header:"Valor Final Declarado",style:{"min-width":"12rem"}},{body:s(({data:t})=>[i(r(t.closing_balance)+" MT ",1)]),_:1}),l(d,{header:"Estado",style:{"min-width":"12rem"}},{body:s(({data:t})=>[i(r(t.status.name),1)]),_:1}),l(d,{header:"Abertura",dataType:"date",style:{"min-width":"10rem"}},{body:s(({data:t})=>[i(r(f(g)(t.opened_at).format("DD-MM-YYYY H:mm")),1)]),_:1}),l(d,{header:"Fechado as",dataType:"date",style:{"min-width":"10rem"}},{body:s(({data:t})=>[i(r(t.closed_at?f(g)(t.closed_at).format("DD-MM-YYYY H:mm"):"-"),1)]),_:1}),l(d,{header:"Ações",style:{"min-width":"12rem"}},{body:s(({data:t})=>[l(f(Be),{class:"m-3",to:"/admin/cashregisters/"+t.id},{default:s(()=>e[39]||(e[39]=[a("i",{class:"pi pi-eye"},null,-1)])),_:2},1032,["to"])]),_:1})]),_:1},8,["value"])]),_:1})]),_:1})]),_:1})])),l(x,{header:"Confirmação",visible:R.value,"onUpdate:visible":e[1]||(e[1]=t=>R.value=t),style:{width:"350px"},modal:!0},{footer:s(()=>[l(p,{label:"Não",icon:"pi pi-times",onClick:re,class:"p-button-text"}),l(p,{label:"Sim",icon:"pi pi-check",onClick:fe,class:"p-button-text",autofocus:""})]),default:s(()=>[e[40]||(e[40]=a("div",{class:"flex align-items-center justify-content-center"},[a("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}}),a("span",null,"Tem certeza que deseja proceder?")],-1))]),_:1},8,["visible"]),l(x,{header:"Open File",visible:I.value,"onUpdate:visible":e[3]||(e[3]=t=>I.value=t),style:{width:"30vw"}},{default:s(()=>[e[41]||(e[41]=a("p",null,"Here you can manage your files or perform specific actions.",-1)),l(p,{label:"Close",onClick:e[2]||(e[2]=t=>I.value=!1)})]),_:1},8,["visible"]),l(x,{visible:S.value,"onUpdate:visible":e[4]||(e[4]=t=>S.value=t),header:"Recibo",modal:!0,style:{width:"600px"},closable:!1},{footer:s(()=>[l(p,{label:"Imprimir",icon:"pi pi-print",onClick:pe}),l(p,{label:"Fechar",icon:"pi pi-times",class:"p-button-text",onClick:ve})]),default:s(()=>[O.value?(v(),y("iframe",{key:0,src:O.value,style:{width:"100%",height:"500px"},frameborder:"0"},null,8,jt)):Le("",!0)]),_:1},8,["visible"]),l(x,{visible:V.value,"onUpdate:visible":e[5]||(e[5]=t=>V.value=t),header:"Itens do Pedido #",modal:!0,style:{width:"50vw"}},{default:s(()=>[E.value?(v(),Ue(b,{key:0,value:E.value.itens,responsiveLayout:"scroll"},{default:s(()=>[l(d,{header:"Produto",style:{"min-width":"12rem"}},{body:s(({data:t})=>[i(r(t.product.name),1)]),_:1}),l(d,{header:"Quantidade",style:{"min-width":"8rem"}},{body:s(({data:t})=>[i(r(t.quantity),1)]),_:1}),l(d,{header:"Preço Unitário",style:{"min-width":"8rem"}},{body:s(({data:t})=>[i(r(t.price)+" MT",1)]),_:1}),l(d,{header:"Total",style:{"min-width":"8rem"}},{body:s(({data:t})=>[i(r(t.total)+" MT",1)]),_:1})]),_:1},8,["value"])):(v(),y("p",Rt,"Nenhum item encontrado para este pedido."))]),_:1},8,["visible"])],64)}}};export{ma as default};
