/*
 * colorPicker, JavaScript Color Picker (one-file version)
 * http://dematte.at/colorPicker
 *
 * Copyright 2011 by Peter Dematté
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * @version 0.91
 * @created 2010-11-03
 * @updated 2011-07-13
 * Date: Mon Aug 15 12:02:56 2011 -0100
*/
function colorPicker(e,mode,size,allowResize,allowClose,allowDrag,expColor,expHEX,offsetX,offsetY,orientation,parentObj,parentXY,color,difPad,rSpeed,cookieLife,docBody){
	// attribute definitions
	var	cP = colorPicker, // for now to make code shorter
	mode = mode||cP.mode||'B', // ['H', 'S', 'V', 'R', 'G', 'B'] initial colorMode
	size = size||cP.size||4, //[1, 2, 3, 4] initial size from XXS to L
	allowResize = allowResize != undefined?allowResize:cP.allowResize != undefined?cP.allowResize:true, //[true, false] switch to allow/disallow resizing of app
	allowClose = allowClose != undefined?allowClose:cP.allowClose != undefined?cP.allowClose:true, //[true, false] switch to allow/disallow closing of app with 'x' in right top corner
	allowDrag = allowDrag != undefined?allowDrag:cP.allowDrag != undefined?cP.allowDrag:true, //[true, false] switch to allow/disallow dragging app
	expColor = expColor != undefined?expColor:cP.expColor != undefined?cP.expColor:true, //[true, false] switch for exporting colors to background of initial object
	expHEX = expHEX != undefined?expHEX:cP.expHEX != undefined?cP.expHEX:true, //[true, false] switch for exporting color-value to initial object (as value or text)
	offsetX = offsetX||cP.offsetX||0, //[int] x-offset in pixels relative to initial object
	offsetY = offsetY||cP.offsetY||3, // [int] y-offset in pixels relative to initial object
	orientation = cP.orientation||['bottom','left'], //['left||right','bottom||top'] initial xy-position relative to initial object
	parentObj = parentObj||cP.parentObj||null, // [HTMLObject] initial parent of colorPicker
	parentXY = parentXY||cP.parentXY||'', // [right:12px;top:12px] initial style (coordinates,...)
	color = color||cP.objs||[204,0,0], // initial color in common web-formats
	difPad = difPad||cP.difPad||2, // LR padding/border/left of contrast / color difference bar
	rSpeed = rSpeed!=undefined?rSpeed:cP.rSpeed!= undefined?cP.rSpeed:15, // rendering speed; 0 renders right after mousemove
	cookieLife = cookieLife||cP.cookieLife||0, // rendering speed; 0 renders right after mousemove
	docBody = docBody||cP.docBody||document, // the element the dragEvent should be attached to [document || document.body]
	// parentDepth = parentDepth||cP.parentDepth||0,

	// private vars
	cP = colorPicker.cP, // collection of parts -> recycling var
	cCtr,crCtr, // current contrast, current right cursor contrast
	cPRender, // setIterval
	cPM, // picker mode [HSB or RGB]
	sX=1,sZ=1, xyzCorr, // scale for S/XS and XXS
	x,y,z, // coords for RGB color dependent on CP.mode
	// HX = ['hsv','xyz'].concat('001011120102100111001202101102000112121011101211101112'.split('')), // wicked pattern
	difWidth, // width of contr. / color diff bar
	iPhone = navigator.userAgent.toLowerCase().search(/(iphone|mobi)/) !== -1,
	IE = document.createStyleSheet && document.getElementById,

	CP=(!cP)?function(obj) { // building the colorPicker / setting up events and methodes
		var scripts = document.getElementsByTagName('script'),
		    div = document.createElement('div'),
		    testPix = document.createElement('img'),
		    newCSS = document.createElement('style'),
		    IE8 = IE && document.querySelectorAll, IE5 = IE && !document.compatMode, IE6 = IE && !IE5  && !window.XMLHttpRequest,
		    cPDir, _IE, _nonIE, CSSTmp, filter,
		    HTMLTxt='<div class="cPSkin"><input class="cPdummy" type="" /><div class="cPSkinC01"></div><div class="cPSkinC02"></div><div class="cPSkinC03"></div><div class="cPSkinC04"></div><div class="cPSkinS01"></div><div class="cPSkinS02"></div><div class="cPSlides"><div class="cPSL1"></div><span class="cPSL2"></span><span class="cPSL3"></span><div class="cPSLC"></div><span class="cPSL4"></span><span class="cPSR1"></span><span class="cPSR2"></span><div class="cPSR3"></div><div class="cPSRCL"></div><div class="cPSRCR"></div><span class="cPSR5"></span></div><div class="cPMemory"><div class="cPM1"></div><div class="cPM2"></div><div class="cPM3 ext"></div><div class="cPM4"></div><div class="cPM5"></div><div class="cPM6"></div><div class="cPM7 ext"></div><div class="cPM8"></div><div class="cPM9"></div><div class="cPM0"></div></div><div class="cPPanel"><div class="cPHSB"><div class="cPBLH bUp">H</div><input type="text" name="cPIH" value="0" maxlength="3" /><div class="cPBRH noB">&deg;</div><div class="cPBLS bUp">S</div><input type="text" name="cPIS" value="0" maxlength="3" /><div class="cPBRS noB">%</div><div class="cPBLV bUp">B</div><input type="text" name="cPIV" value="0" maxlength="3" /><div class="cPBRV noB">%</div></div><div class="cPRGB"><div class="cPBLR bUp">R</div><input type="text" name="cPIR" value="0" maxlength="3" /><div class="cPBRR bDown">&nbsp;</div><div class="cPBLG bUp">G</div><input type="text" name="cPIG" value="0" maxlength="3" /><div class="cPBRG bDown">&nbsp;</div><div class="cPBLB bDown">B</div><input type="text" name="cPIB" value="0" maxlength="3" /><div class="cPBRB bDown">&nbsp;</div></div><div class="cPCMYK"><div class="cPBLC noB">C</div><input type="text" name="cPIC" value="0" readonly="readonly" /><div class="cPBRC noB">%</div><div class="cPBLM noB">M</div><input type="text" name="cPIM" value="0" readonly="readonly" /><div class="cPBRM noB">%</div><div class="cPBLY noB">Y</div><input type="text" name="cPIY" value="0" readonly="readonly" /><div class="cPBRY noB">%</div><div class="cPBLK noB">K</div><input type="text" name="cPIK" value="100" readonly="readonly" /><div class="cPBRK noB">%</div></div><div class="cPHEX"><div class="cPBLX noB">#</div><input type="text" name="cPIX" value="000000" maxlength="6" /><div class="cPBRX bUp">W</div></div><div class="cPCTRT"></div><div class="cPCD"></div><div class="cPControl"><div class="cPCB1 bUp"></div><div class="cPCB2 bUp"></div><div class="cPCB3 bUp">RES</div><div class="cPCB4 bUp">SAVE</div></div></div><div class="cPClose"></div><div class="cPResize"></div><div class="cPResizer"><div class="cPOP30"></div></div></div>',
		    CSSTxt = '.cPSkin{position:absolute;width:407px;height:302px;text-align:left}.cPSkinC01,.cPSkinC02,.cPSkinC03,.cPSkinC04{position:absolute;width:8px;height:8px;background:url(_icons.png) right top}.cPSkinC02{right:0}.cPSkinC03{bottom:0}.cPSkinC04{bottom:0;right:0}.cPSkinS01,.cPSkinS02{position:absolute;width:100%;height:100%;background-color:#444}.cPSkinS01{left:4px;width:399px}.cPSkinS02{top:4px;height:294px}.cPSlides{position:absolute;left:9px;top:9px;width:284px;height:256px;overflow:hidden}.cPSL2R,.cPSL3R,.cPSL2G,.cPSL3G,.cPSL2B,.cPSL3B,.cPSL1H,.cPSL2H,.cPSL3H,.cPSL1S,.cPSL2S,.cPSL3S,.cPSL1V,.cPSL2V,.cPSL3V{position:absolute;width:256px;height:256px}.cPSL2R,.cPSL3R,.cPSL2G,.cPSL3G,.cPSL2B,.cPSL3B{background:url(_patches.png)}.cPSLCB,.cPSLCW{position:absolute;width:11px;height:11px;font-size:0px;background:url(_icons.png)}.cPSLCW{background-position:0 31px}.cPSLCB{background-position:0 11px}.cPSL4{position:absolute;width:256px;height:256px;cursor:crosshair}.cPSL4NC{cursor:url(_blank.cur),crosshair}.cPSR1R,.cPSR2R,.cPSR3R,.cPSR4R,.cPSR1G,.cPSR2G,.cPSR3G,.cPSR4G,.cPSR1B,.cPSR2B,.cPSR3B,.cPSR4B,.cPSR3H,.cPSR1S,.cPSR2S,.cPSR1V,.cPSR2V,.cPSR4V{position:absolute;right:0px;width:28px;height:256px;background:url(_vertical.png)}.cPSR4S,.cPSR5{position:absolute;right:0;width:28px;height:256px}.cPSRCLB,.cPSRCRB,.cPSRCLW,.cPSRCRW{position:absolute;width:4px;height:7px;font-size:0px;right:0;background:url(_icons.png)}.cPSRCLB,.cPSRCLW{right:24px}.cPSRCLW{background-position:-15px 0}.cPSRCLB{background-position:-26px 0}.cPSRCRW{background-position:0 0}.cPSRCRB{background-position:-11px 0}.cPMemory{position:absolute;left:9px;bottom:9px;width:285px;height:28px;display:inline-block;vertical-align:top}.cPM1,.cPM2,.cPM3,.cPM4,.cPM5,.cPM6,.cPM7,.cPM8,.cPM9,.cPM0{width:28px;height:27px;float:left;background-color:#000;margin-right:1px;margin-top:1px}.cPM1,.cPM3,.cPM5,.cPM7,.cPM9{width:27px}.cPM0{height:28px;margin-top:0;background:#000 url(_icons.png) no-repeat 9px -7px}.cPM0B{background-position:9px -27px}.cPPanel{position:absolute;width:94px;height:282px;top:9px;right:9px!important;right:8px;border:1px solid #222;border-right:1px solid #555;border-bottom:1px solid #555;font:normal normal normal 12px/11px "Courier New",Courier,mono;color:#ddd;background-color:#333}.cPHSB,.cPRGB,.cPCMYK,.cPHEX{border-top:1px solid #444;border-bottom:1px solid #222;display:inline-block;vertical-align:top;padding:2px 0 4px; margin:0px 4px}.cPHSB{border-top:none}.cPHEX{border-bottom:0}.cPPanel input{padding:1px;border:1px solid #222;background-color:#333;float:left;font:normal normal normal 12px/10px "Courier New",Courier,mono;color:#ccc;border-right-color:#555;border-bottom-color:#555;line-height:12px;width:44px;height:12px;margin:2px 2px 0}.cPHSB div,.cPRGB div,.cPCMYK div,.cPHEX div{width:15px;height:14px;border:1px solid #222;border-left-color:#555;border-top-color:#555;float:left;text-align:center;cursor:default;line-height:13px;margin:2px 0 0}.cPPanel .noB{border:1px solid #333}.cPPanel .bUp{border-left-color:#555;border-top-color:#555}.cPPanel .bDown{border:1px solid #555;border-left-color:#222;border-top-color:#222;background-color:#444}.cPCTRT,.cPCD{position:absolute;height:3px;font-size:0;overflow:hidden;left:0;bottom:53px;background-color:#CCC;border-right:1px solid #333;border-bottom:1px solid #222;border-left:1px solid #333;z-index:1}.cPCD{background-color:#C00}.CTRTop{background-color:transparent;z-index:2}.cPCD1{background-color:#FF9900}.cPCD2{background-color:#44DD00}.cPControl{position:absolute;bottom:0;left:0}.cPCB1,.cPCB2,.cPCB3,.cPCB4{width:45px;height:24px;float:left;border:1px solid #555;border-bottom-color:#222;border-right-color:#222;text-align:center;line-height:23px;cursor:default}.cPCB3,.cPCB4{height:25px}.cPClose{position:absolute;right:2px;top:2px;width:15px;height:15px;background:url(_icons.png) -30px 0}.cPResize{position:absolute;right:2px;bottom:2px;width:15px;height:15px;background:url(_icons.png) -45px 0;cursor:se-resize}.cPResizer{border:1px dashed #555;position:absolute;left:-1px;top:-1px;width:100%;height:100%;display:none;z-index:3}.cPResizer div{width:100%;height:100%;background-color:#bbb}.S{width:263px;height:159px}.S .cPSkinS01{width:255px}.S .cPSkinS02{height:151px}.S .cPSlides{width:143px;height:128px;left:8px;top:8px}.S .cPSL2R,.S .cPSL3R,.S .cPSL2G,.S .cPSL3G,.S .cPSL2B,.S .cPSL3B,.S .cPSL1H,.S .cPSL2H,.S .cPSL3H,.S .cPSL1S,.S .cPSL2S,.S .cPSL3S,.S .cPSL1V,.S .cPSL2V,.S .cPSL3V,.S .cPSL4{width:128px;height:128px}.S .cPSR1R,.S .cPSR2R,.S .cPSR3R,.S .cPSR4R,.S .cPSR1G,.S .cPSR2G,.S .cPSR3G,.S .cPSR4G,.S .cPSR1B,.S .cPSR2B,.S .cPSR3B,.S .cPSR4B,.S .cPSR3H,.S .cPSR1S,.S .cPSR2S,.S .cPSR4S,.S .cPSR1V,.S .cPSR2V,.S .cPSR4V,.S .cPSR5{width:15px;height:128px;right:0px!important;right:-1px}.S .cPSRCLB,.S .cPSRCLW{right:12px!important;right:11px;width:3px;background-position:-27px 0}.S .cPSRCLW{background-position:-16px 0}.S .cPSRCRB,.S .cPSRCRW{right:-1px!important;right:-2px}.S .cPMemory{height:15px;width:144px;bottom:8px;left:8px}.S .cPMemory div{height:14px;width:13px}.S .cPMemory .ext{width:14px}.S .cPMemory .cPM0{width:15px;height:15px;background-position:2px -14px}.S .cPMemory .cPM0B{width:15px;height:15px;background-position:2px -34px}.S .cPPanel{height:141px!important;height:142px;top:8px;right:8px!important;right:7px}.S .cPRGB{border-top:0;padding-top:2px}.S .cPCMYK{display:none}.S .cPClose{right:1px;top:1px}.S .cPResize{right:1px;bottom:1px}.XS{width:155px;height:155px}.XS .cPSkinS01{width:147px}.XS .cPSkinS02{height:147px}.XS .cPSlides{left:6px;top:6px}.XS .cPMemory{bottom:6px;left:6px}.XS .cPPanel{display:none}.XS .cPClose,.XS .cPResize{background-position:24px;right:-3px;bottom:-6px;width:9px;height:14px}.XS .cPClose{top:-2px}.XXS{width:151px;height:87px}.XXS .cPSkinS01{width:143px}.XXS .cPSkinS02{height:79px}.XXS .cPSlides{left:4px;top:4px;width:143px;height:64px}.XXS .cPSL1S,.XXS .cPSL2S,.XXS .cPSL3S,.XXS .cPSL1V,.XXS .cPSL2V,.XXS .cPSL3V,.XXS .cPSL4,.XXS .cPSR1S,.XXS .cPSR2S,.XXS .cPSR4S,.XXS .cPSR1V,.XXS .cPSR2V,.XXS .cPSR4V,.XXS .cPSR5{height:64px}.XXS .cPMemory{bottom:4px;left:4px}.cPSR1R,.cPSR1G,.cPSR1B,.cPSR2V,.cPSL3H,.cPSL2S{background:url(_vertical.png) 0 -2432px}.cPSR1R{background-color:#f00}.cPSR2R{background-position:0 -4480px}.cPSR3R{background-position:0 -2944px}.cPSR4R{background-position:0 -3202px}.cPSR1G{background-color:#0f0}.cPSR2G{background-position:0 -3968px}.cPSR3G{background-position:0 -4736px}.cPSR4G{background-position:0 -3712px}.cPSR1B{background-color:#00f}.cPSR2B{background-position:0 -4224px}.cPSR3B{background-position:0 -3456px}.cPSR4B{background-position:0 -2688px}.cPSL2R{background-position:-1664px 0}.cPSL3R{background-position:-896px 0}.cPSL2G,.S .cPSL2H{background-position:-640px 0}.cPSL3G{background-position:-384px 0}.cPSL2B{background-position:-1152px 0}.cPSL3B{background-position:-1408px 0}.cPSR3H{background-position:0 -1664px}.cPSR4S{background:#000 none}.cPSR4V,.cPSL3S{background:url(_vertical.png) 0 -2176px}.cPSL1H{background:none}.cPSL2H{background:url(_horizontal.png) 0 0}.cPSL1S,.cPSL1V{background:url(_horizontal.png) -256px 0}.cPSL2V,.cPSR2S{background:url(_vertical.png) 0 -1920px}.cPSL3V{background:#000}.S .cPSR1R,.S .cPSR2V,.S .cPSR1G,.S .cPSR1B,.S .cPSL3H,.S .cPSL2S{background-position:0 -1408px}.S .cPSR2R{background-position:0 -896px}.S .cPSR3R,.S .cPSL3B{background-position:0 -128px}.S .cPSR4R{background-position:0 -256px}.S .cPSR2G{background-position:0 -640px}.S .cPSR3G{background-position:0 -1024px}.S .cPSR4G{background-position:0 -512px}.S .cPSR2B{background-position:0 -768px}.S .cPSR3B{background-position:0 -384px}.S .cPSR4B,.S .cPSL3R{background-position:0 0}.S .cPSL2R{background-position:-128px -128px}.S .cPSL2G{background-position:-256px -128px}.S .cPSL3G{background-position:-256px 0}.S .cPSL2B{background-position:-128px 0}.S .cPSR3H{background-position:0 -1536px}.S .cPSR2S,.S .cPSL2V{background-position:0 -1152px}.S .cPSR4V,.S .cPSL3S{background-position:0 -1280px}.S .cPSL1S,.S .cPSL1V{background-position:-512px 0}.XXS .cPSR2S,.XXS .cPSL2V{background-position:0 -4992px}.XXS .cPSR2V,.XXS .cPSL2S{background-position:0 -5120px}.XXS .cPSR4V,.XXS .cPSL3S{background-position:0 -5056px}.cPhide{display:none}.cPdummy{position:absolute;left:4px;top:4px;width:10px}.cPinpDrag{background:url(_icons.png) no-repeat -26px -17px}.cPinpDragOn{background:url(_icons.png) no-repeat -26px -30px}',
		
		    _h = 'iVBORw0KGgoAAAANSUhEUgAA',
		    _blank = _h+'AAEAAAABCAYAAAAfFcSJAAAADUlEQVQI12P4//8/MwAI/wMBbrqo4gAAAABJRU5ErkJggg==',
		    _horizontal = _h+'AwAAAAABCAYAAABpNcm2AAAAhElEQVR4Xu2QwQrCQAxEnwuVUv3/L9UiLZsq5LQXhy1BD9nAMGGGhGQux6fwspQ8uCb+f/CyHEzAtYXQtB6/Eyuw0WJ3FrrQ4neGBzBCDUjkR/MF63n0/6HeHdxa9r7ftxkeOJ40vdC++mcO1DOvog7o9qc9PFJmTB11zl9XAEuMmvn/NznMxg7wvKXZAAAAAElFTkSuQmCC',
		    _vertical = _h+'AAEAABRACAYAAABIgKWdAAABmUlEQVR42u3c0U7qQBCA4b+jJSfC+z8pJIbCjhdVVGxNOdYWs//Nl2FnMyGbpbtb0lIyM6AgsgJNJlFAZB2cf7IeCS4EshZkZgYFRJYHnH+yIpmuv7IyboRlHchsXH9ltf1fZhJAyP/y4Bj8CKKUIrIsNE2TZCb4G/T6tzgAGfn0RMCWgN1YtJuryzNB7IE4wJfo9sRol66FYAfB9hJdfZyQmNDlXyFgf+HwbfTDLlsgdjAcjSZu7xLPEOwhOAxEo4kJXa4+dhDQErAZoJ3YtrmvKoUgjvCFbmLbjIkZq9BCsPnMUNuMiV+uEoWA4wDdxLYZE7NVaYHYfGaobcbEL1eJAsFxgG5i2+2Jeap8V/5y/uXDccTIaMHowTEwWjEiMlNEpB4A8n3/h6uBkfs/owqi/tZz/9dbf/0TEamOJt8eAhERqY10DESkSl4Pxm6ERaQ6skmCRESkNhrHQEQqJT3/ikjVh2BvhIpIdby+f8iNsIhUB5n5GMAQMZZYlvY+vsY1/auLz+czcTqd5Fb654/uZ5r9ufkH5Av1TLR7OwfhYAAAAABJRU5ErkJggg==',
		    _icons = _h+'AEgAAAAvCAQAAADqFUQuAAAB7klEQVRYw+2YP26DMBTGnxBdq66VKhaQ+kei3CBFxBwgA2MHVs6QU2RhYcwtWHIXDvL6gAA2DiQpEDtS/MmyebbwT8+fTRRAqITHFoSCXZxXM43hsBA8Tu/4K403io0g9Qu/CNLYqN5d4XAT2vWOAyeB8BwQww7ng3D8AaDYWB2a2OoQG+2qvSU5UhmpaS4D+pSywwMFKR8N0nbNk0vi+Cj/qq3U6zYrEGY6ApBf8GN+MWOGtj2wEudrAGccaDYPbTmcEki2snPBlvVOGU44ZSLQG+H8DOCMm/rf+h7dsqHsiB5iwrFnMAmIPXutQ2RTByM4DIfeOQ2I5CG7QM6gh2YHYq8erq/GWRKoytL6ChxneSD24l2JszQQsCevZ+JzWhqIFM2AMxFoCdWNiwlmmFNNqK8cKMQdRmijSTWifqgWyCUEiwtZ9OyqBEooK2IwophCoIw2SgzaFLsdAJVN9wOMmpy8I04yKXZDoA2WAl0yVOOU0tZD2p0yxfeQvGWKb2rZ1Kq/XdKx1/Lj+gB6AN050B61AtpjKW2AapwOqS4uJJBBTjWhPoDiizKEHURgg0k1on6oFsglBIv7T8iiZ1flliWUFbFEkCg0NfnG7gHZkCk89mRlswdkQq7SQxpkSARS7iHNTtkd3EOa3dR/zD8stDEYhk8AAAAASUVORK5CYII=',
		    _patches = _h+'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';
		
		// find right path for HTML and CSS // ????????????????
		for(var n=0; n<scripts.length; n++)	if (scripts[n].src.indexOf('colorPicker.js') !== -1) cPDir = scripts[n].src.substring(0,scripts[n].src.lastIndexOf("/")+1);
			// n=document.createElement('img'); n.src=cPDir; cPDir=n.src; alert(cPDir); // for IE<8 to get absolute path but it ia an http request!!!
		if (/^\//.exec(cPDir)) cPDir = location.href.split('/')[0]+'//'+location.href.split('/')[2]+cPDir;
		else if (!/:\/\//.exec(cPDir)) cPDir = location.href.substring(0,location.href.lastIndexOf("/")+1)+cPDir;

		// collect HTML elements
		div.innerHTML = HTMLTxt.replace(/src=\"/g,'src="'+cPDir);
		cP = colorPicker.cP = (parentObj||obj.parentNode).appendChild(div.getElementsByTagName('div')[0]);
		cP['cPSkins']=cP.style; cP.but=[];
		div = cP.all || cP.getElementsByTagName('*'); // div -> varRecycling : this happens often in my code!
		for(var n=0, nN, nB, m=0; n<div.length; n++, nN=null) { // collect references / buttons
			if (div[n].className) nN = div[n].className.replace(/(.*?)\s.*/,"$1");
			if (div[n].name) nN = div[n].name.replace(/(.*?)\s.*/,"$1");
			if (nN) {cP[nN]=div[n]; if (!div[n].name && div[n].className.search(/cP(B|P|Sl|d|Sk|RGB|HSB|CMYK|HEX|Mem|Cont|CB(3|4)|SL[2-3]|SR(1|3))/)) // this saves memory
				cP[nN+'s']=div[n].style; else if (nB = /cPB(.)(.)\s+b/.exec(div[n].className)) {cP.but[m]=[nN,nB[1],nB[2],m++]}}
		}

		// add CSS with opacity and import base64-images
		_IE = 'mhtml:'+cPDir+'IE.mht!'; _nonIE = 'data:image/png;base64,';
		if (IE) testPix.src=_IE+'_horizontal'; if (!IE || IE8 || testPix.height==1) CSSTxt = CSSTxt.replace(/url\(_[a-z]*\.png\);*/g,"") + '.cPSkinC01,.cPSkinC02,.cPSkinC03,.cPSkinC04,.cPSLCB,.cPSLCW,.cPSRCLB,.cPSRCRB,.cPSRCLW,.cPSRCRW,.cPM0,.cPClose,.cPResize,.cPinpDrag,.cPinpDragOn{background-image:url('+(IE&&!IE8?_IE+'_icons':_nonIE+_icons)+')}.cPSL2R,.cPSL3R,.cPSL2G,.cPSL3G,.cPSL2B,.cPSL3B{background-image:url('+(IE&&!IE8?_IE+'_patches':_nonIE+_patches)+')}.cPSR1R,.cPSR2R,.cPSR3R,.cPSR4R,.cPSR1G,.cPSR2G,.cPSR3G,.cPSR4G,.cPSR1B,.cPSR2B,.cPSR3B,.cPSR4B,.cPSR3H,.cPSR2V,.cPSL3H,.cPSL2S,.cPSR4V,.cPSL3S,.cPSL2V,.cPSR2S{background-image:url('+(IE&&!IE8?_IE+'_vertical':_nonIE+_vertical)+')}.cPSL2H,.cPSL1S,.cPSL1V{background-image:url('+(IE&&!IE8?_IE+'_horizontal':_nonIE+_horizontal)+')}';
		for(n=0, CSSTmp=[]; n<=100; n++) CSSTmp[n] = '.cPOP'+n+(IE?(IE8?'{-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity='+n+')";':'{')+'filter:alpha(opacity='+n+')}':'{opacity:.'+(n<=9?0:'')+n+'}');
		CSSTxt = CSSTxt.replace('_blank.cur',!IE5&&!IE6?(IE?_IE+'_IECur':_nonIE+_blank):'_blank.cur').replace(/url\(_/g,'url('+cPDir+'_')+CSSTmp.join('').replace(/\opacity:.100/,'opacity:1');
		if(!IE && document.compatMode == 'BackCompat') CSSTxt += '.cPPanel input{height:16px;width:48px}';
		newCSS.setAttribute('type','text/css');
		if (!newCSS.styleSheet) newCSS.appendChild(document.createTextNode(CSSTxt));
		document.getElementsByTagName('head')[0].appendChild(newCSS); CSSTmp = document.styleSheets[document.styleSheets.length-1]; // use CSSTmp for adding CSS later on
		if (newCSS.styleSheet) CSSTmp.cssText = CSSTxt; // IE5.5 compatible
		
		// some special style-grabbing to speed up contrast/color bar
		cP.CTRTop = (cP.CTRTop = /\.CTRTop\s*{(.*?)}/.exec(CSSTxt))? cP.CTRTop[1]: '';
		cP.cPCD1  = (cP.cPCD1  = /\.cPCD1\s*{(.*?)}/.exec(CSSTxt)) ? cP.cPCD1[1] : '';
		cP.cPCD2  = (cP.cPCD2  = /\.cPCD2\s*{(.*?)}/.exec(CSSTxt)) ? cP.cPCD2[1] : '';
		
		if (IE5 || IE6 || (IE && document.compatMode == 'BackCompat')) { // IE5.5 && IE6 -> PNG transparency and quirksMode patch: ~2.2kB
			CSSTxt = []; n=0; CSSTxt[n++] = '.cPClose,.cPResize,.cPSkinC01,.cPSkinC02,.cPSkinC03,.cPSkinC04,.cPinpDrag,.cPinpDragOn,.cPSRCLB,.cPSRCRB,.cPSRCLW,.cPSRCRW,.cPSLCB,.cPSLCW,.cPM0{background-image:url('+cPDir+'_icons.'+(!IE5 && !IE6?'png':'gif')+')}.cPSkinC03,.cPSkinC04{bottom:-7px}'+(IE8?'.cPSkinC02,.cPSkinC04{right:-1px}':'')+'.S{height:160px}.S .cPSlides{width:143px}.S .cPMemory{bottom:8px}.XS{height:156px}.XS .cPMemory{bottom:6px}.XXS{height:88px}.XXS .cPMemory{bottom:4px}';
			if (IE5 || document.compatMode == 'BackCompat') CSSTxt[n++] = '.cPPanel{width:96px;height:284px}.cPPanel input{height:16px;width:48px}.cPHSB div,.cPRGB div,.cPCMYK div,.cPHEX div{width:17px;height:16px}.cPCB1,.cPCB2,.cPCB3,.cPCB4{width:47px;height:26px}.cPCTRT,.cPCD{height:4px;margin-bottom:1px}.S .cPPanel{height:143px}.S .cPControl{bottom:-1px}.S .cPControl{bottom:-1px}.S.cPCTRT,.S.cPCD{bottom:51px}.S .cPResize{bottom:2px}';
			CSSTxt[n++] = '.cPSR1R,.cPSR1G,.cPSR1B,.cPSR2V,.cPSL3H,.cPSL2S{top:-2432px;height:5184px}.cPSR2S,.cPSL2V{top:-1920px;height:5184px}.cPSL2H{left:0;width:768px}.S .cPSR1R,.S .cPSR1G,.S .cPSR1B,.S .cPSR2V,.S .cPSL3H,.S .cPSL2S{top:-1408px;height:5184px}.S .cPSR2S,.S .cPSL2V{top:-1152px;height:5184px}.S .cPSL2H{left:-640px;width:768px}.XXS .cPSR2S,.XXS .cPSL2V{top:-4992px;height:5184px}.XXS .cPSR2V,.XXS .cPSL2S{top:-5120px;height:5184px}.cPSR1H,.cPSR2H{display:none}';
			filter = 'filter: progid:DXImageTransform.Microsoft.AlphaImageLoader (sizingMethod=\'scale\', src=\'';
			CSSTxt[n++] = '.cPSR1R,.cPSR1G,.cPSR1B,.cPSR2S,.cPSR2V,.cPSL3H,.cPSL2S,.cPSL2V{'+(testPix.height==1?_IE+'_vertical':filter+cPDir+'_vertical.png')+'\');background-image:none}.cPSL2H{'+(testPix.height==1?_IE+'_horizontal':filter+cPDir+'_horizontal.png')+'\');background-image:none}';
			CSSTmp.cssText += CSSTxt.join('');
			for(var sC = cP.getElementsByTagName('span'),nN,pix;0<sC.length;) {
				pix=document.createElement('img'); pix.className = sC[0].className;
				nN = pix.className.replace(/(.*?)\s.*/,"$1").replace('R4','R3'); 
				pix.src = cPDir+'_blank.gif'; sC[0].swapNode(pix); cP[nN]=pix; cP[nN+'s']=pix.style}
		}

		// add events; 100% event delegation
		cP.onmousedown = function(e){
			var e = e || window.event, obj = e.target || e.srcElement, xy = (obj == cP.cPSL4), origin, mousePos, nCN, oCN = cP.className, nB;
			if (docBody.funcCache) stopDrag(); // just in case somebody left the browser...
			if (!iPhone && (!obj.name || !obj.name.search(/PI[RGBHSVX]/))) unFocus(); // to get focus off input tags
			if ((!obj.className.search(/(cP(HSB|RGB|CMYK|HEX|Panel|Skin)|(.*?\snoB))/) ||
			   (obj.name && !obj.name.indexOf('cPI') && obj.readOnly)) && allowDrag) { // drag app
				mousePos = getMousePos(e,getOrigin(cP)), origin =  getOrigin(cP.parentNode);
				addEvent(docBody, 'mousemove', function(e){
					var mP = getMousePos(e,origin);
					if (IE && (!this.skip?this.skip=1:this.skip++)%2) return false; // delay for IE 
					cP.cPSkins.cssText = 'left:'+(mP[0]-mousePos[0])+'px;top:'+(mP[1]-mousePos[1])+'px';
					return false});
			} else if (obj==cP.cPResize && allowResize) { // resize app
				origin = getOrigin(cP);
				if (iPhone) {nCN = oCN == 'cPSkin'?' S':oCN == 'cPSkin S'?' S XS':oCN == 'cPSkin S XS'?' S XS XXS':''; resizeWin(nCN)}
				else addEvent(docBody, 'mousemove', function(e){
					var mousePos = getMousePos(e,origin);
					cP.cPResizers.cssText='display:block;width:'+((mousePos[0]<3?3:mousePos[0])+5)+'px;height:'+((mousePos[1]<3?3:mousePos[1])+5)+'px';
					nCN = (mousePos[1] < 87)?' S XS XXS':(mousePos[0] < 180)?' S XS':(mousePos[0] < 275 || mousePos[1] < 175)?' S':'';
					if (cP.className != 'cPSkin' + nCN) resizeWin(nCN);
					return false});
			} else if (!obj.className.search(/cPS[L|R]/)) { // add events to slides
				if (cP.WEBS1) cP.WEBS1=null; // kill the memory of webSmart/Save button
				origin = getOrigin(obj); doDrag(e,origin,xy);
				if (xy) cP.cPSL4.className = 'cPSL4 cPSL4NC';
				addEvent(docBody, 'mousemove', function(e){doDrag(e,origin,xy);return false});
				if (rSpeed) cPRender = setInterval(function(){doRender(xy)},rSpeed); else cPRender = true;
			} else if (nB = /cPCB(\d+).*?/.exec(obj.className)) {// 4 control buttons
				if (nB[1]==1) initCp(HSV2RGB(CP.hsv[0]+(CP.hsv[0]>127.5?-127.5:127.5),CP.hsv[1],CP.hsv[2],true)); // shift color 180°
				else if (nB[1]==2) initCp(X2RGB(CP.CB2Color)); // set saved color
				else if (nB[1]==3) {cP.cPCB2s.backgroundColor='rgb('+cP.cObj.color+')'; CP.CB2Color=cP.cObj.color; CP.iCtr = getBrightness(CP.CB2Color); doRender(true,true)} // reset color 
				else {cP.cPCB2s.backgroundColor='rgb('+CP.rgbRND+')'; CP.CB2Color=CP.rgbRND; initCp(CP.rgbRND);}// save color
				chBut(obj,false)
			} else if (nB = /cPB(.)(.)\s+b/.exec(obj.className)) { // all other buttons
				if(nB[1]=='L'){CP.mode=obj.className.substr(4,1); initCp(CP.rgb)} // change mode
				if(nB[1]=='R') { // buttons on right side
					if(nB[2]!='X') {nB = obj.className.substr(4,1); CP.modeRGB[nB]=!CP.modeRGB[nB]; chBut(obj,CP.modeRGB[nB]); setCookie('CP.modeRGB.'+nB,CP.modeRGB[nB]); initCp(CP.rgb)} // RGB-special buttons
					else {var r=CP.rgbRND[0], g=CP.rgbRND[1], b=CP.rgbRND[2], s=(r*(256*256)+g*256+b)%17?17:51, t=(s-1)/2; // WEB-Smart/Save button
						cP.WEBS1 = cP.WEBS1||CP.rgbRND; if (cP.cPBRX.firstChild.data == 'W') initCp(cP.WEBS1); // cP.WEBS1=null in chInput/SL|SR mousedown/init
						else {initCp([r+(r%s>t?s:0)-r%s, g+(g%s>t?s:0)-g%s, b+(b%s>t?s:0)-b%s])} chBut(obj,false)}}
			} else if (obj == cP.cPClose) {toggleCp(true); // close app
			} else if (nB = /cPM([0-9]).*/.exec(obj.className)) if (nB[1]!='0') initCp(X2RGB(obj.style.backgroundColor)); // memory squares
				else {var color = 'rgb('+CP.rgbRND+')', nB; // save color to memory squares
					for (var n=1; n<9; n++) {nB='cPM'+n+'s'; if (X2RGB(cP[nB].backgroundColor)+'' == CP.rgbRND+'') {
						cP['cPM'+n].color = color; cP[nB].backgroundColor = (getBrightness(X2RGB(color)) > 129)?'#333':'#CCC';
						cP.timeOut = setTimeout(function(){cP[nB].backgroundColor = cP['cPM'+n].color; cP.timeOut = null},200); return false}}
					if (!cP.timeOut) {for (n=9; n>1; n--) {nB='cPM'+n+'s'; cP[nB].backgroundColor = cP['cPM'+(n-1)+'s'].backgroundColor; setCookie('cP.'+nB,cP[nB].backgroundColor.replace(/,/g,'|'))}
					cP.cPM1s.backgroundColor = color; setCookie('cP.cPM1s',color.replace(/,/g,'|'))}}
			if (obj.name && obj.name.search(/[CMYKX]/) == -1) { // input fields as slides
				var nB = obj.name, sMax = nB.search(/[RGB]/)!=-1?255:nB.search(/[SV]/)!=-1?100:360,
				    pos = nB.search(/[RH]/)!=-1?0:nB.search(/[GS]/)!=-1?1:2, delay = true, oldValue = (sMax==255) ? CP.rgb[pos] : CP.hsv[pos]/255*sMax;
				obj.className = 'cPinpDrag'; cP.inp = obj; mousePos = sMax-getMousePos(e,[0,0,0,0])[1];
				addEvent(docBody, 'mousemove', function(e){
					var mP = (!delay?oldValue:0)+sMax-getMousePos(e,[0,0,0,0])[1]-mousePos;
					if (!delay) chInput(mP,sMax,pos,CP.rgb,CP.hsv);
					else if (Math.abs(mP) > 10) { // start it all
						delay = false; mousePos += mP; obj.className = 'cPinpDragOn'; unFocus(); 
						if (rSpeed) cPRender = setInterval(function(){doRender(true,true)},rSpeed); else cPRender = true;return false}});
			} else if (obj.name); else return false; // for all others
		};
		
		addEvent(docBody,'mouseup',stopDrag); 
		
		cP.onkeydown=function(e){ // input values + arrow keys // so much code for this little job... // maybe better if onkeypress and recode (iPhone??)??
			var e = e||window.event, obj = e.target||e.srcElement, obj=obj.nodeType==3?obj.parentNode:obj, code = e.which||e.keyCode, 
			    code=code>=96&&code<=105?code-48:code, chr=String.fromCharCode(code), vCHR={37:1,38:1,39:1,40:1,46:1,8:1,9:1,13:1,33:1,34:1}[code], // left, up, right, down, del, back, tab, enter, pageUp, pageDown
					nB = obj.name, sMax = /[RGB]/.exec(nB)?255:/[SV]/.exec(nB)?100:/[H]/.exec(nB)?360:16777215,
			    sc = sMax>360?/[0-9a-fA-F]/:/\d/, bm, sel, sleft, pos, tmpVal, valC1 = /38|40|33|34/.exec(code), valC2 = /8|46/.exec(code)||valC1;
			if (code==13) {if (obj==cP.cPIX) initCp(X2RGB(obj.value)); unFocus(); return false}
			if ((vCHR && !valC2) || (obj == cP.cPIX && (vCHR || sc.test(chr)))) return true;
			if (!nB || nB.search(/[RGBHSVX]/) == -1 || (!vCHR && !sc.test(chr))) return false; if (obj.value=='0') obj.value='';
			if (document.selection) { // IE
				bm = document.selection.createRange().getBookmark(); sel = obj.createTextRange(); sleft = obj.createTextRange();
				sel.moveToBookmark(bm); sleft.collapse(true); sleft.setEndPoint("EndToStart", sel);
				obj.selectionStart = sleft.text.length; obj.selectionEnd = sleft.text.length + sel.text.length;
			} pos = obj.selectionStart-(code==8?1:0);
			tmpVal = (obj.value.substr(0,pos)+(vCHR?'':chr)+obj.value.substr(obj.selectionEnd+(code==46?1:0))).replace(/^0*/g,'');
			if (valC1) tmpVal = +obj.value+(code==38?1:code==40?-1:code==33?(tmpVal>(sMax-10)?sMax-tmpVal:10):-10); obj.value = tmpVal;
			if (+tmpVal <= sMax) chInput(+tmpVal,sMax,nB.search(/[RH]/)!=-1?0:nB.search(/[GS]/)!=-1?1:2,CP.rgb,CP.hsv); if (rSpeed) doRender(true,true);
			pos = pos-(valC2?1:0);
			if(obj.createTextRange) {bm = obj.createTextRange();bm.move("character", pos+1);bm.select()} else obj.setSelectionRange(pos+1, pos+1);
			return false;
		}; cP.onkeypress=function(e){ // Opera & Konquhororrrr are not quiet after returnFalse in keyDown!!!! and IE<8 ignores ENTER in keyDown :o(
			var e = e||window.event, obj = e.target||e.srcElement, code = e.which||e.keyCode, vCHR={37:1,38:1,39:1,40:1,46:1,8:1,9:1,13:1}[code];
			if ((vCHR && obj!=cP.cPIX && code!=46 && code!=8) || (obj == cP.cPIX && (vCHR || /[0-9a-fA-F]/.test(String.fromCharCode(code))))) {
				if (code==13 && obj==cP.cPIX) {initCp(X2RGB(obj.value)); unFocus()} return true} else return false
		};
		
		cP.onmouseup = cP.cPPanel.onmouseout = function(e){ // reset all button states
			var e = e || window.event, obj = e.target || e.srcElement;
			if (!obj.className.search(/cPC*B(\d|RX)/) && !cPRender) chBut(obj,true);
			return false;
		};

		cP.ondblclick=function(e){
			var e = e || window.event, obj = e.target || e.srcElement, nB = /cPB(.)(.)\s+b/.exec(obj.className);
			if (nB && nB[2]!='X'&& nB[1]!='R' && sX>1) {CP.mode=nB[2]=='H'?'R':nB[2]=='S'?'G':nB[2]=='V'?'B':nB[2]=='R'?'H':nB[2]=='G'?'S':'V';initCp(CP.rgb);resizeWin(' S')} // change mode if small
			else if (obj == cP.cPCB1) {if (CP.bd) {document.body.style.cssText = CP.bd;CP.bd=null} 
				else {CP.bd = document.body.style.cssText+';'; document.body.style.background='rgb('+CP.rgbRND+')'}}
		};
		
		// method definitions
		colorPicker.importRGB = function(rgb) { var pos, CP=colorPicker.CP; if(rgb[0]===false) rgb[0]=CP.rgb[0]; else pos=0; if(rgb[1]===false) rgb[1]=CP.rgb[1]; else pos=1; if(rgb[2]===false) rgb[2]=CP.rgb[2]; else pos=2;
			chInput(rgb[pos],255,pos,rgb,CP.hsv);
			if (!rSpeed) doRender(true,true); else if (!cPRender)cPRender = setInterval(function(){doRender(true,true)},rSpeed)};
		colorPicker.importHSB = function(hsv) { var pos, sMax, CP=colorPicker.CP; if(hsv[0]===false) hsv[0]=CP.hsv[0]; else {pos=0; sMax=360}; if(hsv[1]===false) hsv[1]=CP.hsv[1]; else {pos=1; sMax=100}; if(hsv[2]===false) hsv[2]=CP.hsv[2]; else {pos=2; sMax=100};
			chInput(hsv[pos],sMax,pos,CP.rgb,hsv);
			if (!rSpeed) doRender(true,true); else if (!cPRender)cPRender = setInterval(function(){doRender(true,true)},rSpeed)};
		colorPicker.importColor = function(color) {initCp(X2RGB(color))};
		colorPicker.stopRendering = function() {clearInterval(cPRender); cPRender=false; doRender(true,true)};
		
		scripts=div=newCSS=n=m=cPDir=nN=nB=CSSTxt=CSSTmp=HTMLTxt=_h=_blank=_horizontal=_vertical=_icons=_patches=_IE=_nonIE=testPix=filter=null;
		CP=colorPicker.CP={}; CP.modeRGB={}; CP.mode=colorPicker.mode||mode; colorPicker.rSpeed=rSpeed;
	}:colorPicker.CP, // collection of colors/coords
	
	initCp = function(rgb) {
		cPM = /R|G|B/.exec(CP.mode)?'RGB':'HSV';
		x=/R|G/.exec(CP.mode)?2:CP.mode=='H'?1:0;
		y=/S|H/.exec(CP.mode)?2:CP.mode=='G'?0:1;
		z=/R|H/.exec(CP.mode)?0:/G|S/.exec(CP.mode)?1:2;
		
		setCookie('CP.mode',CP.mode); if (!allowResize) cP.cPResizes.display='none'; if (!allowClose) cP.cPCloses.display='none';
		sX=/cPSkin(\s+S)*(\s+XS)*(\s+XXS)*/.exec(cP.className); sZ=sX[3]?2:1; sX=sX[1]?2:1; xyzCorr=(sX>1?128:0)+(sZ>1?64:0); // reset size and size correction
		for(var n=0; n<cP.but.length; n++) if (cP.but[n][1] == 'L') chBut(cP[cP.but[n][0]],cP.but[n][2] != CP.mode); // reset button states
		for(var n=0,nob='RGB'.split('');n<nob.length;n++) if (CP.modeRGB[nob[n]]) cP['cPBR'+nob[n]].className = 'cPBR'+nob[n]+' '+'bUp';
		difWidth = getStyle(cP.cPCTRT.parentNode,'width').replace('px','')-difPad; // max width of contrast/color bar
		CP.iCtr = getBrightness(CP.CB2Color); cCtr=null; crCtr=null;// of the current saved color !!!
		cP.cPCB2s.backgroundColor = 'rgb('+CP.CB2Color+')'; // set right swatch
		rSpeed = colorPicker.rSpeed;
		for(var n=1, m='L';n<=3;n++) { // preset all classNames in all sliders
			cP['cPS'+m+n].className = 'cPS'+m+n+CP.mode+(CP.modeRGB[CP.mode]&&n>1&&m=='R'?' cPhide':'');
			if (n>2 && m=='L') {m='R'; n=0}}
		CP.rgb=[]; CP.hsv=[]; if (cPM == 'HSV') rgb = RGB2HSV(rgb[0],rgb[1],rgb[2]);
		doDrag(null,null,true,[rgb[x],rgb[y],rgb[z]],true);
	},
	
	doDrag = function(e,origin,xy,mouseIs,render,rgb,hsv) { // this func gets/stores mouse coordinates and calculates all colors
		var CP=colorPicker.CP, xyz; //s=CP.mode=='H'?2:CP.mode=='S'?14:26; // CP = scope shifting
		if (!mouseIs){ // correct mousepos to stay inside boundries
			xyz = getMousePos(e,origin);
			xyz[1] = xyz[1]<0?255:xyz[1]*sX*sZ>255?0:255-xyz[1]*sX*sZ;
			if (xy) {CP.xyz[0] = xyz[0]<0?0:xyz[0]*sX>255?255:xyz[0]*sX; CP.xyz[1] = xyz[1]} else CP.xyz[2] = xyz[1]; //; s+=6}
		} else CP.xyz=mouseIs;// { s=(s-2)/2+38}

		if (cPM == 'RGB')	{ // store colors
			if (rgb) CP.rgb = rgb; else {CP.rgb[x] = CP.xyz[0]; CP.rgb[y] = CP.xyz[1]; CP.rgb[z] = CP.xyz[2]}
			CP.hsv = hsv?hsv:RGB2HSV(CP.rgb[0],CP.rgb[1],CP.rgb[2]);
		} else {
			// CP.hsv = hsv?hsv:[CP[HX[HX[s++]]][HX[s++]],CP[HX[HX[s++]]][HX[s++]],CP[HX[HX[s++]]][HX[s]]]; // useing wicked pattern ;o)
			if (hsv) CP.hsv = hsv; else {CP.hsv[x] = CP.xyz[0]; CP.hsv[y] = CP.xyz[1]; CP.hsv[z] = CP.xyz[2]}
			CP.rgb = rgb?rgb:HSV2RGB(CP.hsv[0],CP.hsv[1],CP.hsv[2], true);
		}
		CP.rgbRND = [Math.round(CP.rgb[0]),Math.round(CP.rgb[1]),Math.round(CP.rgb[2])];
		CP.cmyk = RGB2CMYK(CP.rgb[0],CP.rgb[1],CP.rgb[2]); CP.cmyk = [Math.round(CP.cmyk[0]*100),Math.round(CP.cmyk[1]*100),Math.round(CP.cmyk[2]*100),Math.round((1-CP.cmyk[3])*100)];
		CP.hex = RGB2HEX(CP.rgbRND[0],CP.rgbRND[1],CP.rgbRND[2]);

		if (!rSpeed || render) doRender(xy,mouseIs);
	},
	
	doRender = function(xy,yz) { // function for pure rendering. No rendering elsewhere!!
		var CP=colorPicker.CP,cP=colorPicker.cP,a=0,b=0,c=0,ctrDif,colDif,tmpHSV,nCtr,nrCtr,cPCtr, WS; // cP & CP = scope shifting

		// display all the nice colors in sliders
		if (xy) { // left slider -> right
			if (CP.xyz[0] > CP.xyz[1]) b=1; else a=1;
			if (CP.mode == 'S' || CP.mode == 'V') {cP.cPSR2s.backgroundColor = 'rgb('+HSV2RGB(CP.xyz[0],255,255)+')'; b=1; c=255}
			else if (CP.mode != 'H' && !CP.modeRGB[CP.mode]) cP.cPSR2.className = 'cPSR'+(2+a)+CP.mode+' cPOP'+Math.round((CP.xyz[a]-CP.xyz[b])/(255-CP.xyz[b])*100 || 0);
			if (CP.mode != 'H' && !CP.modeRGB[CP.mode]) cP.cPSR3.className = 'cPSR4'+CP.mode+' cPOP'+Math.round(Math.abs(c-CP.xyz[b])/2.55);
			cP.cPSLCs.cssText = 'left:'+(CP.xyz[0]/sX-5)+'px;top:'+Math.ceil(250-CP.xyz[1]/sX/sZ-xyzCorr)+'px'; // change cursor
		}
		if (!xy || yz) { // right slider -> left
			if (CP.mode == 'H') {tmpHSV = HSV2RGB(CP.xyz[2],255,255); cP.cPSL1s.backgroundColor = 'rgb('+tmpHSV+')'}
			else cP.cPSL3.className = 'cPSL3'+CP.mode+' cPOP'+(100-Math.round(CP.xyz[2]/2.55));
			cP.cPSRCLs.top = c = +Math.ceil(252-CP.xyz[2]/sX/sZ-xyzCorr)+'px'; // change right-left cursor ... recycle var c
			if (yz) cP.cPSRCRs.top = c; // change right-right cursor
		}
		
		// switch brightness if contrast changes
		cPCtr = getBrightness(CP.rgbRND); nCtr = cPCtr > 128;
		if (cCtr !== nCtr) {
			if (nCtr) {if (expColor) cP.cObjs.color = '#222'; cP.cPSLC.className = 'cPSLCB'; CP.cPM0CN = 'cPM0 cPM0B';
				if (CP.mode != 'H' && !CP.modeRGB[CP.mode]) {cP.cPSRCL.className = 'cPSRCLB';
					if (xy) cP.cPSRCR.className = 'cPSRCRB'}
			}	else {if (expColor) cP.cObjs.color = '#ddd';cP.cPSLC.className = 'cPSLCW'; CP.cPM0CN = 'cPM0';
				if (CP.mode != 'H' && !CP.modeRGB[CP.mode]) {cP.cPSRCL.className = 'cPSRCLW';
					if (xy) cP.cPSRCR.className = 'cPSRCRW'}
			}
		} cCtr = nCtr;
		if (!xy || yz) { // only right
			if (CP.modeRGB[CP.mode]) {
				nrCtr = CP.xyz[2] > 153;
				if (crCtr !== nrCtr){ // RGB special mode
					if (nrCtr && CP.mode == 'G') {cP.cPSRCL.className = 'cPSRCLB'; if (xy) cP.cPSRCR.className = 'cPSRCRB'; 
					} else {cP.cPSRCL.className = 'cPSRCLW'; if (xy) cP.cPSRCR.className = 'cPSRCRW'}}
			} else if (CP.mode == 'H') { // HSB_H mode extra rules
				nrCtr = getBrightness(tmpHSV || HSV2RGB(CP.hsv[0],255,255)) > 128;
				if (crCtr !== nrCtr) {if (nrCtr) cP.cPSRCL.className = 'cPSRCLB'; else cP.cPSRCL.className = 'cPSRCLW'}
			} crCtr = nrCtr;
		}
		
		// display brightness/color match bar
		colDif = getColorDifference(CP.CB2Color,CP.rgb)/765*difWidth; // 765 = 3 colors * 255 possible values
		ctrDif = Math.abs((cPCtr-CP.iCtr)/255*difWidth);
		cP.cPCTRTs.cssText = 'width:'+ctrDif+'px;'+((colDif>ctrDif)?cP.CTRTop:'');
		cP.cPCDs.cssText = 'width:'+colDif+'px;'+((ctrDif<difWidth/2 && colDif<difWidth/3*2)?'':((ctrDif<difWidth/2 || colDif<difWidth/3*2)?cP.cPCD1:cP.cPCD2));
		
		// display color values // this also touches the DOM
		cP.cPIR.value = CP.rgbRND[0]; cP.cPIG.value = CP.rgbRND[1]; cP.cPIB.value = CP.rgbRND[2];
		cP.cPIH.value = Math.round(CP.hsv[0]/255*360); cP.cPIS.value = Math.round(CP.hsv[1]/2.55); cP.cPIV.value = Math.round(CP.hsv[2]/2.55);
		cP.cPIC.value = CP.cmyk[0]; cP.cPIM.value = CP.cmyk[1]; cP.cPIY.value = CP.cmyk[2]; cP.cPIK.value = CP.cmyk[3];
		cP.cPIX.value = CP.hex;

		// display WEBSave/WEBSmart/otherColor button
		WS = (CP.rgbRND[0]%51==0 && CP.rgbRND[1]%51==0 && CP.rgbRND[2]%51==0) ? 'W' :
		     (CP.rgbRND[0]%17==0 && CP.rgbRND[1]%17==0 && CP.rgbRND[2]%17==0) ? 'M' : '!';
		if (WS != CP.WS) cP.cPBRX.firstChild.data = CP.WS = WS;
		
		// display value/color in initField swatch and left/background
		cP.cPCB1s.backgroundColor = 'rgb('+CP.rgbRND+')';
		if (expColor) cP.cObjs.backgroundColor = 'rgb('+CP.rgbRND+')'; if (expHEX) cP.cObj.value = CP.valPrefix+CP.hex;
		if (CP.bd) document.body.style.background = 'rgb('+CP.rgbRND+')';
		
		if (colorPicker.exportColor) colorPicker.exportColor();
		if (xy && yz) { // stopRender
			cP.cPSRCR.className = cP.cPSRCL.className.replace('L','R'); // ;o)
			cP.cPM0s.backgroundColor = 'rgb('+CP.rgbRND+')';
			if (CP.cPM0CN) {cP.cPM0.className = CP.cPM0CN; CP.cPM0CN = ''}
		}
	},

	stopDrag = function(){
		removeEvent(docBody, 'mousemove');
		if (cPRender){clearInterval(cPRender); cPRender=false; doRender(true,true)}
		cP.cPSL4.className = 'cPSL4'; // cursor to normal
		cP.cPResizers.cssText='';
		// cP.cObj.osXY = 'left:'+cP.style.left+';top:'+cP.style.top+';'; // save position
		cP.cObj.osX = cP.style.left; cP.cObj.osY = cP.style.top; // save position
		if (cP.inp) cP.inp.className = '';
	},
	
	chInput = function(mP,sMax,pos,rgb,hsv) {
		if (cP.WEBS1) cP.WEBS1=null; // kill the memory of webSmart/Save button
		mP = (mP<0)?0:(mP>sMax)?sMax:mP;
		if (sMax == 255) {rgb[pos] = mP; if (cPM == 'HSV') hsv=RGB2HSV(rgb[0],rgb[1],rgb[2])}
		else {hsv[pos] = mP/sMax*255; if (cPM == 'RGB') rgb=HSV2RGB(hsv[0],hsv[1],hsv[2])};
		if (cPM == 'RGB') doDrag(null,null,rSpeed==0,[rgb[x],rgb[y],rgb[z]],false,rgb,(sMax != 255)?hsv:false);
		else doDrag(null,null,rSpeed==0,[hsv[x],hsv[y],hsv[z]],false,(sMax == 255)?rgb:false,hsv)
	},
	
	chBut = function(obj,onOff) {obj.className = (onOff) ? obj.className.replace('bDown','bUp') : obj.className.replace('bUp','bDown')},
	
	resizeWin = function(nCN) {
		cP.className = 'cPSkin' + nCN; sX=(nCN)?2:1; sZ=(nCN==' S XS XXS')?2:1; xyzCorr=(sX>1?128:0)+(sZ>1?64:0);
		if (nCN==' S XS XXS' && (cPM=='RGB' || CP.mode=='H')) {CP.modeTmp=CP.mode; CP.mode='S'; initCp(CP.rgb)}
		else if (CP.modeTmp && CP.modeTmp!=CP.mode) {CP.mode=CP.modeTmp; CP.modeTmp=null; initCp(CP.rgb)} else doRender(true,true);
		cP.cPRGB.className = (cPM=='RGB' || !nCN) ? 'cPRGB' : 'cPhide'; cP.cPHSB.className = (cPM=='HSV' || !nCN) ? 'cPHSB' : 'cPhide';
		setCookie('size',nCN==' S XS XXS'?1:nCN==' S XS'?2:nCN==' S'?3:4);
	},

	toggleCp = function(onOff,obj) { 
		var cPPS;
		if (onOff && !parentObj) cP.cPSkins.display = 'none';
		else {if (!parentObj) {
				if (cP.cObj && obj.parentNode != cP.cObj.parentNode) obj.parentNode.appendChild(cP.parentNode.removeChild(cP));
				cPPS = cP.parentNode.style;
				if (getStyle(cP.parentNode,'position') == 'static') cPPS.position = 'relative'; // fixed???
				if (!/(display|height|width|zoom)/.exec(cPPS.cssText.toLowerCase())) cPPS.zoom = '1'}
			// cP.cPSkins.cssText = parentObj?parentXY:'position:absolute;'+(obj.osXY?obj.osXY:
			// 	'left:'+(obj.offsetLeft+offsetX+(orientation[1]=='right'?obj.offsetWidth:0))+'px;'+
			// 	'top:'+(obj.offsetTop-(orientation[0]=='top'?cP.offsetHeight-(orientation[1]=='right'?obj.offsetHeight:-offsetY):orientation[1]=='right'?obj.offsetHeight-obj.offsetHeight:-offsetY-obj.offsetHeight))+'px');
			if (parentObj) cP.cPSkins.cssText = cP.cPSkins.cssText.replace(parentXY,'') + parentXY;
			else {
				cP.cPSkins.position = 'absolute'; cP.cPSkins.display = '';
				cP.cPSkins.left = (obj.osX?obj.osX:(obj.offsetLeft+offsetX+(orientation[1]=='right'?obj.offsetWidth:0))+'px');
				cP.cPSkins.top =  (obj.osY?obj.osY:(obj.offsetTop-(orientation[0]=='top'?cP.offsetHeight-(orientation[1]=='right'?obj.offsetHeight:-offsetY):orientation[1]=='right'?obj.offsetHeight-obj.offsetHeight:-offsetY-obj.offsetHeight))+'px')}
			cP.cObj = obj; initCp(CP.CB2Color)}
	},
	
	unFocus = function() {cP.cPdummy.focus(); if (IE) cP.cPdummy.blur()},

	/* -------------------------------- */
	/* ------  my nice helpers -------- */
	/* -------------------------------- */
	
	getBrightness = function(rgb) {return Math.sqrt(rgb[0]*rgb[0]*.241+rgb[1]*rgb[1]*.691+rgb[2]*rgb[2]*.068)},
	
	getColorDifference = function(rgb1,rgb2) {
		return (Math.max(rgb1[0], rgb2[0]) - Math.min(rgb1[0], rgb2[0])) +
		       (Math.max(rgb1[1], rgb2[1]) - Math.min(rgb1[1], rgb2[1])) +
		       (Math.max(rgb1[2], rgb2[2]) - Math.min(rgb1[2], rgb2[2]));
	},
	
	X2RGB = function (hex) { // accepts array(r,g,b), 'rgb(r,g,b)', #0 - #123AEFxyz, 0 - 123AEFxyz, #2af, 2af
		hex = (hex+'').replace(/[(^rgb\()]*[^a-fA-F0-9,]*/g,'').split(',');
		if (hex.length == 3) return [+hex[0],+hex[1],+hex[2]];
		hex+=''; if (hex.length == 3) {hex=hex.split(''); return [parseInt((hex[0]+hex[0]),16),parseInt((hex[1]+hex[1]),16),parseInt((hex[2]+hex[2]),16)]}
		while(hex.length<6) hex='0'+hex; return [parseInt(hex.substr(0,2),16),parseInt(hex.substr(2,2),16),parseInt(hex.substr(4,2),16)]
	},
	
	RGB2HEX = function (r,g,b) {
		return((r<16?'0':'')+r.toString(16)+
		       (g<16?'0':'')+g.toString(16)+
		       (b<16?'0':'')+b.toString(16)).toUpperCase();
	},
	
	HSV2RGB = function(x,y,z,s) { // !!! this function takes x,y,z not h,s,v
		var r=0, g=0, b=0, c=0, d=(100-y/2.55)/100, i=z/255,j=z*(255-y)/255;
		
		if (x<42.5){r=z;g=x*6*i;g+=(z-g)*d;b=j}
		else if (x>=42.5&&x< 85){c=42.5;r=(255-(x-c)*6)*i;r+=(z-r)*d;g=z;b=j}
		else if (x>=85&&x<127.5){c=85;r=j;g=z;b=(x-c)*6*i;b+=(z-b)*d}
		else if (x>=127.5&&x<170){c=127.5;r=j;g=(255-(x-c)*6)*i;g+=(z-g)*d;b=z}
		else if (x>=170&&x<212.5){c=170;r=(x-c)*6*i;r+=(z-r)*d;g=j;b=z}
		else if (x>=212.5){c=212.5;r=z;g=j;b=(255-(x-c)*6)*i;b+=(z-b)*d}
		if (s) return[r,g,b]; else return [Math.round(r),Math.round(g),Math.round(b)];
	},
	
	RGB2HSV = function(r,g,b) { // !!! this function returns x,y,z not h,s,v
		var n = Math.min(Math.min(r,g),b), v = Math.max(Math.max(r,g),b), m = v - n, h = 0;
		if(m === 0) return [0, 0, v];
		h = r===n ? 3+(b-g)/m : (g===n ? 5+(r-b)/m : 1+(g-r)/m);
		return [h===6?0:h*42.5, m/v*255, v];
	},
	
	RGB2CMYK = function(r,g,b) {
		var k = Math.min(1-r,1-g,1-b), l = 1-k;
		if (k == 1) return[0,0,0,-k/255];
		else return[(1-r-k)/l,(1-g-k)/l,(1-b-k)/l,-k/255];
	},
	
	getStyle = function (obj,prop) { // simple version
		if (obj.currentStyle)	return obj.currentStyle[prop];
		else if (window.getComputedStyle) return document.defaultView.getComputedStyle(obj,null).getPropertyValue(prop);
	},

	getOrigin = function(obj) {
		var parent=null, box=null, pos=[],
		    _sL = document.body.scrollLeft+document.documentElement.scrollLeft,
				_sT = document.body.scrollTop+document.documentElement.scrollTop;
		
		if (obj.parentNode === null || getStyle(obj,'display') == 'none') return false;
		if (obj.getBoundingClientRect) { // IE
			box = obj.getBoundingClientRect();
			return [Math.round(box.left)+(document.documentElement.scrollLeft||document.body.scrollLeft),
			        Math.round(box.top)+(document.documentElement.scrollTop||document.body.scrollTop),_sL,_sT];
		}	else if (document.getBoxObjectFor) { // gecko
			box = document.getBoxObjectFor(obj);
			pos = [box.x, box.y];
		}	else { // safari/opera
			pos = [obj.offsetLeft, obj.offsetTop];
			parent = obj.offsetParent;
			if (parent != obj) {
				while (parent) {
					pos[0] += parent.offsetLeft; pos[1] += parent.offsetTop;
					parent = parent.offsetParent;
				}
			}
			if (window.opera  || (document.childNodes && !document.all && !navigator.taintEnabled && !accentColorName)) pos[1] -= document.body.offsetTop;
		}
		if (obj.parentNode) parent = obj.parentNode; else parent = null;
		while (parent && parent.tagName != 'BODY' && parent.tagName != 'HTML') {
			pos[0] -= parent.scrollLeft;
			pos[1] -= parent.scrollTop;
			if (parent.parentNode) parent = parent.parentNode;
			else parent = null;
		}
		return pos.concat([_sL,_sT]);
	},

	getMousePos = function(e,xy) {
		getMousePos = (typeof e.pageX === 'number' && !window.opera) ?
			function(e,xy) {return [e.pageX-xy[0],e.pageY-xy[1]]} :
			function(e,xy) {return [e.clientX+xy[2]-xy[0],e.clientY+xy[3]-xy[1]]};
		return getMousePos(e,xy);
	},

	addEvent = function(obj, type, func) {
		if (!obj || !type || !func) return false;
		obj.funcCache = obj.funcCache || {};
		obj.funcCache[type] = func;
		if (obj.addEventListener) obj.addEventListener(type, func, false);
		else obj.attachEvent("on" + type, func);
	},
	
	removeEvent = function(obj, type, func) {
		if (!obj || !type) return false;
		if (!func && (!obj.funcCache || !obj.funcCache[type])) return false;
		if (obj.removeEventListener) obj.removeEventListener(type, func||obj.funcCache[type], false);
		else obj.detachEvent("on" + type, func||obj.funcCache[type]);
	},
	
	setCookie = function(name,value) {
		var date = new Date(), expires = '';
		if (cookieLife) expires = '; expires='+(new Date(date.getTime()+cookieLife*60*60*24*1000).toGMTString());
		if (typeof value != undefined) document.cookie = name+'='+(value.toString().replace(/\s+/g,' '))+expires+'; path=/';
	};
	
	(function() {
		var obj = parentObj || e.target || e.srcElement, ca = document.cookie.split(';'), n, c;
			if (iPhone) obj.blur();
			if (!cP) {CP(obj); e=false} 
			for(n=0; n < ca.length; n++) { // restore cookies
				c = ca[n]; while (c.charAt(0)==' ') c = c.substring(1,c.length);
				// if (/(^size|cP\.cPM|CP\.mode)/.exec(c)) eval(c.replace(/(\d)s=(.*)/,"$1s.backgroundColor='$2"+"'").replace(/\|/g,','))} // in this case CP.mode has to be saved as 'X'
				if (c=/(^cP\.|^CP\.|^size)(.*)=(.*)/.exec(c)) if (c[3]) {{if (c[1]=='cP.') cP[c[2]].backgroundColor=c[3].replace(/\|/g,',');
					else if (c[1]=='CP.') {c[2]=c[2].split('.'); if(c[2][1]) CP[c[2][0]][c[2][1]]=c[3]=='false'?false:true; else CP[c[2]]=c[3]}
					else size=c[3]}}}
			CP.CB2Color = obj.color = X2RGB(obj.value||color||[204,0,0]);
			if (cP.WEBS1) cP.WEBS1=null; // kill the memory of webSmart/Save button
			cP.cObjs = obj.style;
			if (obj.value) CP.valPrefix = /(#*)/.exec(obj.value)[0]; else CP.valPrefix = '#'; // wether you have an # or an ## (Cold Fusion) or none
			toggleCp(cP.cPSkins.display == '' && obj == cP.cObj ? true : false, obj);
			if (!e) resizeWin(size==1?' S XS XXS':size==2?' S XS':size==3?' S':'');
	})();
}