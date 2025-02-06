import{s as ve}from"./index-CgIXUiNM.js";import{s as fe}from"./index-UIVhOxZ_.js";import{s as be}from"./index-BfnY2mx3.js";import{s as ye,a as _e,b as ge}from"./index-ctPaDQX2.js";import{s as he,a as xe}from"./index-DxTfyHKA.js";import{s as we}from"./index-D65E1ytH.js";import{s as ke}from"./index-Bpweer7u.js";import{u as Ce,r as l,w as Te,b as $e,c as d,d as t,a as n,g as h,t as i,l as A,F as f,a1 as y,e as u,o,k as S,M as De,aV as Me,i as Pe}from"./app-CfCpLOBj.js";import{d as Re}from"./moment-CQ1ixRO1.js";import"./index-DhAzfIqN.js";import"./index-C_NLFgCt.js";import"./index-p3BNxDU2.js";import"./index-DcQK9fXi.js";import"./index-CtNzv2u3.js";import"./index-Bc_zR1Fw.js";import"./index-DvVl1tKl.js";import"./index-D7Jict8j.js";import"./index-DGaq71BE.js";import"./index-Bgnrt-Dp.js";import"./index-BZ923KtI.js";import"./index-Cy_oHJS4.js";const je={key:0,class:"flex flex-col md:flex-row gap-12 min-h-screen items-center justify-center"},Ee={class:"w-full"},Ue={class:"flex flex-col gap-4 text-center"},Se={key:1},Be={class:"grid grid-cols-12 gap-4 h-screen"},qe={class:"col-span-12 lg:col-span-3 h-full"},Le={class:"card flex flex-col gap-4 h-full"},Fe={key:0},Ne=["onClick"],Ae={class:"flex justify-between mb-4"},Ve={key:0,class:"mt-4"},Ie=["disabled"],Ze={key:0,class:"flex flex-col gap-4 text-center"},Oe={class:"col-span-12 lg:col-span-9"},ze={class:"mb-2"},We={class:"card flex flex-col gap-4"},He={key:0},Qe={class:"grid grid-cols-12 gap-8"},Xe={class:"card mb-0 bg-gray-100"},Ge={class:"mb-4"},Je=["src"],Ke={class:"flex justify-between mb-4"},Ye={class:"text-surface-900 dark:text-surface-0 font-medium text-xl"},et={class:"flex justify-between mb-4"},tt={class:"text-primary font-medium"},at=["onClick"],st={key:1},lt={class:"p-4"},ot={class:"space-y-2"},nt=["onClick"],it={class:"mt-4 text-lg font-semibold"},dt={class:"text-blue-500"},rt={class:"mt-4 flex justify-end"},ut={class:"p-4"},ct={class:"mt-4 flex justify-end space-x-2"},pt={class:"p-4"},mt={class:"space-y-2"},vt={class:"mt-4 text-lg font-semibold"},ft={class:"text-blue-500"},bt={class:"mt-4 flex justify-end"},yt={class:"p-4"},_t={class:"space-y-2"},gt={class:"mt-4 text-lg font-semibold"},ht={class:"text-blue-500"},xt={class:"mt-4 flex justify-end"},wt="13579",Wt={__name:"IndexPdvCategories",setup(kt){const b=Pe(),c=Ce();l(null);const M=l(!0),B=l(!1);let V=l(null);const I=l(""),P=l(null),Y=l(1);l(15),l(0);const q=l(!1),R=l(null),x=l([]),w=l(0),k=l(0),m=l(!1),T=l([]),ee=l(!1),$=l(1),L=l([]),Z=l(null),F=l(null),N=l(!1),j=l(!1),te=l(!1),E=l(!1),U=l(!1),D=l(!1),ae=[{label:"Consumo",items:[{label:"Ver consumo",icon:"pi pi-fw pi-folder-open",command:()=>{j.value=!0}},{label:"Imprimir Consumo",icon:"pi pi-fw pi-print",command:()=>{ue()}}]},{label:"Conta",items:[{label:"Fechamento da Conta",icon:"pi pi-fw pi-lock",command:()=>{E.value=!0}},{label:"Finalizar da Conta",icon:"pi pi-fw pi-check",command:()=>{U.value=!0}}]}];function O(){b&&b.back()}const se=s=>{Z.value=s,D.value=!0},le=()=>{if(F.value!==wt){c.add({severity:"error",summary:"Erro",detail:"Código de confirmação inválido",life:3e3});return}else axios.post(`/api/orderitem/${Z.value}`).then(s=>{P.value=s.data,k.value=s.data.total_consumed,R.value=s.data.categories,T.value=s.data.order_items,L.value=s.data.payment_methods,$.value=1,D.value=!1,c.add({severity:"success",summary:"Sucesso",detail:"Sucesso ao apagar",life:3e3})}).catch(s=>{c.add({severity:"error",summary:"Erro",detail:`${s}`,life:3e3})}).finally(()=>{})},z=()=>{q.value=!1};function oe(){const s={products:x.value.map(e=>({id:e.id,name:e.name,quantity:e.quantity,total:e.price*e.quantity})),total:w.value,table_id:b.currentRoute.value.params.id};m.value=!0,axios.post("/api/pdv",s,{headers:{"Content-Type":"multipart/form-data"},responseType:"blob"}).then(e=>{b.back();const r=window.URL.createObjectURL(new Blob([e.data])),v=document.createElement("a");v.href=r,v.setAttribute("download","recibo.pdf"),document.body.appendChild(v),v.click(),ee.value=!1,c.add({severity:"success",summary:"Successo",detail:"Produto encomedado sucesso!",life:3e3})}).catch(e=>{m.value=!1,c.add({severity:"error",summary:"Erro}",detail:`${e}`,life:3e3}),e.response.data.errors&&setErrors(e.response.data.errors)}).finally(()=>{m.value=!1})}function ne(){m.value=!0,axios.get(`/api/pdv/closeaccount/${b.currentRoute.value.params.id}`,{responseType:"blob"}).then(s=>{const e=window.URL.createObjectURL(new Blob([s.data])),r=document.createElement("a");r.href=e,r.setAttribute("download","recibo.pdf"),document.body.appendChild(r),r.click(),E.value=!1,c.add({severity:"success",summary:"Successo",detail:"Encomenda fechada sucesso!",life:3e3})}).catch(s=>{m.value=!1,c.add({severity:"error",summary:"Erro}",detail:`${s}`,life:3e3}),s.response.data.errors&&setErrors(s.response.data.errors)}).finally(()=>{m.value=!1})}function ie(){const s={payment_method_id:$.value,table_id:b.currentRoute.value.params.id};m.value=!0,axios.post("/api/payaccount",s,{headers:{"Content-Type":"multipart/form-data"},responseType:"blob"}).then(e=>{b.back();const r=window.URL.createObjectURL(new Blob([e.data])),v=document.createElement("a");v.href=r,v.setAttribute("download","recibo.pdf"),document.body.appendChild(v),v.click(),U.value=!1,c.add({severity:"success",summary:"Successo",detail:"Encomenda fechada sucesso!",life:3e3})}).catch(e=>{m.value=!1,c.add({severity:"error",summary:"Erro}",detail:`${e}`,life:3e3}),e.response.data.errors&&setErrors(e.response.data.errors)}).finally(()=>{m.value=!1})}function de(s){const e=x.value.find(r=>r.id===s.id);e?e.quantity+=1:x.value.push({...s,quantity:1}),W()}function re(s){x.value.splice(s,1),W()}function W(){w.value=x.value.reduce((s,e)=>s+e.price*e.quantity,0)}function ue(){axios.post(`/api/getreceipt/${b.currentRoute.value.params.id}`,{},{responseType:"blob"}).then(s=>{const e=window.URL.createObjectURL(new Blob([s.data])),r=document.createElement("a");r.href=e,r.setAttribute("download","consumo.pdf"),document.body.appendChild(r),r.click(),te.value=!1,c.add({severity:"success",summary:"Successo",detail:"Consumo Impresso com sucesso!",life:3e3})}).catch(s=>{M.value=!1,c.add({severity:"error",summary:`${s}`,detail:"Message Detail",life:3e3}),O()})}const H=async(s=1)=>{axios.get(`/api/pdv/${b.currentRoute.value.params.id}`,{params:{query:I.value}}).then(e=>{P.value=e.data,k.value=e.data.total_consumed,R.value=e.data.categories,T.value=e.data.order_items,L.value=e.data.payment_methods,$.value=1,M.value=!1}).catch(e=>{M.value=!1,c.add({severity:"error",summary:`${e}`,detail:"Message Detail",life:3e3}),O()})},ce=()=>{B.value=!0,axios.delete(`/api/tables/${V.value}`).then(()=>{P.value.data=P.value.data.filter(s=>s.id!==V.value),z(),c.add({severity:"success",summary:"Sucesso",detail:"Sucesso ao apagar",life:3e3})}).catch(s=>{c.add({severity:"error",summary:"Erro",detail:`${s}`,life:3e3}),B.value=!1}).finally(()=>{B.value=!1})},pe=Re(()=>{H(Y.value)},300);return Te(I,pe),$e(()=>{H()}),(s,e)=>{const r=ke,v=we,Q=_e,X=ge,G=he,J=xe,K=ye,_=be,C=fe,me=ve;return o(),d(f,null,[M.value?(o(),d("div",je,[t("div",Ee,[t("div",Ue,[n(r,{style:{width:"50px",height:"50px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"}),e[13]||(e[13]=t("p",null,"Por Favor Aguarde...",-1))])])])):(o(),d("div",Se,[t("div",Be,[t("div",qe,[t("div",Le,[t("h2",null,[e[14]||(e[14]=h("Resumo da venda ")),w.value>0?(o(),d("div",Fe,[t("strong",null,"Total: "+i(w.value)+" MT",1)])):A("",!0)]),(o(!0),d(f,null,y(x.value,(a,p)=>(o(),d("div",{key:p,class:"card bg-gray-100 p-4"},[t("div",null,[t("strong",null,i(a.name),1),t("button",{onClick:g=>re(p),class:"rounded-full bg-red-100",style:{width:"2.5rem",height:"2.5rem"}},"X",8,Ne)]),t("div",Ae,[t("div",null,i(a.quantity)+" x "+i(a.price)+" MT",1),t("div",null,i(a.price*a.quantity)+" MT",1)])]))),128)),w.value>0?(o(),d("div",Ve,[t("strong",null,"Total: "+i(w.value)+" MT",1),t("button",{disabled:m.value,onClick:oe,class:"bg-blue-500 text-white px-4 py-2 rounded-full mt-1",style:{width:"100%"}},e[15]||(e[15]=[h("Adicionar a conta"),t("i",{class:"pi pi-print"},null,-1)]),8,Ie),m.value?(o(),d("div",Ze,[n(r,{style:{width:"50px",height:"50px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"})])):A("",!0)])):A("",!0)])]),t("div",Oe,[t("div",ze,[n(v,{model:ae},{end:u(()=>[t("p",null,"Total Consumo: "+i(k.value)+" MT",1)]),_:1})]),t("div",We,[n(K,{value:1},{default:u(()=>[n(X,{class:"flex overflow-x-auto space-x-4 pb-2"},{default:u(()=>[(o(!0),d(f,null,y(R.value,a=>(o(),S(Q,{key:a.id,value:a.id},{default:u(()=>[h(i(a.name),1)]),_:2},1032,["value"]))),128))]),_:1}),n(J,null,{default:u(()=>[(o(!0),d(f,null,y(R.value,a=>(o(),S(G,{key:a.id,value:a.id},{default:u(()=>[n(K,{value:0},{default:u(()=>[n(X,{class:"flex overflow-x-auto space-x-4 pb-2"},{default:u(()=>[(o(!0),d(f,null,y(a.sub_categories,p=>(o(),S(Q,{key:p.id,value:p.id},{default:u(()=>[h(i(p.name),1)]),_:2},1032,["value"]))),128))]),_:2},1024),n(J,null,{default:u(()=>[(o(!0),d(f,null,y(a.sub_categories,p=>(o(),S(G,{key:p.id,value:p.id},{default:u(()=>[p.products.length>0?(o(),d("div",He,[t("div",Qe,[(o(!0),d(f,null,y(p.products,g=>(o(),d("div",{key:g.id,class:"col-span-12 lg:col-span-6 xl:col-span-3"},[t("div",Xe,[t("div",Ge,[t("img",{src:g.image?`/${g.image}`:"/image/image.png",alt:"Imagem do Produto",class:"w-full h-32 rounded-t-lg"},null,8,Je)]),t("div",Ke,[t("div",null,[t("div",Ye,i(g.name),1)])]),t("div",et,[t("span",tt,"Preço: "+i(g.price)+" MT",1)]),t("button",{onClick:Ct=>de(g),class:"bg-blue-500 text-white px-4 py-2 rounded-full mt-1"}," Adicionar ",8,at)])]))),128))])])):(o(),d("div",st,e[16]||(e[16]=[t("p",null,"No products available.",-1)])))]),_:2},1032,["value"]))),128))]),_:2},1024)]),_:2},1024)]),_:2},1032,["value"]))),128))]),_:1})]),_:1})])])])])),n(C,{header:"Confirmação",visible:q.value,"onUpdate:visible":e[0]||(e[0]=a=>q.value=a),style:{width:"350px"},modal:!0},{footer:u(()=>[n(_,{label:"Não",icon:"pi pi-times",onClick:z,class:"p-button-text"}),n(_,{label:"Sim",icon:"pi pi-check",onClick:ce,class:"p-button-text",autofocus:""})]),default:u(()=>[e[17]||(e[17]=t("div",{class:"flex align-items-center justify-content-center"},[t("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}}),t("span",null,"Tem certeza que deseja proceder?")],-1))]),_:1},8,["visible"]),n(C,{header:"Open File",visible:N.value,"onUpdate:visible":e[2]||(e[2]=a=>N.value=a),style:{width:"30vw"}},{default:u(()=>[e[18]||(e[18]=t("p",null,"Here you can manage your files or perform specific actions.",-1)),n(_,{label:"Close",onClick:e[1]||(e[1]=a=>N.value=!1)})]),_:1},8,["visible"]),n(C,{header:"Consumo da Mesa",visible:j.value,"onUpdate:visible":e[4]||(e[4]=a=>j.value=a),style:{width:"30vw"}},{default:u(()=>[t("div",lt,[e[20]||(e[20]=t("h3",{class:"text-lg font-bold mb-4"},"Detalhes do Pedido",-1)),t("ul",ot,[(o(!0),d(f,null,y(T.value,a=>(o(),d("li",{key:a.id,class:"flex justify-between border-b pb-2 mt-5"},[t("span",null,i(a.quantity)+" x "+i(a.product.name),1),t("span",null,[h("MZN "+i(a.total)+" ",1),t("i",{class:"pi pi-trash",onClick:p=>se(a.id)},null,8,nt)])]))),128))]),t("p",it,[e[19]||(e[19]=t("span",null,"Total: ",-1)),t("span",dt,"MZN "+i(k.value),1)]),t("div",rt,[n(_,{label:"Fechar",onClick:e[3]||(e[3]=a=>j.value=!1),class:"bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded"})])])]),_:1},8,["visible"]),n(C,{header:"Confirmar Exclusão",visible:D.value,"onUpdate:visible":e[7]||(e[7]=a=>D.value=a),style:{width:"20vw"}},{default:u(()=>[t("div",ut,[e[21]||(e[21]=t("p",{class:"mb-4"},[h("Insira o código para confirmar a exclusão do item "),t("strong"),h(".")],-1)),De(t("input",{"onUpdate:modelValue":e[5]||(e[5]=a=>F.value=a),type:"password",placeholder:"Código de confirmação",class:"w-full p-2 border rounded"},null,512),[[Me,F.value]]),t("div",ct,[n(_,{label:"Cancelar",onClick:e[6]||(e[6]=a=>D.value=!1),class:"bg-gray-300 hover:bg-gray-400 text-black font-semibold px-4 py-2 rounded"}),n(_,{label:"Confirmar",onClick:le,class:"bg-red-500 hover:bg-red-600 text-white font-semibold px-4 py-2 rounded"})])])]),_:1},8,["visible"]),n(C,{header:"Fechar a conta",visible:E.value,"onUpdate:visible":e[9]||(e[9]=a=>E.value=a),style:{width:"30vw"}},{default:u(()=>[t("div",pt,[e[23]||(e[23]=t("h3",{class:"text-lg font-bold mb-4"},"Detalhes do Pedido",-1)),t("ul",mt,[(o(!0),d(f,null,y(T.value,a=>(o(),d("li",{key:a.id,class:"flex justify-between border-b pb-2 mt-5"},[t("span",null,i(a.quantity)+" x "+i(a.product.name),1),t("span",null,"MZN "+i(a.total),1)]))),128))]),t("p",vt,[e[22]||(e[22]=t("span",null,"Total: ",-1)),t("span",ft,"MZN "+i(k.value),1)]),t("div",bt,[n(_,{label:"Fechar Conta",onClick:e[8]||(e[8]=a=>ne()),class:"bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded"})])])]),_:1},8,["visible"]),n(C,{header:"Fechar a conta",visible:U.value,"onUpdate:visible":e[12]||(e[12]=a=>U.value=a),style:{width:"30vw"}},{default:u(()=>[t("div",yt,[e[25]||(e[25]=t("h3",{class:"text-lg font-bold mb-4"},"Detalhes do Pedido",-1)),t("ul",_t,[(o(!0),d(f,null,y(T.value,a=>(o(),d("li",{key:a.id,class:"flex justify-between border-b pb-2 mt-5"},[t("span",null,i(a.quantity)+" x "+i(a.product.name),1),t("span",null,"MZN "+i(a.total),1)]))),128))]),n(me,{modelValue:$.value,"onUpdate:modelValue":e[10]||(e[10]=a=>$.value=a),options:L.value,optionLabel:"name",optionValue:"id",class:"mt-2",placeholder:"Selecionar"},null,8,["modelValue","options"]),t("p",gt,[e[24]||(e[24]=t("span",null,"Total: ",-1)),t("span",ht,"MZN "+i(k.value),1)]),t("div",xt,[n(_,{label:"Pagar a Conta",onClick:e[11]||(e[11]=a=>ie()),class:"bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded"})])])]),_:1},8,["visible"])],64)}}};export{Wt as default};
