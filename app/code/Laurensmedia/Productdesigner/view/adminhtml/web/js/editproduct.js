require(["jquery"], function ($){
	
	$("#addbutton").click(function(){
		huidigewaarde = $("#sidecounter").val();
		nieuwewaarde = (+huidigewaarde + 1);
		$("#sidecounter").val(nieuwewaarde);
		$("#sideslijst").append("<div class=\"item\" id=\"sideitem_"+nieuwewaarde+"\"> Title: <input type=\"text\" name=\"side["+nieuwewaarde+"][label]\" /><br />Upload image: <input type=\"file\" name=\"side["+nieuwewaarde+"][image]\" /><br />Upload overlay image (keep empty for auto-generation): <input type=\"file\" name=\"side["+nieuwewaarde+"][overlay]\" /><br /> Surcharge: <input type=\"text\" name=\"side["+nieuwewaarde+"][surcharge]\" /><br /><button id=\"del_"+nieuwewaarde+"\" type=\"button\" class=\"scalable delete\" onclick=\"jQuery(this).parent(\'div.item\').remove();\"><span>Delete</span></button><br /><br /></div>");
	});
	$(".deleteside").click(function(){
		var delid = $(this).attr('id');
		delid = delid.replace('del_', '');
		$("#sideitem_"+delid).remove();
	});

		$("#size-addbutton").click(function(){
			huidigewaarde = $("#maatcounter").val();
			nieuwewaarde = (+huidigewaarde + 1);
			$("#maatcounter").val(nieuwewaarde);
			$("#maatlijst").append("<div class=\"item\" id=\"sizeitem_"+nieuwewaarde+"\"> Title: <input type=\"text\" name=\"maat_"+nieuwewaarde+"\" /> Surcharge: <input type=\"text\" name=\"meerprijs_"+nieuwewaarde+"\" /> <button id=\"del_"+nieuwewaarde+"\" type=\"button\" class=\"scalable delete\" onclick=\"jQuery(this).parent(\'div.item\').remove();\"><span>Delete</span></button><br /><br /></div>");
		});
		$(".deletemaat").click(function(){
			var delid = $(this).attr('id');
			delid = delid.replace('del_', '');
			$("#sizeitem_"+delid).remove();
		});
	


	$("#drukaddbutton").click(function(){
		huidigewaarde = $("#druktypecounter").val();
		nieuwewaarde = (+huidigewaarde + 1);
		$("#druktypecounter").val(nieuwewaarde);
		$("#druktypelijst").append("<div id=\"druktypeitem_"+nieuwewaarde+"\"> Title: <input type=\"text\" name=\"druktype_"+nieuwewaarde+"\" /> Surcharge: <input type=\"text\" name=\"druktypemeerprijs_"+nieuwewaarde+"\" /> <button id=\"druktypedel_"+nieuwewaarde+"\" type=\"button\" class=\"scalable delete deletemaat\" onclick=\"deletemaat("+nieuwewaarde+")\"><span>Delete</span></button><br /><br /></div>");
	});
	$(".deletedruktype").click(function(){
		var delid = $(this).attr('id');
		delid = delid.replace('del_', '');
		$("#druktypeitem_"+delid).remove();
	});

	function deletemaat(delid){
		$("#druktypeitem_"+delid).remove();
	}


	$("#textcoloraddbutton").click(function(){
		huidigewaarde = $("#textcolorscounter").val();
		nieuwewaarde = (+huidigewaarde + 1);
		$("#textcolorscounter").val(nieuwewaarde);
		$("#textcolorlijst").append("<div id=\"textcolorsitem_"+nieuwewaarde+"\"><input type='text' name='textcolors_"+nieuwewaarde+"' class='textcolorpicker' /><button id=\"textcolorsdel_"+nieuwewaarde+"\" type=\"button\" class=\"scalable delete deletemaat\" onclick=\"deletemaat("+nieuwewaarde+")\"><span>Delete</span></button><br /><br /></div>");
		$("input.textcolorpicker").each(function(){
			var papa = $(this);
			$(this).ColorPicker({ onChange: function(hsb, hex, rgb, el){
								$(papa).val("#"+hex).css('background-color', '#'+hex);
								}
						});
		});
	});
	$(".deletetextcolor").click(function(){
		var delid = $(this).attr('id');
		delid = delid.replace('del_', '');
		$("#textcolorsitem_"+delid).remove();
	});

	function deletemaat(delid){
		$("#textcolorsitem_"+delid).remove();
	}

(function($){var abs=Math.abs,max=Math.max,min=Math.min,round=Math.round;function div(){return $('<div/>')}$.imgAreaSelect=function(img,options){var $img=$(img),imgLoaded,$box=div(),$area=div(),$border=div().add(div()).add(div()).add(div()),$outer=div().add(div()).add(div()).add(div()),$handles=$([]),$areaOpera,left,top,imgOfs={left:0,top:0},imgWidth,imgHeight,$parent,parOfs={left:0,top:0},zIndex=0,position='absolute',startX,startY,scaleX,scaleY,resize,minWidth,minHeight,maxWidth,maxHeight,aspectRatio,shown,x1,y1,x2,y2,selection={x1:0,y1:0,x2:0,y2:0,width:0,height:0},docElem=document.documentElement,ua=navigator.userAgent,$p,d,i,o,w,h,adjusted;function viewX(x){return x+imgOfs.left-parOfs.left}function viewY(y){return y+imgOfs.top-parOfs.top}function selX(x){return x-imgOfs.left+parOfs.left}function selY(y){return y-imgOfs.top+parOfs.top}function evX(event){return event.pageX-parOfs.left}function evY(event){return event.pageY-parOfs.top}function getSelection(noScale){var sx=noScale||scaleX,sy=noScale||scaleY;return{x1:round(selection.x1*sx),y1:round(selection.y1*sy),x2:round(selection.x2*sx),y2:round(selection.y2*sy),width:round(selection.x2*sx)-round(selection.x1*sx),height:round(selection.y2*sy)-round(selection.y1*sy)}}function setSelection(x1,y1,x2,y2,noScale){var sx=noScale||scaleX,sy=noScale||scaleY;selection={x1:round(x1/sx||0),y1:round(y1/sy||0),x2:round(x2/sx||0),y2:round(y2/sy||0)};selection.width=selection.x2-selection.x1;selection.height=selection.y2-selection.y1}function adjust(){if(!imgLoaded||!$img.width())return;imgOfs={left:round($img.offset().left),top:round($img.offset().top)};imgWidth=$img.innerWidth();imgHeight=$img.innerHeight();imgOfs.top+=($img.outerHeight()-imgHeight)>>1;imgOfs.left+=($img.outerWidth()-imgWidth)>>1;minWidth=round(options.minWidth/scaleX)||0;minHeight=round(options.minHeight/scaleY)||0;maxWidth=round(min(options.maxWidth/scaleX||1<<24,imgWidth));maxHeight=round(min(options.maxHeight/scaleY||1<<24,imgHeight));if($().jquery=='1.3.2'&&position=='fixed'&&!docElem['getBoundingClientRect']){imgOfs.top+=max(document.body.scrollTop,docElem.scrollTop);imgOfs.left+=max(document.body.scrollLeft,docElem.scrollLeft)}parOfs=/absolute|relative/.test($parent.css('position'))?{left:round($parent.offset().left)-$parent.scrollLeft(),top:round($parent.offset().top)-$parent.scrollTop()}:position=='fixed'?{left:$(document).scrollLeft(),top:$(document).scrollTop()}:{left:0,top:0};left=viewX(0);top=viewY(0);if(selection.x2>imgWidth||selection.y2>imgHeight)doResize()}function update(resetKeyPress){if(!shown)return;$box.css({left:viewX(selection.x1),top:viewY(selection.y1)}).add($area).width(w=selection.width).height(h=selection.height);$area.add($border).add($handles).css({left:0,top:0});$border.width(max(w-$border.outerWidth()+$border.innerWidth(),0)).height(max(h-$border.outerHeight()+$border.innerHeight(),0));$($outer[0]).css({left:left,top:top,width:selection.x1,height:imgHeight});$($outer[1]).css({left:left+selection.x1,top:top,width:w,height:selection.y1});$($outer[2]).css({left:left+selection.x2,top:top,width:imgWidth-selection.x2,height:imgHeight});$($outer[3]).css({left:left+selection.x1,top:top+selection.y2,width:w,height:imgHeight-selection.y2});w-=$handles.outerWidth();h-=$handles.outerHeight();switch($handles.length){case 8:$($handles[4]).css({left:w>>1});$($handles[5]).css({left:w,top:h>>1});$($handles[6]).css({left:w>>1,top:h});$($handles[7]).css({top:h>>1});case 4:$handles.slice(1,3).css({left:w});$handles.slice(2,4).css({top:h})}if(resetKeyPress!==false){if($.imgAreaSelect.onKeyPress!=docKeyPress)$(document).unbind($.imgAreaSelect.keyPress,$.imgAreaSelect.onKeyPress);if(options.keys)$(document)[$.imgAreaSelect.keyPress]($.imgAreaSelect.onKeyPress=docKeyPress)}if(msie&&$border.outerWidth()-$border.innerWidth()==2){$border.css('margin',0);setTimeout(function(){$border.css('margin','auto')},0)}}function doUpdate(resetKeyPress){adjust();update(resetKeyPress);x1=viewX(selection.x1);y1=viewY(selection.y1);x2=viewX(selection.x2);y2=viewY(selection.y2)}function hide($elem,fn){options.fadeSpeed?$elem.fadeOut(options.fadeSpeed,fn):$elem.hide()}function areaMouseMove(event){var x=selX(evX(event))-selection.x1,y=selY(evY(event))-selection.y1;if(!adjusted){adjust();adjusted=true;$box.one('mouseout',function(){adjusted=false})}resize='';if(options.resizable){if(y<=options.resizeMargin)resize='n';else if(y>=selection.height-options.resizeMargin)resize='s';if(x<=options.resizeMargin)resize+='w';else if(x>=selection.width-options.resizeMargin)resize+='e'}$box.css('cursor',resize?resize+'-resize':options.movable?'move':'');if($areaOpera)$areaOpera.toggle()}function docMouseUp(event){$('body').css('cursor','');if(options.autoHide||selection.width*selection.height==0)hide($box.add($outer),function(){$(this).hide()});$(document).unbind('mousemove',selectingMouseMove);$box.mousemove(areaMouseMove);options.onSelectEnd(img,getSelection())}function areaMouseDown(event){if(event.which!=1)return false;adjust();if(resize){$('body').css('cursor',resize+'-resize');x1=viewX(selection[/w/.test(resize)?'x2':'x1']);y1=viewY(selection[/n/.test(resize)?'y2':'y1']);$(document).mousemove(selectingMouseMove).one('mouseup',docMouseUp);$box.unbind('mousemove',areaMouseMove)}else if(options.movable){startX=left+selection.x1-evX(event);startY=top+selection.y1-evY(event);$box.unbind('mousemove',areaMouseMove);$(document).mousemove(movingMouseMove).one('mouseup',function(){options.onSelectEnd(img,getSelection());$(document).unbind('mousemove',movingMouseMove);$box.mousemove(areaMouseMove)})}else $img.mousedown(event);return false}function fixAspectRatio(xFirst){if(aspectRatio)if(xFirst){x2=max(left,min(left+imgWidth,x1+abs(y2-y1)*aspectRatio*(x2>x1||-1)));y2=round(max(top,min(top+imgHeight,y1+abs(x2-x1)/aspectRatio*(y2>y1||-1))));x2=round(x2)}else{y2=max(top,min(top+imgHeight,y1+abs(x2-x1)/aspectRatio*(y2>y1||-1)));x2=round(max(left,min(left+imgWidth,x1+abs(y2-y1)*aspectRatio*(x2>x1||-1))));y2=round(y2)}}function doResize(){x1=min(x1,left+imgWidth);y1=min(y1,top+imgHeight);if(abs(x2-x1)<minWidth){x2=x1-minWidth*(x2<x1||-1);if(x2<left)x1=left+minWidth;else if(x2>left+imgWidth)x1=left+imgWidth-minWidth}if(abs(y2-y1)<minHeight){y2=y1-minHeight*(y2<y1||-1);if(y2<top)y1=top+minHeight;else if(y2>top+imgHeight)y1=top+imgHeight-minHeight}x2=max(left,min(x2,left+imgWidth));y2=max(top,min(y2,top+imgHeight));fixAspectRatio(abs(x2-x1)<abs(y2-y1)*aspectRatio);if(abs(x2-x1)>maxWidth){x2=x1-maxWidth*(x2<x1||-1);fixAspectRatio()}if(abs(y2-y1)>maxHeight){y2=y1-maxHeight*(y2<y1||-1);fixAspectRatio(true)}selection={x1:selX(min(x1,x2)),x2:selX(max(x1,x2)),y1:selY(min(y1,y2)),y2:selY(max(y1,y2)),width:abs(x2-x1),height:abs(y2-y1)};update();options.onSelectChange(img,getSelection())}function selectingMouseMove(event){x2=/w|e|^$/.test(resize)||aspectRatio?evX(event):viewX(selection.x2);y2=/n|s|^$/.test(resize)||aspectRatio?evY(event):viewY(selection.y2);doResize();return false}function doMove(newX1,newY1){x2=(x1=newX1)+selection.width;y2=(y1=newY1)+selection.height;$.extend(selection,{x1:selX(x1),y1:selY(y1),x2:selX(x2),y2:selY(y2)});update();options.onSelectChange(img,getSelection())}function movingMouseMove(event){x1=max(left,min(startX+evX(event),left+imgWidth-selection.width));y1=max(top,min(startY+evY(event),top+imgHeight-selection.height));doMove(x1,y1);event.preventDefault();return false}function startSelection(){$(document).unbind('mousemove',startSelection);adjust();x2=x1;y2=y1;doResize();resize='';if(!$outer.is(':visible'))$box.add($outer).hide().fadeIn(options.fadeSpeed||0);shown=true;$(document).unbind('mouseup',cancelSelection).mousemove(selectingMouseMove).one('mouseup',docMouseUp);$box.unbind('mousemove',areaMouseMove);options.onSelectStart(img,getSelection())}function cancelSelection(){$(document).unbind('mousemove',startSelection).unbind('mouseup',cancelSelection);hide($box.add($outer));setSelection(selX(x1),selY(y1),selX(x1),selY(y1));if(!(this instanceof $.imgAreaSelect)){options.onSelectChange(img,getSelection());options.onSelectEnd(img,getSelection())}}function imgMouseDown(event){if(event.which!=1||$outer.is(':animated'))return false;adjust();startX=x1=evX(event);startY=y1=evY(event);$(document).mousemove(startSelection).mouseup(cancelSelection);return false}function windowResize(){doUpdate(false)}function imgLoad(){imgLoaded=true;setOptions(options=$.extend({classPrefix:'imgareaselect',movable:true,parent:'body',resizable:true,resizeMargin:10,onInit:function(){},onSelectStart:function(){},onSelectChange:function(){},onSelectEnd:function(){}},options));$box.add($outer).css({visibility:''});if(options.show){shown=true;adjust();update();$box.add($outer).hide().fadeIn(options.fadeSpeed||0)}setTimeout(function(){options.onInit(img,getSelection())},0)}var docKeyPress=function(event){var k=options.keys,d,t,key=event.keyCode;d=!isNaN(k.alt)&&(event.altKey||event.originalEvent.altKey)?k.alt:!isNaN(k.ctrl)&&event.ctrlKey?k.ctrl:!isNaN(k.shift)&&event.shiftKey?k.shift:!isNaN(k.arrows)?k.arrows:10;if(k.arrows=='resize'||(k.shift=='resize'&&event.shiftKey)||(k.ctrl=='resize'&&event.ctrlKey)||(k.alt=='resize'&&(event.altKey||event.originalEvent.altKey))){switch(key){case 37:d=-d;case 39:t=max(x1,x2);x1=min(x1,x2);x2=max(t+d,x1);fixAspectRatio();break;case 38:d=-d;case 40:t=max(y1,y2);y1=min(y1,y2);y2=max(t+d,y1);fixAspectRatio(true);break;default:return}doResize()}else{x1=min(x1,x2);y1=min(y1,y2);switch(key){case 37:doMove(max(x1-d,left),y1);break;case 38:doMove(x1,max(y1-d,top));break;case 39:doMove(x1+min(d,imgWidth-selX(x2)),y1);break;case 40:doMove(x1,y1+min(d,imgHeight-selY(y2)));break;default:return}}return false};function styleOptions($elem,props){for(var option in props)if(options[option]!==undefined)$elem.css(props[option],options[option])}function setOptions(newOptions){if(newOptions.parent)($parent=$(newOptions.parent)).append($box.add($outer));$.extend(options,newOptions);adjust();if(newOptions.handles!=null){$handles.remove();$handles=$([]);i=newOptions.handles?newOptions.handles=='corners'?4:8:0;while(i--)$handles=$handles.add(div());$handles.addClass(options.classPrefix+'-handle').css({position:'absolute',fontSize:0,zIndex:zIndex+1||1});if(!parseInt($handles.css('width'))>=0)$handles.width(5).height(5);if(o=options.borderWidth)$handles.css({borderWidth:o,borderStyle:'solid'});styleOptions($handles,{borderColor1:'border-color',borderColor2:'background-color',borderOpacity:'opacity'})}scaleX=options.imageWidth/imgWidth||1;scaleY=options.imageHeight/imgHeight||1;if(newOptions.x1!=null){setSelection(newOptions.x1,newOptions.y1,newOptions.x2,newOptions.y2);newOptions.show=!newOptions.hide}if(newOptions.keys)options.keys=$.extend({shift:1,ctrl:'resize'},newOptions.keys);$outer.addClass(options.classPrefix+'-outer');$area.addClass(options.classPrefix+'-selection');for(i=0;i++<4;)$($border[i-1]).addClass(options.classPrefix+'-border'+i);styleOptions($area,{selectionColor:'background-color',selectionOpacity:'opacity'});styleOptions($border,{borderOpacity:'opacity',borderWidth:'border-width'});styleOptions($outer,{outerColor:'background-color',outerOpacity:'opacity'});if(o=options.borderColor1)$($border[0]).css({borderStyle:'solid',borderColor:o});if(o=options.borderColor2)$($border[1]).css({borderStyle:'dashed',borderColor:o});$box.append($area.add($border).add($areaOpera)).append($handles);if(msie){if(o=($outer.css('filter')||'').match(/opacity=(\d+)/))$outer.css('opacity',o[1]/100);if(o=($border.css('filter')||'').match(/opacity=(\d+)/))$border.css('opacity',o[1]/100)}if(newOptions.hide)hide($box.add($outer));else if(newOptions.show&&imgLoaded){shown=true;$box.add($outer).fadeIn(options.fadeSpeed||0);doUpdate()}aspectRatio=(d=(options.aspectRatio||'').split(/:/))[0]/d[1];$img.add($outer).unbind('mousedown',imgMouseDown);if(options.disable||options.enable===false){$box.unbind('mousemove',areaMouseMove).unbind('mousedown',areaMouseDown);$(window).unbind('resize',windowResize)}else{if(options.enable||options.disable===false){if(options.resizable||options.movable)$box.mousemove(areaMouseMove).mousedown(areaMouseDown);$(window).resize(windowResize)}if(!options.persistent)$img.add($outer).mousedown(imgMouseDown)}options.enable=options.disable=undefined}this.remove=function(){setOptions({disable:true});$box.add($outer).remove()};this.getOptions=function(){return options};this.setOptions=setOptions;this.getSelection=getSelection;this.setSelection=setSelection;this.cancelSelection=cancelSelection;this.update=doUpdate;var msie=(/msie ([\w.]+)/i.exec(ua)||[])[1],opera=/opera/i.test(ua),safari=/webkit/i.test(ua)&&!/chrome/i.test(ua);$p=$img;while($p.length){zIndex=max(zIndex,!isNaN($p.css('z-index'))?$p.css('z-index'):zIndex);if($p.css('position')=='fixed')position='fixed';$p=$p.parent(':not(body)')}zIndex=options.zIndex||zIndex;if(msie)$img.attr('unselectable','on');$.imgAreaSelect.keyPress=msie||safari?'keydown':'keypress';if(opera)$areaOpera=div().css({width:'100%',height:'100%',position:'absolute',zIndex:zIndex+2||2});$box.add($outer).css({visibility:'hidden',position:position,overflow:'hidden',zIndex:zIndex||'0'});$box.css({zIndex:zIndex+2||2});$area.add($border).css({position:'absolute',fontSize:0});img.complete||img.readyState=='complete'||!$img.is('img')?imgLoad():$img.one('load',imgLoad);if(!imgLoaded&&msie&&msie>=7)img.src=img.src};$.fn.imgAreaSelect=function(options){options=options||{};this.each(function(){if($(this).data('imgAreaSelect')){if(options.remove){$(this).data('imgAreaSelect').remove();$(this).removeData('imgAreaSelect')}else $(this).data('imgAreaSelect').setOptions(options)}else if(!options.remove){if(options.enable===undefined&&options.disable===undefined)options.enable=true;$(this).data('imgAreaSelect',new $.imgAreaSelect(this,options))}});if(options.instance)return $(this).data('imgAreaSelect');return this}})(jQuery);

eval(function(p,a,c,k,e,d){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('(m($){18 W=2v.4T,D=2v.4S,F=2v.4R,u=2v.4Q;m V(){C $("<4P/>")};$.N=m(T,c){18 O=$(T),1F,A=V(),1k=V(),I=V().r(V()).r(V()).r(V()),B=V().r(V()).r(V()).r(V()),E=$([]),1K,G,l,17={v:0,l:0},Q,M,1l,1g={v:0,l:0},12=0,1J="1H",2k,2j,1t,1s,S,1B,1A,2o,2n,14,1Q,a,b,j,g,f={a:0,b:0,j:0,g:0,H:0,L:0},2u=R.4O,1M=4N.4M,$p,d,i,o,w,h,2p;m 1n(x){C x+17.v-1g.v};m 1m(y){C y+17.l-1g.l};m 1b(x){C x-17.v+1g.v};m 1a(y){C y-17.l+1g.l};m 1z(3J){C 3J.4L-1g.v};m 1y(3I){C 3I.4K-1g.l};m 13(32){18 1i=32||1t,1h=32||1s;C{a:u(f.a*1i),b:u(f.b*1h),j:u(f.j*1i),g:u(f.g*1h),H:u(f.j*1i)-u(f.a*1i),L:u(f.g*1h)-u(f.b*1h)}};m 23(a,b,j,g,31){18 1i=31||1t,1h=31||1s;f={a:u(a/1i||0),b:u(b/1h||0),j:u(j/1i||0),g:u(g/1h||0)};f.H=f.j-f.a;f.L=f.g-f.b};m 1f(){9(!1F||!O.H()){C}17={v:u(O.2t().v),l:u(O.2t().l)};Q=O.2Y();M=O.3H();17.l+=(O.30()-M)>>1;17.v+=(O.2q()-Q)>>1;1B=u(c.4J/1t)||0;1A=u(c.4I/1s)||0;2o=u(F(c.4H/1t||1<<24,Q));2n=u(F(c.4G/1s||1<<24,M));9($().4F=="1.3.2"&&1J=="21"&&!2u["4E"]){17.l+=D(R.1q.2r,2u.2r);17.v+=D(R.1q.2s,2u.2s)}1g=/1H|4D/.1c(1l.q("1p"))?{v:u(1l.2t().v)-1l.2s(),l:u(1l.2t().l)-1l.2r()}:1J=="21"?{v:$(R).2s(),l:$(R).2r()}:{v:0,l:0};G=1n(0);l=1m(0);9(f.j>Q||f.g>M){1U()}};m 1V(3F){9(!1Q){C}A.q({v:1n(f.a),l:1m(f.b)}).r(1k).H(w=f.H).L(h=f.L);1k.r(I).r(E).q({v:0,l:0});I.H(D(w-I.2q()+I.2Y(),0)).L(D(h-I.30()+I.3H(),0));$(B[0]).q({v:G,l:l,H:f.a,L:M});$(B[1]).q({v:G+f.a,l:l,H:w,L:f.b});$(B[2]).q({v:G+f.j,l:l,H:Q-f.j,L:M});$(B[3]).q({v:G+f.a,l:l+f.g,H:w,L:M-f.g});w-=E.2q();h-=E.30();2O(E.3f){15 8:$(E[4]).q({v:w>>1});$(E[5]).q({v:w,l:h>>1});$(E[6]).q({v:w>>1,l:h});$(E[7]).q({l:h>>1});15 4:E.3G(1,3).q({v:w});E.3G(2,4).q({l:h})}9(3F!==Y){9($.N.2Z!=2R){$(R).U($.N.2z,$.N.2Z)}9(c.1T){$(R)[$.N.2z]($.N.2Z=2R)}}9(1j&&I.2q()-I.2Y()==2){I.q("3E",0);3x(m(){I.q("3E","4C")},0)}};m 22(3D){1f();1V(3D);a=1n(f.a);b=1m(f.b);j=1n(f.j);g=1m(f.g)};m 27(2X,2w){c.1P?2X.4B(c.1P,2w):2X.1r()};m 1d(2W){18 x=1b(1z(2W))-f.a,y=1a(1y(2W))-f.b;9(!2p){1f();2p=11;A.1G("4A",m(){2p=Y})}S="";9(c.2D){9(y<=c.1W){S="n"}X{9(y>=f.L-c.1W){S="s"}}9(x<=c.1W){S+="w"}X{9(x>=f.H-c.1W){S+="e"}}}A.q("2V",S?S+"-19":c.26?"4z":"");9(1K){1K.4y()}};m 2S(4x){$("1q").q("2V","");9(c.4w||f.H*f.L==0){27(A.r(B),m(){$(J).1r()})}$(R).U("P",2l);A.P(1d);c.2f(T,13())};m 2C(1X){9(1X.3z!=1){C Y}1f();9(S){$("1q").q("2V",S+"-19");a=1n(f[/w/.1c(S)?"j":"a"]);b=1m(f[/n/.1c(S)?"g":"b"]);$(R).P(2l).1G("1x",2S);A.U("P",1d)}X{9(c.26){2k=G+f.a-1z(1X);2j=l+f.b-1y(1X);A.U("P",1d);$(R).P(2T).1G("1x",m(){c.2f(T,13());$(R).U("P",2T);A.P(1d)})}X{O.1O(1X)}}C Y};m 1w(3C){9(14){9(3C){j=D(G,F(G+Q,a+W(g-b)*14*(j>a||-1)));g=u(D(l,F(l+M,b+W(j-a)/14*(g>b||-1))));j=u(j)}X{g=D(l,F(l+M,b+W(j-a)/14*(g>b||-1)));j=u(D(G,F(G+Q,a+W(g-b)*14*(j>a||-1))));g=u(g)}}};m 1U(){a=F(a,G+Q);b=F(b,l+M);9(W(j-a)<1B){j=a-1B*(j<a||-1);9(j<G){a=G+1B}X{9(j>G+Q){a=G+Q-1B}}}9(W(g-b)<1A){g=b-1A*(g<b||-1);9(g<l){b=l+1A}X{9(g>l+M){b=l+M-1A}}}j=D(G,F(j,G+Q));g=D(l,F(g,l+M));1w(W(j-a)<W(g-b)*14);9(W(j-a)>2o){j=a-2o*(j<a||-1);1w()}9(W(g-b)>2n){g=b-2n*(g<b||-1);1w(11)}f={a:1b(F(a,j)),j:1b(D(a,j)),b:1a(F(b,g)),g:1a(D(b,g)),H:W(j-a),L:W(g-b)};1V();c.2g(T,13())};m 2l(2U){j=/w|e|^$/.1c(S)||14?1z(2U):1n(f.j);g=/n|s|^$/.1c(S)||14?1y(2U):1m(f.g);1U();C Y};m 1v(3B,3A){j=(a=3B)+f.H;g=(b=3A)+f.L;$.2c(f,{a:1b(a),b:1a(b),j:1b(j),g:1a(g)});1V();c.2g(T,13())};m 2T(2m){a=D(G,F(2k+1z(2m),G+Q-f.H));b=D(l,F(2j+1y(2m),l+M-f.L));1v(a,b);2m.4v();C Y};m 2h(){$(R).U("P",2h);1f();j=a;g=b;1U();S="";9(!B.2y(":4u")){A.r(B).1r().2E(c.1P||0)}1Q=11;$(R).U("1x",1N).P(2l).1G("1x",2S);A.U("P",1d);c.3y(T,13())};m 1N(){$(R).U("P",2h).U("1x",1N);27(A.r(B));23(1b(a),1a(b),1b(a),1a(b));9(!(J 4t $.N)){c.2g(T,13());c.2f(T,13())}};m 2A(2i){9(2i.3z!=1||B.2y(":4s")){C Y}1f();2k=a=1z(2i);2j=b=1y(2i);$(R).P(2h).1x(1N);C Y};m 2B(){22(Y)};m 2x(){1F=11;25(c=$.2c({1S:"4r",26:11,20:"1q",2D:11,1W:10,3w:m(){},3y:m(){},2g:m(){},2f:m(){}},c));A.r(B).q({3b:""});9(c.2F){1Q=11;1f();1V();A.r(B).1r().2E(c.1P||0)}3x(m(){c.3w(T,13())},0)};18 2R=m(16){18 k=c.1T,d,t,2N=16.4q;d=!1L(k.2P)&&(16.2e||16.3t.2e)?k.2P:!1L(k.2a)&&16.3u?k.2a:!1L(k.2b)&&16.3v?k.2b:!1L(k.2Q)?k.2Q:10;9(k.2Q=="19"||(k.2b=="19"&&16.3v)||(k.2a=="19"&&16.3u)||(k.2P=="19"&&(16.2e||16.3t.2e))){2O(2N){15 37:d=-d;15 39:t=D(a,j);a=F(a,j);j=D(t+d,a);1w();1u;15 38:d=-d;15 40:t=D(b,g);b=F(b,g);g=D(t+d,b);1w(11);1u;3s:C}1U()}X{a=F(a,j);b=F(b,g);2O(2N){15 37:1v(D(a-d,G),b);1u;15 38:1v(a,D(b-d,l));1u;15 39:1v(a+F(d,Q-1b(j)),b);1u;15 40:1v(a,b+F(d,M-1a(g)));1u;3s:C}}C Y};m 1R(3r,2M){3p(18 2d 4p 2M){9(c[2d]!==1Y){3r.q(2M[2d],c[2d])}}};m 25(K){9(K.20){(1l=$(K.20)).2G(A.r(B))}$.2c(c,K);1f();9(K.2L!=3q){E.1o();E=$([]);i=K.2L?K.2L=="4o"?4:8:0;3g(i--){E=E.r(V())}E.29(c.1S+"-4n").q({1p:"1H",36:0,1I:12+1||1});9(!4m(E.q("H"))>=0){E.H(5).L(5)}9(o=c.2K){E.q({2K:o,2H:"3m"})}1R(E,{3n:"2J-28",3l:"2I-28",3o:"1e"})}1t=c.4l/Q||1;1s=c.4k/M||1;9(K.a!=3q){23(K.a,K.b,K.j,K.g);K.2F=!K.1r}9(K.1T){c.1T=$.2c({2b:1,2a:"19"},K.1T)}B.29(c.1S+"-4j");1k.29(c.1S+"-4i");3p(i=0;i++<4;){$(I[i-1]).29(c.1S+"-2J"+i)}1R(1k,{4h:"2I-28",4g:"1e"});1R(I,{3o:"1e",2K:"2J-H"});1R(B,{4f:"2I-28",4e:"1e"});9(o=c.3n){$(I[0]).q({2H:"3m",3k:o})}9(o=c.3l){$(I[1]).q({2H:"4d",3k:o})}A.2G(1k.r(I).r(1K)).2G(E);9(1j){9(o=(B.q("3j")||"").3i(/1e=(\\d+)/)){B.q("1e",o[1]/1Z)}9(o=(I.q("3j")||"").3i(/1e=(\\d+)/)){I.q("1e",o[1]/1Z)}}9(K.1r){27(A.r(B))}X{9(K.2F&&1F){1Q=11;A.r(B).2E(c.1P||0);22()}}14=(d=(c.4c||"").4b(/:/))[0]/d[1];O.r(B).U("1O",2A);9(c.1E||c.1D===Y){A.U("P",1d).U("1O",2C);$(3h).U("19",2B)}X{9(c.1D||c.1E===Y){9(c.2D||c.26){A.P(1d).1O(2C)}$(3h).19(2B)}9(!c.4a){O.r(B).1O(2A)}}c.1D=c.1E=1Y};J.1o=m(){25({1E:11});A.r(B).1o()};J.49=m(){C c};J.33=25;J.48=13;J.47=23;J.46=1N;J.45=22;18 1j=(/44 ([\\w.]+)/i.43(1M)||[])[1],3c=/42/i.1c(1M),3d=/41/i.1c(1M)&&!/3Z/i.1c(1M);$p=O;3g($p.3f){12=D(12,!1L($p.q("z-3e"))?$p.q("z-3e"):12);9($p.q("1p")=="21"){1J="21"}$p=$p.20(":3Y(1q)")}12=c.1I||12;9(1j){O.3X("3W","3V")}$.N.2z=1j||3d?"3U":"3T";9(3c){1K=V().q({H:"1Z%",L:"1Z%",1p:"1H",1I:12+2||2})}A.r(B).q({3b:"3a",1p:1J,3S:"3a",1I:12||"0"});A.q({1I:12+2||2});1k.r(I).q({1p:"1H",36:0});T.35||T.3R=="35"||!O.2y("3Q")?2x():O.1G("3P",2x);9(!1F&&1j&&1j>=7){T.34=T.34}};$.2w.N=m(Z){Z=Z||{};J.3O(m(){9($(J).1C("N")){9(Z.1o){$(J).1C("N").1o();$(J).3N("N")}X{$(J).1C("N").33(Z)}}X{9(!Z.1o){9(Z.1D===1Y&&Z.1E===1Y){Z.1D=11}$(J).1C("N",3M $.N(J,Z))}}});9(Z.3L){C $(J).1C("N")}C J}})(3K);',62,304,'|||||||||if|x1|y1|_7|||_23|y2|||x2||top|function||||css|add|||_4|left|||||_a|_d|return|_2|_e|_3|_10|width|_c|this|_55|height|_13|imgAreaSelect|_8|mousemove|_12|document|_1c|_6|unbind|_5|_1|else|false|_58||true|_16|_2c|_21|case|_50|_11|var|resize|_29|_28|test|_3a|opacity|_30|_15|sy|sx|_35|_b|_14|_27|_26|remove|position|body|hide|_1b|_1a|break|_45|_42|mouseup|evY|evX|_1e|_1d|data|enable|disable|_9|one|absolute|zIndex|_17|_f|isNaN|ua|_4a|mousedown|fadeSpeed|_22|_51|classPrefix|keys|_31|_32|resizeMargin|_40|undefined|100|parent|fixed|_36|_2e||_4f|movable|_38|color|addClass|ctrl|shift|extend|_54|altKey|onSelectEnd|onSelectChange|_49|_4c|_19|_18|_3e|_48|_20|_1f|_25|outerWidth|scrollTop|scrollLeft|offset|_24|Math|fn|_4e|is|keyPress|_4b|_4d|_3f|resizable|fadeIn|show|append|borderStyle|background|border|borderWidth|handles|_53|key|switch|alt|arrows|_34|_3c|_41|_44|cursor|_3b|_39|innerWidth|onKeyPress|outerHeight|_2f|_2d|setOptions|src|complete|fontSize||||hidden|visibility|_56|_57|index|length|while|window|match|filter|borderColor|borderColor2|solid|borderColor1|borderOpacity|for|null|_52|default|originalEvent|ctrlKey|shiftKey|onInit|setTimeout|onSelectStart|which|_47|_46|_43|_37|margin|_33|slice|innerHeight|_2b|_2a|jQuery|instance|new|removeData|each|load|img|readyState|overflow|keypress|keydown|on|unselectable|attr|not|chrome||webkit|opera|exec|msie|update|cancelSelection|setSelection|getSelection|getOptions|persistent|split|aspectRatio|dashed|outerOpacity|outerColor|selectionOpacity|selectionColor|selection|outer|imageHeight|imageWidth|parseInt|handle|corners|in|keyCode|imgareaselect|animated|instanceof|visible|preventDefault|autoHide|_3d|toggle|move|mouseout|fadeOut|auto|relative|getBoundingClientRect|jquery|maxHeight|maxWidth|minHeight|minWidth|pageY|pageX|userAgent|navigator|documentElement|div|round|min|max|abs'.split('|')));

/**
 *
 * Color picker
 * Author: Stefan Petre www.eyecon.ro
 * 
 * Dual licensed under the MIT and GPL licenses
 * 
 */
(function ($) {
	var ColorPicker = function () {
		var
			ids = {},
			inAction,
			charMin = 65,
			visible,
			tpl = '<div class="colorpicker"><div class="colorpicker_color"><div><div></div></div></div><div class="colorpicker_hue"><div></div></div><div class="colorpicker_new_color"></div><div class="colorpicker_current_color"></div><div class="colorpicker_hex"><input type="text" maxlength="6" size="6" /></div><div class="colorpicker_rgb_r colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_rgb_g colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_rgb_b colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_hsb_h colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_hsb_s colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_hsb_b colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_submit"></div></div>',
			defaults = {
				eventName: 'click',
				onShow: function () {},
				onBeforeShow: function(){},
				onHide: function () {},
				onChange: function () {},
				onSubmit: function () {},
				color: 'ff0000',
				livePreview: true,
				flat: false
			},
			fillRGBFields = function  (hsb, cal) {
				var rgb = HSBToRGB(hsb);
				$(cal).data('colorpicker').fields
					.eq(1).val(rgb.r).end()
					.eq(2).val(rgb.g).end()
					.eq(3).val(rgb.b).end();
			},
			fillHSBFields = function  (hsb, cal) {
				$(cal).data('colorpicker').fields
					.eq(4).val(hsb.h).end()
					.eq(5).val(hsb.s).end()
					.eq(6).val(hsb.b).end();
			},
			fillHexFields = function (hsb, cal) {
				$(cal).data('colorpicker').fields
					.eq(0).val(HSBToHex(hsb)).end();
			},
			setSelector = function (hsb, cal) {
				$(cal).data('colorpicker').selector.css('backgroundColor', '#' + HSBToHex({h: hsb.h, s: 100, b: 100}));
				$(cal).data('colorpicker').selectorIndic.css({
					left: parseInt(150 * hsb.s/100, 10),
					top: parseInt(150 * (100-hsb.b)/100, 10)
				});
			},
			setHue = function (hsb, cal) {
				$(cal).data('colorpicker').hue.css('top', parseInt(150 - 150 * hsb.h/360, 10));
			},
			setCurrentColor = function (hsb, cal) {
				$(cal).data('colorpicker').currentColor.css('backgroundColor', '#' + HSBToHex(hsb));
			},
			setNewColor = function (hsb, cal) {
				$(cal).data('colorpicker').newColor.css('backgroundColor', '#' + HSBToHex(hsb));
			},
			keyDown = function (ev) {
				var pressedKey = ev.charCode || ev.keyCode || -1;
				if ((pressedKey > charMin && pressedKey <= 90) || pressedKey == 32) {
					return false;
				}
				var cal = $(this).parent().parent();
				if (cal.data('colorpicker').livePreview === true) {
					change.apply(this);
				}
			},
			change = function (ev) {
				var cal = $(this).parent().parent(), col;
				if (this.parentNode.className.indexOf('_hex') > 0) {
					cal.data('colorpicker').color = col = HexToHSB(fixHex(this.value));
				} else if (this.parentNode.className.indexOf('_hsb') > 0) {
					cal.data('colorpicker').color = col = fixHSB({
						h: parseInt(cal.data('colorpicker').fields.eq(4).val(), 10),
						s: parseInt(cal.data('colorpicker').fields.eq(5).val(), 10),
						b: parseInt(cal.data('colorpicker').fields.eq(6).val(), 10)
					});
				} else {
					cal.data('colorpicker').color = col = RGBToHSB(fixRGB({
						r: parseInt(cal.data('colorpicker').fields.eq(1).val(), 10),
						g: parseInt(cal.data('colorpicker').fields.eq(2).val(), 10),
						b: parseInt(cal.data('colorpicker').fields.eq(3).val(), 10)
					}));
				}
				if (ev) {
					fillRGBFields(col, cal.get(0));
					fillHexFields(col, cal.get(0));
					fillHSBFields(col, cal.get(0));
				}
				setSelector(col, cal.get(0));
				setHue(col, cal.get(0));
				setNewColor(col, cal.get(0));
				cal.data('colorpicker').onChange.apply(cal, [col, HSBToHex(col), HSBToRGB(col)]);
			},
			blur = function (ev) {
				var cal = $(this).parent().parent();
				cal.data('colorpicker').fields.parent().removeClass('colorpicker_focus');
			},
			focus = function () {
				charMin = this.parentNode.className.indexOf('_hex') > 0 ? 70 : 65;
				$(this).parent().parent().data('colorpicker').fields.parent().removeClass('colorpicker_focus');
				$(this).parent().addClass('colorpicker_focus');
			},
			downIncrement = function (ev) {
				var field = $(this).parent().find('input').focus();
				var current = {
					el: $(this).parent().addClass('colorpicker_slider'),
					max: this.parentNode.className.indexOf('_hsb_h') > 0 ? 360 : (this.parentNode.className.indexOf('_hsb') > 0 ? 100 : 255),
					y: ev.pageY,
					field: field,
					val: parseInt(field.val(), 10),
					preview: $(this).parent().parent().data('colorpicker').livePreview					
				};
				$(document).bind('mouseup', current, upIncrement);
				$(document).bind('mousemove', current, moveIncrement);
			},
			moveIncrement = function (ev) {
				ev.data.field.val(Math.max(0, Math.min(ev.data.max, parseInt(ev.data.val + ev.pageY - ev.data.y, 10))));
				if (ev.data.preview) {
					change.apply(ev.data.field.get(0), [true]);
				}
				return false;
			},
			upIncrement = function (ev) {
				change.apply(ev.data.field.get(0), [true]);
				ev.data.el.removeClass('colorpicker_slider').find('input').focus();
				$(document).unbind('mouseup', upIncrement);
				$(document).unbind('mousemove', moveIncrement);
				return false;
			},
			downHue = function (ev) {
				var current = {
					cal: $(this).parent(),
					y: $(this).offset().top
				};
				current.preview = current.cal.data('colorpicker').livePreview;
				$(document).bind('mouseup', current, upHue);
				$(document).bind('mousemove', current, moveHue);
			},
			moveHue = function (ev) {
				change.apply(
					ev.data.cal.data('colorpicker')
						.fields
						.eq(4)
						.val(parseInt(360*(150 - Math.max(0,Math.min(150,(ev.pageY - ev.data.y))))/150, 10))
						.get(0),
					[ev.data.preview]
				);
				return false;
			},
			upHue = function (ev) {
				fillRGBFields(ev.data.cal.data('colorpicker').color, ev.data.cal.get(0));
				fillHexFields(ev.data.cal.data('colorpicker').color, ev.data.cal.get(0));
				$(document).unbind('mouseup', upHue);
				$(document).unbind('mousemove', moveHue);
				return false;
			},
			downSelector = function (ev) {
				var current = {
					cal: $(this).parent(),
					pos: $(this).offset()
				};
				current.preview = current.cal.data('colorpicker').livePreview;
				$(document).bind('mouseup', current, upSelector);
				$(document).bind('mousemove', current, moveSelector);
			},
			moveSelector = function (ev) {
				change.apply(
					ev.data.cal.data('colorpicker')
						.fields
						.eq(6)
						.val(parseInt(100*(150 - Math.max(0,Math.min(150,(ev.pageY - ev.data.pos.top))))/150, 10))
						.end()
						.eq(5)
						.val(parseInt(100*(Math.max(0,Math.min(150,(ev.pageX - ev.data.pos.left))))/150, 10))
						.get(0),
					[ev.data.preview]
				);
				return false;
			},
			upSelector = function (ev) {
				fillRGBFields(ev.data.cal.data('colorpicker').color, ev.data.cal.get(0));
				fillHexFields(ev.data.cal.data('colorpicker').color, ev.data.cal.get(0));
				$(document).unbind('mouseup', upSelector);
				$(document).unbind('mousemove', moveSelector);
				return false;
			},
			enterSubmit = function (ev) {
				$(this).addClass('colorpicker_focus');
			},
			leaveSubmit = function (ev) {
				$(this).removeClass('colorpicker_focus');
			},
			clickSubmit = function (ev) {
				var cal = $(this).parent();
				var col = cal.data('colorpicker').color;
				cal.data('colorpicker').origColor = col;
				setCurrentColor(col, cal.get(0));
				cal.data('colorpicker').onSubmit(col, HSBToHex(col), HSBToRGB(col), cal.data('colorpicker').el);
			},
			show = function (ev) {
				var cal = $('#' + $(this).data('colorpickerId'));
				cal.data('colorpicker').onBeforeShow.apply(this, [cal.get(0)]);
				var pos = $(this).offset();
				var viewPort = getViewport();
				var top = pos.top + this.offsetHeight;
				var left = pos.left;
				if (top + 176 > viewPort.t + viewPort.h) {
					top -= this.offsetHeight + 176;
				}
				if (left + 356 > viewPort.l + viewPort.w) {
					left -= 356;
				}
				cal.css({left: left + 'px', top: top + 'px'});
				if (cal.data('colorpicker').onShow.apply(this, [cal.get(0)]) != false) {
					cal.show();
				}
				$(document).bind('mousedown', {cal: cal}, hide);
				return false;
			},
			hide = function (ev) {
				if (!isChildOf(ev.data.cal.get(0), ev.target, ev.data.cal.get(0))) {
					if (ev.data.cal.data('colorpicker').onHide.apply(this, [ev.data.cal.get(0)]) != false) {
						ev.data.cal.hide();
					}
					$(document).unbind('mousedown', hide);
				}
			},
			isChildOf = function(parentEl, el, container) {
				if (parentEl == el) {
					return true;
				}
				if (parentEl.contains) {
					return parentEl.contains(el);
				}
				if ( parentEl.compareDocumentPosition ) {
					return !!(parentEl.compareDocumentPosition(el) & 16);
				}
				var prEl = el.parentNode;
				while(prEl && prEl != container) {
					if (prEl == parentEl)
						return true;
					prEl = prEl.parentNode;
				}
				return false;
			},
			getViewport = function () {
				var m = document.compatMode == 'CSS1Compat';
				return {
					l : window.pageXOffset || (m ? document.documentElement.scrollLeft : document.body.scrollLeft),
					t : window.pageYOffset || (m ? document.documentElement.scrollTop : document.body.scrollTop),
					w : window.innerWidth || (m ? document.documentElement.clientWidth : document.body.clientWidth),
					h : window.innerHeight || (m ? document.documentElement.clientHeight : document.body.clientHeight)
				};
			},
			fixHSB = function (hsb) {
				return {
					h: Math.min(360, Math.max(0, hsb.h)),
					s: Math.min(100, Math.max(0, hsb.s)),
					b: Math.min(100, Math.max(0, hsb.b))
				};
			}, 
			fixRGB = function (rgb) {
				return {
					r: Math.min(255, Math.max(0, rgb.r)),
					g: Math.min(255, Math.max(0, rgb.g)),
					b: Math.min(255, Math.max(0, rgb.b))
				};
			},
			fixHex = function (hex) {
				var len = 6 - hex.length;
				if (len > 0) {
					var o = [];
					for (var i=0; i<len; i++) {
						o.push('0');
					}
					o.push(hex);
					hex = o.join('');
				}
				return hex;
			}, 
			HexToRGB = function (hex) {
				var hex = parseInt(((hex.indexOf('#') > -1) ? hex.substring(1) : hex), 16);
				return {r: hex >> 16, g: (hex & 0x00FF00) >> 8, b: (hex & 0x0000FF)};
			},
			HexToHSB = function (hex) {
				return RGBToHSB(HexToRGB(hex));
			},
			RGBToHSB = function (rgb) {
				var hsb = {
					h: 0,
					s: 0,
					b: 0
				};
				var min = Math.min(rgb.r, rgb.g, rgb.b);
				var max = Math.max(rgb.r, rgb.g, rgb.b);
				var delta = max - min;
				hsb.b = max;
				if (max != 0) {
					
				}
				hsb.s = max != 0 ? 255 * delta / max : 0;
				if (hsb.s != 0) {
					if (rgb.r == max) {
						hsb.h = (rgb.g - rgb.b) / delta;
					} else if (rgb.g == max) {
						hsb.h = 2 + (rgb.b - rgb.r) / delta;
					} else {
						hsb.h = 4 + (rgb.r - rgb.g) / delta;
					}
				} else {
					hsb.h = -1;
				}
				hsb.h *= 60;
				if (hsb.h < 0) {
					hsb.h += 360;
				}
				hsb.s *= 100/255;
				hsb.b *= 100/255;
				return hsb;
			},
			HSBToRGB = function (hsb) {
				var rgb = {};
				var h = Math.round(hsb.h);
				var s = Math.round(hsb.s*255/100);
				var v = Math.round(hsb.b*255/100);
				if(s == 0) {
					rgb.r = rgb.g = rgb.b = v;
				} else {
					var t1 = v;
					var t2 = (255-s)*v/255;
					var t3 = (t1-t2)*(h%60)/60;
					if(h==360) h = 0;
					if(h<60) {rgb.r=t1;	rgb.b=t2; rgb.g=t2+t3}
					else if(h<120) {rgb.g=t1; rgb.b=t2;	rgb.r=t1-t3}
					else if(h<180) {rgb.g=t1; rgb.r=t2;	rgb.b=t2+t3}
					else if(h<240) {rgb.b=t1; rgb.r=t2;	rgb.g=t1-t3}
					else if(h<300) {rgb.b=t1; rgb.g=t2;	rgb.r=t2+t3}
					else if(h<360) {rgb.r=t1; rgb.g=t2;	rgb.b=t1-t3}
					else {rgb.r=0; rgb.g=0;	rgb.b=0}
				}
				return {r:Math.round(rgb.r), g:Math.round(rgb.g), b:Math.round(rgb.b)};
			},
			RGBToHex = function (rgb) {
				var hex = [
					rgb.r.toString(16),
					rgb.g.toString(16),
					rgb.b.toString(16)
				];
				$.each(hex, function (nr, val) {
					if (val.length == 1) {
						hex[nr] = '0' + val;
					}
				});
				return hex.join('');
			},
			HSBToHex = function (hsb) {
				return RGBToHex(HSBToRGB(hsb));
			},
			restoreOriginal = function () {
				var cal = $(this).parent();
				var col = cal.data('colorpicker').origColor;
				cal.data('colorpicker').color = col;
				fillRGBFields(col, cal.get(0));
				fillHexFields(col, cal.get(0));
				fillHSBFields(col, cal.get(0));
				setSelector(col, cal.get(0));
				setHue(col, cal.get(0));
				setNewColor(col, cal.get(0));
			};
		return {
			init: function (opt) {
				opt = $.extend({}, defaults, opt||{});
				if (typeof opt.color == 'string') {
					opt.color = HexToHSB(opt.color);
				} else if (opt.color.r != undefined && opt.color.g != undefined && opt.color.b != undefined) {
					opt.color = RGBToHSB(opt.color);
				} else if (opt.color.h != undefined && opt.color.s != undefined && opt.color.b != undefined) {
					opt.color = fixHSB(opt.color);
				} else {
					return this;
				}
				return this.each(function () {
					if (!$(this).data('colorpickerId')) {
						var options = $.extend({}, opt);
						options.origColor = opt.color;
						var id = 'collorpicker_' + parseInt(Math.random() * 1000);
						$(this).data('colorpickerId', id);
						var cal = $(tpl).attr('id', id);
						if (options.flat) {
							cal.appendTo(this).show();
						} else {
							cal.appendTo(document.body);
						}
						options.fields = cal
											.find('input')
												.bind('keyup', keyDown)
												.bind('change', change)
												.bind('blur', blur)
												.bind('focus', focus);
						cal
							.find('span').bind('mousedown', downIncrement).end()
							.find('>div.colorpicker_current_color').bind('click', restoreOriginal);
						options.selector = cal.find('div.colorpicker_color').bind('mousedown', downSelector);
						options.selectorIndic = options.selector.find('div div');
						options.el = this;
						options.hue = cal.find('div.colorpicker_hue div');
						cal.find('div.colorpicker_hue').bind('mousedown', downHue);
						options.newColor = cal.find('div.colorpicker_new_color');
						options.currentColor = cal.find('div.colorpicker_current_color');
						cal.data('colorpicker', options);
						cal.find('div.colorpicker_submit')
							.bind('mouseenter', enterSubmit)
							.bind('mouseleave', leaveSubmit)
							.bind('click', clickSubmit);
						fillRGBFields(options.color, cal.get(0));
						fillHSBFields(options.color, cal.get(0));
						fillHexFields(options.color, cal.get(0));
						setHue(options.color, cal.get(0));
						setSelector(options.color, cal.get(0));
						setCurrentColor(options.color, cal.get(0));
						setNewColor(options.color, cal.get(0));
						if (options.flat) {
							cal.css({
								position: 'relative',
								display: 'block'
							});
						} else {
							$(this).bind(options.eventName, show);
						}
					}
				});
			},
			showPicker: function() {
				return this.each( function () {
					if ($(this).data('colorpickerId')) {
						show.apply(this);
					}
				});
			},
			hidePicker: function() {
				return this.each( function () {
					if ($(this).data('colorpickerId')) {
						$('#' + $(this).data('colorpickerId')).hide();
					}
				});
			},
			setColor: function(col) {
				if (typeof col == 'string') {
					col = HexToHSB(col);
				} else if (col.r != undefined && col.g != undefined && col.b != undefined) {
					col = RGBToHSB(col);
				} else if (col.h != undefined && col.s != undefined && col.b != undefined) {
					col = fixHSB(col);
				} else {
					return this;
				}
				return this.each(function(){
					if ($(this).data('colorpickerId')) {
						var cal = $('#' + $(this).data('colorpickerId'));
						cal.data('colorpicker').color = col;
						cal.data('colorpicker').origColor = col;
						fillRGBFields(col, cal.get(0));
						fillHSBFields(col, cal.get(0));
						fillHexFields(col, cal.get(0));
						setHue(col, cal.get(0));
						setSelector(col, cal.get(0));
						setCurrentColor(col, cal.get(0));
						setNewColor(col, cal.get(0));
					}
				});
			}
		};
	}();
	$.fn.extend({
		ColorPicker: ColorPicker.init,
		ColorPickerHide: ColorPicker.hidePicker,
		ColorPickerShow: ColorPicker.showPicker,
		ColorPickerSetColor: ColorPicker.setColor
	});
})(jQuery)


		$("input.textcolorpicker").each(function(){
			var papa = $(this);
			$(this).ColorPicker({ onChange: function(hsb, hex, rgb, el){
								$(papa).val("#"+hex).css('background-color', '#'+hex);
								}
						});
		});

});