import{B as s,o as l,c as d,d as o,N as t,p as a,P as p}from"./app-TAwxfwHz.js";var c=function(n){var r=n.dt;return`
.p-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    padding: `.concat(r("toolbar.padding"),`;
    background: `).concat(r("toolbar.background"),`;
    border: 1px solid `).concat(r("toolbar.border.color"),`;
    color: `).concat(r("toolbar.color"),`;
    border-radius: `).concat(r("toolbar.border.radius"),`;
    gap: `).concat(r("toolbar.gap"),`;
}

.p-toolbar-start,
.p-toolbar-center,
.p-toolbar-end {
    display: flex;
    align-items: center;
}
`)},i={root:"p-toolbar p-component",start:"p-toolbar-start",center:"p-toolbar-center",end:"p-toolbar-end"},b=s.extend({name:"toolbar",theme:c,classes:i}),m={name:"BaseToolbar",extends:p,props:{ariaLabelledby:{type:String,default:null}},style:b,provide:function(){return{$pcToolbar:this,$parentInstance:this}}},u={name:"Toolbar",extends:m,inheritAttrs:!1},v=["aria-labelledby"];function y(e,n,r,$,f,g){return l(),d("div",a({class:e.cx("root"),role:"toolbar","aria-labelledby":e.ariaLabelledby},e.ptmi("root")),[o("div",a({class:e.cx("start")},e.ptm("start")),[t(e.$slots,"start")],16),o("div",a({class:e.cx("center")},e.ptm("center")),[t(e.$slots,"center")],16),o("div",a({class:e.cx("end")},e.ptm("end")),[t(e.$slots,"end")],16)],16,v)}u.render=y;export{u as s};
