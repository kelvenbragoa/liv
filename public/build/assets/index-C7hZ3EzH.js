import{B as d,o as s,c,N as n,p as o,P as u,$,F as p,M as h,aR as y,k as f,e as m,O as T,l as P,n as k}from"./app-DpegVBBD.js";var B={root:"p-tabpanels"},S=d.extend({name:"tabpanels",classes:B}),A={name:"BaseTabPanels",extends:u,props:{},style:S,provide:function(){return{$pcTabPanels:this,$parentInstance:this}}},C={name:"TabPanels",extends:A,inheritAttrs:!1};function x(a,e,r,v,b,t){return s(),c("div",o({class:a.cx("root"),role:"presentation"},a.ptmi("root")),[n(a.$slots,"default")],16)}C.render=x;var _={root:function(e){var r=e.instance;return["p-tabpanel",{"p-tabpanel-active":r.active}]}},g=d.extend({name:"tabpanel",classes:_}),w={name:"BaseTabPanel",extends:u,props:{value:{type:[String,Number],default:void 0},as:{type:[String,Object],default:"DIV"},asChild:{type:Boolean,default:!1},header:null,headerStyle:null,headerClass:null,headerProps:null,headerActionProps:null,contentStyle:null,contentClass:null,contentProps:null,disabled:Boolean},style:g,provide:function(){return{$pcTabPanel:this,$parentInstance:this}}},z={name:"TabPanel",extends:w,inheritAttrs:!1,inject:["$pcTabs"],computed:{active:function(){var e;return $((e=this.$pcTabs)===null||e===void 0?void 0:e.d_value,this.value)},id:function(){var e;return"".concat((e=this.$pcTabs)===null||e===void 0?void 0:e.id,"_tabpanel_").concat(this.value)},ariaLabelledby:function(){var e;return"".concat((e=this.$pcTabs)===null||e===void 0?void 0:e.id,"_tab_").concat(this.value)},attrs:function(){return o(this.a11yAttrs,this.ptmi("root",this.ptParams))},a11yAttrs:function(){var e;return{id:this.id,tabindex:(e=this.$pcTabs)===null||e===void 0?void 0:e.tabindex,role:"tabpanel","aria-labelledby":this.ariaLabelledby,"data-pc-name":"tabpanel","data-p-active":this.active}},ptParams:function(){return{context:{active:this.active}}}}};function D(a,e,r,v,b,t){var l,i;return t.$pcTabs?(s(),c(p,{key:1},[a.asChild?n(a.$slots,"default",{key:1,class:k(a.cx("root")),active:t.active,a11yAttrs:t.a11yAttrs}):(s(),c(p,{key:0},[!((l=t.$pcTabs)!==null&&l!==void 0&&l.lazy)||t.active?h((s(),f(T(a.as),o({key:0,class:a.cx("root")},t.attrs),{default:m(function(){return[n(a.$slots,"default")]}),_:3},16,["class"])),[[y,(i=t.$pcTabs)!==null&&i!==void 0&&i.lazy?!0:t.active]]):P("",!0)],64))],64)):n(a.$slots,"default",{key:0})}z.render=D;export{C as a,z as s};
