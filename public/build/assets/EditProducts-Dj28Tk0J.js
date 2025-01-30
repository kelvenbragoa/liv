import{s as K}from"./index-DMuQdrqe.js";import{s as X}from"./index-CZBs6gwK.js";import{s as Y}from"./index-mha18wwy.js";import{s as Z}from"./index-BLwiGO_t.js";import{s as ee}from"./index-CIQd874r.js";import{s as ae}from"./index-Cn4GbL5i.js";import{u as le,r as l,w as oe,d as se,c as U,b as a,a as i,e as F,f as o,j as h,n as V,t as k,k as te,l as ie,F as re,i as ne,o as S,g as de}from"./app-CRDhLYtA.js";import{u as ue}from"./vee-validate-Cy-3hN9P.js";import{d as ce}from"./moment-CQ1ixRO1.js";import{c as me,a as C}from"./index.esm-CQT6nZnd.js";import"./index-tOe_dSf-.js";import"./index-h4qRBuf4.js";import"./index-BWYolI4P.js";import"./index-DMwg3V4S.js";import"./index-DR5gvq7W.js";import"./index-D4lm_p5p.js";import"./index-CTJBRcg3.js";import"./index-onqL3la4.js";import"./index-BJ82ptOr.js";const pe={key:0,class:"flex flex-col md:flex-row gap-12 min-h-screen items-center justify-center"},fe={class:"w-full"},ve={class:"flex flex-col gap-4 text-center"},ge={key:1,class:"flex flex-col md:flex-row gap-12"},_e={class:"w-full"},be={class:"card flex flex-col gap-4"},ye={class:"w-full"},xe={class:"flex flex-col gap-2"},he={id:"name-help",class:"p-error"},Ve={class:"flex flex-col gap-2"},ke={id:"price-help",class:"p-error"},Se={class:"flex flex-col gap-2"},Ce={id:"category_id-help",class:"p-error"},$e={class:"flex flex-col gap-2"},Pe={id:"sub_category_id-help",class:"p-error"},we={class:"flex flex-col gap-2"},De={class:"col-6 lg:col-3 xl:col-3"},Be=["src"],Ye={__name:"EditProducts",setup(Ue){const c=ne(),m=le();l(null);const $=l(!0),P=l(!1);let N=l(null);const R=l(""),r=l(null),I=l(1);l(15),l(0);const w=l(!1),q=l(null),D=l(null),v=l(null),p=l(!1),M=me({category_id:C().required().trim().label("Categoria"),name:C().required().trim().label("Name"),price:C().required().trim().label("Preco"),sub_category_id:C().required().trim().label("Name")}),{defineField:f,handleSubmit:W,resetForm:Fe,errors:n,setErrors:A}=ue({validationSchema:M}),[d]=f("category_id"),[g]=f("sub_category_id"),[_]=f("price"),[b]=f("name"),[O]=f("_method"),u=l();function E(){c&&c.back()}const T=()=>{w.value=!1},B=W(s=>{u.value!=null&&(s.image=u.value),p.value=!0,axios.post(`/api/products/${c.currentRoute.value.params.id}`,s,{headers:{"Content-Type":"multipart/form-data"}}).then(e=>{c.back(),m.add({severity:"success",summary:"Successo",detail:"Produto criado com sucesso",life:3e3})}).catch(e=>{p.value=!1,m.add({severity:"error",summary:"Erro}",detail:`${e.response.data.message}`,life:3e3}),e.response.data.errors&&A(e.response.data.errors)}).finally(()=>{p.value=!1})}),j=async(s=1)=>{axios.get(`/api/products/${c.currentRoute.value.params.id}/edit`,{params:{query:R.value}}).then(e=>{r.value=e.data.product,b.value=r.value.name,_.value=r.value.price,d.value=r.value.category_id,g.value=r.value.sub_category_id,u.value=r.value.image,q.value=e.data.categories,D.value=e.data.sub_categories,v.value=D.value.filter(y=>y.category_id===d.value),O.value="put",$.value=!1}).catch(e=>{$.value=!1,m.add({severity:"error",summary:`${e}`,detail:"Message Detail",life:3e3}),E()})},Q=()=>{P.value=!0,axios.delete(`/api/products/${N.value}`).then(()=>{r.value.data=r.value.data.filter(s=>s.id!==N.value),T(),m.add({severity:"success",summary:"Sucesso",detail:"Sucesso ao apagar",life:3e3})}).catch(s=>{m.add({severity:"error",summary:"Erro",detail:`${s}`,life:3e3}),P.value=!1}).finally(()=>{P.value=!1})},G=s=>{u.value=s.files[0],console.log(u.value)};return ce(()=>{j(I.value)},300),oe(d,s=>{s?v.value=D.value.filter(e=>e.category_id===s):v.value=[]}),se(()=>{j()}),(s,e)=>{const y=ae,x=ee,z=Z,L=Y,H=X,J=K;return S(),U(re,null,[$.value?(S(),U("div",pe,[a("div",fe,[a("div",ve,[i(y,{style:{width:"50px",height:"50px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"}),e[6]||(e[6]=a("p",null,"Por Favor Aguarde...",-1))])])])):(S(),U("div",ge,[a("div",_e,[a("div",be,[a("div",ye,[i(x,{label:"Voltar",class:"mr-2 mb-2",onClick:E},{default:F(()=>e[7]||(e[7]=[a("i",{class:"pi pi-angle-left"},null,-1),de(" Voltar")])),_:1})]),e[13]||(e[13]=a("div",{class:"font-semibold text-xl"},"Produto",-1)),e[14]||(e[14]=a("small",{class:"p-error"},"Os campos marcados * sao considerados campos obrigatorios.",-1)),a("form",{onSubmit:e[4]||(e[4]=(...t)=>o(B)&&o(B)(...t))},[a("div",xe,[e[8]||(e[8]=a("label",{for:"name1"},"Nome",-1)),i(z,{modelValue:o(b),"onUpdate:modelValue":e[0]||(e[0]=t=>h(b)?b.value=t:null),id:"name",placeholder:"Nome da Produto",class:V({"p-invalid":o(n).name}),type:"text"},null,8,["modelValue","class"]),a("small",he,k(o(n).name),1)]),a("div",Ve,[e[9]||(e[9]=a("label",{for:"name1"},"Preço",-1)),i(z,{modelValue:o(_),"onUpdate:modelValue":e[1]||(e[1]=t=>h(_)?_.value=t:null),id:"price",placeholder:"Preço",class:V({"p-invalid":o(n).price}),type:"number"},null,8,["modelValue","class"]),a("small",ke,k(o(n).price),1)]),a("div",Se,[e[10]||(e[10]=a("label",{for:"name1"},"Categoria",-1)),i(L,{modelValue:o(d),"onUpdate:modelValue":e[2]||(e[2]=t=>h(d)?d.value=t:null),options:q.value,optionLabel:"name",optionValue:"id",placeholder:"Selecionar",class:V({"p-invalid":o(n).category_id})},null,8,["modelValue","options","class"]),a("small",Ce,k(o(n).category_id),1)]),a("div",$e,[e[11]||(e[11]=a("label",{for:"name1"},"SubCategoria",-1)),i(L,{modelValue:o(g),"onUpdate:modelValue":e[3]||(e[3]=t=>h(g)?g.value=t:null),options:v.value,optionLabel:"name",optionValue:"id",placeholder:"Selecionar",class:V({"p-invalid":o(n).sub_category_id})},null,8,["modelValue","options","class"]),a("small",Pe,k(o(n).sub_category_id),1)]),a("div",we,[e[12]||(e[12]=a("label",{for:"name1"},"Imagem",-1)),i(H,{mode:"basic",name:"image[]",accept:"image/*",auto:"",maxFileSize:1e6,customUpload:"",onUploader:G})]),a("div",De,[a("img",{src:"/"+u.value,alt:"",weigth:"100",height:"100",style:{"border-radius":"15px"}},null,8,Be)]),i(x,{label:"Submeter",class:"mr-2 mb-2",onClick:o(B),disabled:p.value},null,8,["onClick","disabled"]),p.value?(S(),te(y,{key:0,style:{width:"35px",height:"35px"},strokeWidth:"8",fill:"var(--surface-ground)",animationDuration:".5s","aria-label":"Custom ProgressSpinner"})):ie("",!0)],32)])])])),i(J,{header:"Confirmação",visible:w.value,"onUpdate:visible":e[5]||(e[5]=t=>w.value=t),style:{width:"350px"},modal:!0},{footer:F(()=>[i(x,{label:"Não",icon:"pi pi-times",onClick:T,class:"p-button-text"}),i(x,{label:"Sim",icon:"pi pi-check",onClick:Q,class:"p-button-text",autofocus:""})]),default:F(()=>[e[15]||(e[15]=a("div",{class:"flex align-items-center justify-content-center"},[a("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}}),a("span",null,"Tem certeza que deseja proceder?")],-1))]),_:1},8,["visible"])],64)}}};export{Ye as default};
