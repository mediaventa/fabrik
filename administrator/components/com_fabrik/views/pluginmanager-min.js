var PluginManager=new Class({initialize:function(a){this.plugins=a;this.counter=0;this.opts=this.opts||{};this.deletePluginClick=this.deletePlugin.bindWithEvent(this);this.watchAdd()},_makeSel:function(h,d,g,f){var b,a;var e=[];this.sel=f;e.push(new Element("option",{value:""}).appendText(Joomla.JText._("COM_FABRIK_PLEASE_SELECT")));if(typeOf(g)==="object"){$H(g).each(function(i,c){e.push(new Element("optgroup",{label:c}));i.each(function(j){e=this._addSelOpt(e,j)}.bind(this))}.bind(this))}else{g.each(function(c){e=this._addSelOpt(e,c)}.bind(this))}return new Element("select",{"class":h,name:d}).adopt(e)},_addSelOpt:function(a,b){if(typeOf(b)==="object"){v=b.value?b.value:b.name;l=b.label?b.label:v}else{v=l=b}if(v===this.sel){a.push(new Element("option",{value:v,selected:"selected"}).set("text",l))}else{a.push(new Element("option",{value:v}).set("text",l))}return a},addPlugin:function(a){this.plugins.push(a)},deletePlugin:function(a){a.stop();$(a.target).up(3).dispose();this.counter--},watchAdd:function(){$("addPlugin").addEvent("click",function(a){a.stop();this.addAction("","","","")}.bind(this))},watchDelete:function(){$("plugins").getElements(".delete").each(function(a){a.removeEvents("click");a.addEvent("click",this.deletePluginClick)}.bind(this))},getPluginTop:function(){return""},addAction:function(f,d,e,h,k){k=k===false?false:true;var b=new Element("td");var j="";this.plugins.each(function(c){if(c.name===d){j+=f}else{j+=c.options.html}}.bind(this));j=j.replace(/\[0\]/gi,"["+this.counter+"]");b.innerHTML=j;var g="block";var i=new Element("div",{"class":"actionContainer"}).adopt(new Element("table",{"class":"adminform",id:"formAction_"+this.counter,styles:{display:g}}).adopt(new Element("tbody",{styles:{width:"100%"}}).adopt([this.getPluginTop(d,e,h),new Element("tr").adopt(b),new Element("tr").adopt(new Element("td",{}).adopt(new Element("a",{href:"#","class":"delete removeButton"}).appendText(Joomla.JText._("COM_FABRIK_DELETE"))))])));i.inject($("plugins"));if(this.counter!==0){i.getElements("input[name^=params]","select[name^=params]").each(function(o){if(o.id!==""){var c=o.id.split("-");c.pop();o.id=c.join("-")+"-"+this.counter}}.bind(this));i.getElements("img[src=components/com_fabrik/images/ajax-loader.gif]").each(function(c){c.id=c.id.replace("-0_loader","-"+this.counter+"_loader")}.bind(this));if(k===true){this.plugins.each(function(c){var o=new CloneObject(c,true,[]);o.cloned(this.counter)}.bind(this))}}var n=$("formAction_"+this.counter);n.getElements("."+this.opts.type+"Settings").hide();var a=n.getElement(" .page-"+d);if(a){a.show()}n.getElement(".elementtype").addEvent("change",function(o){o.stop();var p=$(o.target).up(3).id.replace("formAction_","");$("formAction_"+p).getElements("."+this.opts.type+"Settings").hide();var c=$(o.target).get("value");if(c!==Joomla.JText._("COM_FABRIK_PLEASE_SELECT")&&c!==""){$("formAction_"+p).getElement(".page-"+c).show()}}.bind(this));this.watchDelete();var m=new Tips($$("#formAction_"+this.counter+" .hasTip"),{});this.counter++}});fabrikAdminPlugin=new Class({Implements:[Options],options:{},initialize:function(c,b,a){this.name=c;this.label=b;this.setOptions(a)},cloned:function(){}});