var PluginManager=new Class({initialize:function(a){this.plugins=a;this.counter=0;this.opts=this.opts||{};this.deletePluginClick=this.deletePlugin.bindWithEvent(this);this.watchAdd()},_makeSel:function(h,d,g,f){var b,a;var e=[];this.sel=f;e.push(new Element("option",{value:""}).appendText(Joomla.JText._("COM_FABRIK_PLEASE_SELECT")));if(typeOf(g)==="object"){$H(g).each(function(i,c){e.push(new Element("optgroup",{label:c}));i.each(function(j){e=this._addSelOpt(e,j)}.bind(this))}.bind(this))}else{g.each(function(c){e=this._addSelOpt(e,c)}.bind(this))}return new Element("select",{"class":h,name:d}).adopt(e)},_addSelOpt:function(a,b){if(typeOf(b)==="object"){v=b.value?b.value:b.name;l=b.label?b.label:v}else{v=l=b}if(v===this.sel){a.push(new Element("option",{value:v,selected:"selected"}).set("text",l))}else{a.push(new Element("option",{value:v}).set("text",l))}return a},addPlugin:function(a){this.plugins.push(a)},deletePlugin:function(a){$("plugins").getElements("input, select, textarea").each(function(b){var d=b.name.match(/\[[0-9]\]/);if(d){var e=d[0].replace("[","").replace("]","").toInt();if(e>0){e=e-1}b.name=b.name.replace(/\[[0-9]\]/,"["+e+"]")}});a.stop();$(a.target).up(3).dispose();this.counter--},watchAdd:function(){$("addPlugin").addEvent("click",function(a){a.stop();this.addAction("","",{})}.bind(this))},watchDelete:function(){$("plugins").getElements(".delete").each(function(a){a.removeEvents("click");a.addEvent("click",this.deletePluginClick)}.bind(this))},getPluginTop:function(){return""},addAction:function(f,e,a,j){j=j===false?false:true;var d=new Element("td");var i="";this.plugins.each(function(c){if(c.name===e){i+=f}else{i+=c.options.html}}.bind(this));i=i.replace(/\[0\]/gi,"["+this.counter+"]");d.innerHTML=i;var g="block";a.counter=this.counter;var h=new Element("div",{"class":"actionContainer"}).adopt(new Element("table",{"class":"adminform",id:"formAction_"+this.counter,styles:{display:g}}).adopt(new Element("tbody",{styles:{width:"100%"}}).adopt([this.getPluginTop(e,a),new Element("tr").adopt(d),new Element("tr").adopt(new Element("td",{}).adopt(new Element("a",{href:"#","class":"delete removeButton"}).appendText(Joomla.JText._("COM_FABRIK_DELETE"))))])));h.inject($("plugins"));if(this.counter!==0){h.getElements("input[name^=params]","select[name^=params]").each(function(n){if(n.id!==""){var c=n.id.split("-");c.pop();n.id=c.join("-")+"-"+this.counter}}.bind(this));h.getElements("img[src=components/com_fabrik/images/ajax-loader.gif]").each(function(c){c.id=c.id.replace("-0_loader","-"+this.counter+"_loader")}.bind(this));if(j===true){this.plugins.each(function(c){var n=new CloneObject(c,true,[]);n.cloned(this.counter)}.bind(this))}}var m=$("formAction_"+this.counter);m.getElements("."+this.opts.type+"Settings").hide();var b=m.getElement(" .page-"+e);if(b){b.show()}m.getElement(".elementtype").addEvent("change",function(n){n.stop();var o=n.target.getParent(".adminform").id.replace("formAction_","");$("formAction_"+o).getElements("."+this.opts.type+"Settings").hide();var c=n.target.get("value");if(c!==Joomla.JText._("COM_FABRIK_PLEASE_SELECT")&&c!==""){$("formAction_"+o).getElement(".page-"+c).show()}}.bind(this));this.watchDelete();var k=new Tips($$("#formAction_"+this.counter+" .hasTip"),{});this.counter++},getPublishedYesNo:function(c){var b="<label>"+Joomla.JText._("COM_FABRIK_PUBLISHED")+"</label>";var a=c.state!==false?'checked="checked"':"";var d=c.state===false?'checked="checked"':"";b+='<fieldset class="radio"><label>'+Joomla.JText._("JYES")+'<input type="radio" name="jform[params][plugin_state]['+c.counter+']" '+a+' value="1"></label>';b+="<label>"+Joomla.JText._("JNO")+'<input type="radio" name="jform[params][plugin_state]['+c.counter+']"'+d+' value="0"></label></fieldset>';return b}});fabrikAdminPlugin=new Class({Implements:[Options],options:{},initialize:function(c,b,a){this.name=c;this.label=b;this.setOptions(a)},cloned:function(){}});