import{s as ue}from"./index-HiKbvKD5.js";import{s as re}from"./index-CML4I8xk.js";import{s as ce}from"./index-DsYGz-yW.js";import{s as pe,a as me,b as ve}from"./index-BnGhFb5i.js";import{s as fe,a as be}from"./index-C7hZ3EzH.js";import{s as ye}from"./index-BA7Ca_ZT.js";import{s as _e}from"./index-DoDxNUM1.js";import{u as he,r as o,w as ge,b as xe,c as i,d as t,a as d,g as w,t as n,l as L,F as b,a1 as y,e as r,o as l,k as j,i as we}from"./app-DpegVBBD.js";import{d as ke}from"./moment-CQ1ixRO1.js";import"./index-CNQNH7rt.js";import"./index-8e2s9cPg.js";import"./index-CfWSzMo7.js";import"./index-kIrwgUrv.js";import"./index-Dwoluwl6.js";import"./index-BrMVFeMR.js";import"./index-SIrGyeQJ.js";import"./index-DQAJXjBo.js";import"./index-CcQjEyo5.js";import"./index-BlcEsDrz.js";import"./index-h5R4gv03.js";import"./index-DdRnJjEh.js";const Ce={key:0,class:"flex flex-col md:flex-row gap-12 min-h-screen items-center justify-center"},Te={class:"w-full"},$e={class:"flex flex-col gap-4 text-center"},De={key:1},Me={class:"grid grid-cols-12 gap-4 h-screen"},Pe={class:"col-span-12 lg:col-span-3 h-full"},Re={class:"card flex flex-col gap-4 h-full"},je={key:0},Ue=["onClick"],Be={class:"flex justify-between mb-4"},Ee={key:0,class:"mt-4"},Se=["disabled"],qe={key:0,class:"flex flex-col gap-4 text-center"},Le={class:"col-span-12 lg:col-span-9"},Fe={class:"mb-2"},Ne={class:"card flex flex-col gap-4"},Ae={key:0},Ve={class:"grid grid-cols-12 gap-8"},Ze={class:"card mb-0 bg-gray-100"},Ie={class:"mb-4"},Oe=["src"],ze={class:"flex justify-between mb-4"},We={class:"text-surface-900 dark:text-surface-0 font-medium text-xl"},He={class:"flex justify-between mb-4"},Qe={class:"text-primary font-medium"},Xe=["onClick"],Ge={key:1},Je={class:"p-4"},Ke={class:"space-y-2"},Ye={class:"mt-4 text-lg font-semibold"},et={class:"text-blue-500"},tt={class:"mt-4 flex justify-end"},st={class:"p-4"},at={class:"space-y-2"},lt={class:"mt-4 text-lg font-semibold"},ot={class:"text-blue-500"},nt={class:"mt-4 flex justify-end"},it={class:"p-4"},dt={class:"space-y-2"},ut={class:"mt-4 text-lg font-semibold"},rt={class:"text-blue-500"},ct={class:"mt-4 flex justify-end"},St={__name:"IndexPdvCategories",setup(pt){const f=we(),c=he();o(null);const T=o(!0),U=o(!1);let F=o(null);const N=o(""),B=o(null),G=o(1);o(15),o(0);const E=o(!1),S=o(null),h=o([]),g=o(0),k=o(0),p=o(!1),$=o([]),J=o(!1),D=o(1),A=o([]),q=o(!1),M=o(!1),K=o(!1),P=o(!1),R=o(!1),Y=[{label:"Consumo",items:[{label:"Ver consumo",icon:"pi pi-fw pi-folder-open",command:()=>{M.value=!0}},{label:"Imprimir Consumo",icon:"pi pi-fw pi-print",command:()=>{oe()}}]},{label:"Conta",items:[{label:"Fechamento da Conta",icon:"pi pi-fw pi-lock",command:()=>{P.value=!0}},{label:"Finalizar da Conta",icon:"pi pi-fw pi-check",command:()=>{R.value=!0}}]}];function V(){f&&f.back()}const Z=()=>{E.value=!1};function ee(){const a={products:h.value.map(e=>({id:e.id,name:e.name,quantity:e.quantity,total:e.price*e.quantity})),total:g.value,table_id:f.currentRoute.value.params.id};p.value=!0,axios.post("/api/pdv",a,{headers:{"Content-Type":"multipart/form-data"},responseType:"blob"}).then(e=>{f.back();const u=window.URL.createObjectURL(new Blob([e.data])),m=document.createElement("a");m.href=u,m.setAttribute("download","recibo.pdf"),document.body.appendChild(m),m.click(),J.value=!1,c.add({severity:"success",summary:"Successo",detail:"Produto encomedado sucesso!",life:3e3})}).catch(e=>{p.value=!1,c.add({severity:"error",summary:"Erro}",detail:`${e}`,life:3e3}),e.response.data.errors&&setErrors(e.response.data.errors)}).finally(()=>{p.value=!1})}function te(){p.value=!0,axios.get(`/api/pdv/closeaccount/${f.currentRoute.value.params.id}`,{responseType:"blob"}).then(a=>{const e=window.URL.createObjectURL(new Blob([a.data])),u=document.createElement("a");u.href=e,u.setAttribute("download","recibo.pdf"),document.body.appendChild(u),u.click(),P.value=!1,c.add({severity:"success",summary:"Successo",detail:"Encomenda fechada sucesso!",life:3e3})}).catch(a=>{p.value=!1,c.add({severity:"error",summary:"Erro}",detail:`${a}`,life:3e3}),a.response.data.errors&&setErrors(a.response.data.errors)}).finally(()=>{p.value=!1})}function se(){const a={payment_method_id:D.value,table_id:f.currentRoute.value.params.id};p.value=!0,axios.post("/api/payaccount",a,{headers:{"Content-Type":"multipart/form-data"},responseType:"blob"}).then(e=>{f.back();const u=window.URL.createObjectURL(new Blob([e.data])),m=document.createElement("a");m.href=u,m.setAttribute("download","recibo.pdf"),document.body.appendChild(m),m.click(),R.value=!1,c.add({severity:"success",summary:"Successo",detail:"Encomenda fechada sucesso!",life:3e3})}).catch(e=>{p.value=!1,c.add({severity:"error",summary:"Erro}",detail:`${e}`,life:3e3}),e.response.data.errors&&setErrors(e.response.data.errors)}).finally(()=>{p.value=!1})}function ae(a){const e=h.value.find(u=>u.id===a.id);e?e.quantity+=1:h.value.push({...a,quantity:1}),I()}function le(a){h.value.splice(a,1),I()}function I(){g.value=h.value.reduce((a,e)=>a+e.price*e.quantity,0)}const oe=async()=>{axios.post(`/api/getreceipt/${f.currentRoute.value.params.id}`,{responseType:"blob"}).then(a=>{f.back();const e=window.URL.createObjectURL(new Blob([a.data])),u=document.createElement("a");u.href=e,u.setAttribute("download","consumo.pdf"),document.body.appendChild(u),u.click(),K.value=!1,c.add({severity:"success",summary:"Successo",detail:"Consumo Impresso com sucesso!",life:3e3})}).catch(a=>{T.value=!1,c.add({severity:"error",summary:`${a}`,detail:"Message Detail",life:3e3}),V()})},O=async(a=1)=>{axios.get(`/api/pdv/${f.currentRoute.value.params.id}`,{params:{query:N.value}}).then(e=>{B.value=e.data,k.value=e.data.total_consumed,S.value=e.data.categories,$.value=e.data.order_items,A.value=e.data.payment_methods,D.value=1,T.value=!1}).catch(e=>{T.value=!1,c.add({severity:"error",summary:`${e}`,detail:"Message Detail",life:3e3}),V()})},ne=()=>{U.value=!0,axios.delete(`/api/tables/${F.value}`).then(()=>{B.value.data=B.value.data.filter(a=>a.id!==F.value),Z(),c.add({severity:"success",summary:"Sucesso",detail:"Sucesso ao apagar",life:3e3})}).catch(a=>{c.add({severity:"error",summary:"Erro",detail:`${a}`,life:3e3}),U.value=!1}).finally(()=>{U.value=!1})},ie=ke(()=>{O(G.value)},300);return ge(N,ie),xe(()=>{O()}),(a,e)=>{const u=_e,m=ye,z=me,W=ve,H=fe,Q=be,X=pe,x=ce,C=re,de=ue;return l(),i(b,null,[T.value?(l(),i("div",Ce,[t("div",Te,[t("div",$e,[d(u,{style:{width:"50px",height:"50px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"}),e[10]||(e[10]=t("p",null,"Por Favor Aguarde...",-1))])])])):(l(),i("div",De,[t("div",Me,[t("div",Pe,[t("div",Re,[t("h2",null,[e[11]||(e[11]=w("Resumo da venda ")),g.value>0?(l(),i("div",je,[t("strong",null,"Total: "+n(g.value)+" MT",1)])):L("",!0)]),(l(!0),i(b,null,y(h.value,(s,v)=>(l(),i("div",{key:v,class:"card bg-gray-100 p-4"},[t("div",null,[t("strong",null,n(s.name),1),t("button",{onClick:_=>le(v),class:"rounded-full bg-red-100",style:{width:"2.5rem",height:"2.5rem"}},"X",8,Ue)]),t("div",Be,[t("div",null,n(s.quantity)+" x "+n(s.price)+" MT",1),t("div",null,n(s.price*s.quantity)+" MT",1)])]))),128)),g.value>0?(l(),i("div",Ee,[t("strong",null,"Total: "+n(g.value)+" MT",1),t("button",{disabled:p.value,onClick:ee,class:"bg-blue-500 text-white px-4 py-2 rounded-full mt-1",style:{width:"100%"}},e[12]||(e[12]=[w("Adicionar a conta"),t("i",{class:"pi pi-print"},null,-1)]),8,Se),p.value?(l(),i("div",qe,[d(u,{style:{width:"50px",height:"50px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"})])):L("",!0)])):L("",!0)])]),t("div",Le,[t("div",Fe,[d(m,{model:Y},{end:r(()=>[t("p",null,"Total Consumo: "+n(k.value)+" MT",1)]),_:1})]),t("div",Ne,[d(X,{value:1},{default:r(()=>[d(W,{class:"flex overflow-x-auto space-x-4 pb-2"},{default:r(()=>[(l(!0),i(b,null,y(S.value,s=>(l(),j(z,{key:s.id,value:s.id},{default:r(()=>[w(n(s.name),1)]),_:2},1032,["value"]))),128))]),_:1}),d(Q,null,{default:r(()=>[(l(!0),i(b,null,y(S.value,s=>(l(),j(H,{key:s.id,value:s.id},{default:r(()=>[d(X,{value:0},{default:r(()=>[d(W,{class:"flex overflow-x-auto space-x-4 pb-2"},{default:r(()=>[(l(!0),i(b,null,y(s.sub_categories,v=>(l(),j(z,{key:v.id,value:v.id},{default:r(()=>[w(n(v.name),1)]),_:2},1032,["value"]))),128))]),_:2},1024),d(Q,null,{default:r(()=>[(l(!0),i(b,null,y(s.sub_categories,v=>(l(),j(H,{key:v.id,value:v.id},{default:r(()=>[v.products.length>0?(l(),i("div",Ae,[t("div",Ve,[(l(!0),i(b,null,y(v.products,_=>(l(),i("div",{key:_.id,class:"col-span-12 lg:col-span-6 xl:col-span-3"},[t("div",Ze,[t("div",Ie,[t("img",{src:_.image?`/${_.image}`:"/image/image.png",alt:"Imagem do Produto",class:"w-full h-32 rounded-t-lg"},null,8,Oe)]),t("div",ze,[t("div",null,[t("div",We,n(_.name),1)])]),t("div",He,[t("span",Qe,"Preço: "+n(_.price)+" MT",1)]),t("button",{onClick:mt=>ae(_),class:"bg-blue-500 text-white px-4 py-2 rounded-full mt-1"}," Adicionar ",8,Xe)])]))),128))])])):(l(),i("div",Ge,e[13]||(e[13]=[t("p",null,"No products available.",-1)])))]),_:2},1032,["value"]))),128))]),_:2},1024)]),_:2},1024)]),_:2},1032,["value"]))),128))]),_:1})]),_:1})])])])])),d(C,{header:"Confirmação",visible:E.value,"onUpdate:visible":e[0]||(e[0]=s=>E.value=s),style:{width:"350px"},modal:!0},{footer:r(()=>[d(x,{label:"Não",icon:"pi pi-times",onClick:Z,class:"p-button-text"}),d(x,{label:"Sim",icon:"pi pi-check",onClick:ne,class:"p-button-text",autofocus:""})]),default:r(()=>[e[14]||(e[14]=t("div",{class:"flex align-items-center justify-content-center"},[t("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}}),t("span",null,"Tem certeza que deseja proceder?")],-1))]),_:1},8,["visible"]),d(C,{header:"Open File",visible:q.value,"onUpdate:visible":e[2]||(e[2]=s=>q.value=s),style:{width:"30vw"}},{default:r(()=>[e[15]||(e[15]=t("p",null,"Here you can manage your files or perform specific actions.",-1)),d(x,{label:"Close",onClick:e[1]||(e[1]=s=>q.value=!1)})]),_:1},8,["visible"]),d(C,{header:"Consumo da Mesa",visible:M.value,"onUpdate:visible":e[4]||(e[4]=s=>M.value=s),style:{width:"30vw"}},{default:r(()=>[t("div",Je,[e[18]||(e[18]=t("h3",{class:"text-lg font-bold mb-4"},"Detalhes do Pedido",-1)),t("ul",Ke,[(l(!0),i(b,null,y($.value,s=>(l(),i("li",{key:s.id,class:"flex justify-between border-b pb-2 mt-5"},[t("span",null,n(s.quantity)+" x "+n(s.product.name),1),t("span",null,[w("MZN "+n(s.total)+" ",1),e[16]||(e[16]=t("i",{class:"pi pi-trash"},null,-1))])]))),128))]),t("p",Ye,[e[17]||(e[17]=t("span",null,"Total: ",-1)),t("span",et,"MZN "+n(k.value),1)]),t("div",tt,[d(x,{label:"Fechar",onClick:e[3]||(e[3]=s=>M.value=!1),class:"bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded"})])])]),_:1},8,["visible"]),d(C,{header:"Fechar a conta",visible:P.value,"onUpdate:visible":e[6]||(e[6]=s=>P.value=s),style:{width:"30vw"}},{default:r(()=>[t("div",st,[e[21]||(e[21]=t("h3",{class:"text-lg font-bold mb-4"},"Detalhes do Pedido",-1)),t("ul",at,[(l(!0),i(b,null,y($.value,s=>(l(),i("li",{key:s.id,class:"flex justify-between border-b pb-2 mt-5"},[t("span",null,n(s.quantity)+" x "+n(s.product.name),1),t("span",null,[w("MZN "+n(s.total)+" ",1),e[19]||(e[19]=t("i",{class:"pi pi-trash"},null,-1))])]))),128))]),t("p",lt,[e[20]||(e[20]=t("span",null,"Total: ",-1)),t("span",ot,"MZN "+n(k.value),1)]),t("div",nt,[d(x,{label:"Fechar Conta",onClick:e[5]||(e[5]=s=>te()),class:"bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded"})])])]),_:1},8,["visible"]),d(C,{header:"Fechar a conta",visible:R.value,"onUpdate:visible":e[9]||(e[9]=s=>R.value=s),style:{width:"30vw"}},{default:r(()=>[t("div",it,[e[23]||(e[23]=t("h3",{class:"text-lg font-bold mb-4"},"Detalhes do Pedido",-1)),t("ul",dt,[(l(!0),i(b,null,y($.value,s=>(l(),i("li",{key:s.id,class:"flex justify-between border-b pb-2 mt-5"},[t("span",null,n(s.quantity)+" x "+n(s.product.name),1),t("span",null,"MZN "+n(s.total),1)]))),128))]),d(de,{modelValue:D.value,"onUpdate:modelValue":e[7]||(e[7]=s=>D.value=s),options:A.value,optionLabel:"name",optionValue:"id",class:"mt-2",placeholder:"Selecionar"},null,8,["modelValue","options"]),t("p",ut,[e[22]||(e[22]=t("span",null,"Total: ",-1)),t("span",rt,"MZN "+n(k.value),1)]),t("div",ct,[d(x,{label:"Pagar a Conta",onClick:e[8]||(e[8]=s=>se()),class:"bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded"})])])]),_:1},8,["visible"])],64)}}};export{St as default};
