// запускается данный скрипт таким образом: df2011();
var df2011_PageHost = window.location.host;
var df2011_PageHostWithWWW = "www."+df2011_PageHost;
var df2011_PageHostWithOutWWW = window.location.host.replace(/www\./i, '');
var df2011_Types = 'art.ais.ddf.dib.rle.crw.thm.djv.djvu.iw4.emf.fpx.icl.icn.ico.cur.ani.iff.lbm.ilbm.jpe.jif.jfif.kdc.mag.pbm.pcd.pcx.dcx.pgm.pic.pct.pict.pix.ppm.psd.psp.ras.rsb.sgi.rgb.rgba.bw.int.inta.sid.tga.tif.tiff.xif.ttf.ttc.wmf.xbm.xpm.asf.asx.wpl.wm.wmx.wmd.wmz.wma.wax.wmv.wvx.cda.avi.wav.mpeg.mpg.mpe.m.1.v.m.2.v.mpa.mp.2.mp.3.mpv.2.mp.2.v.m.2.s.vob.ac.3.aac.m.3.u.pls.mid.midi.rmi.aif.aifc.aiff.au.snd.rar.zip.cab.arj.lzh.ace.tar.gzip.uue.bz2.jar.iso.7z.nrg.iso.cue.bin.img.ccd.sub.bkf.pdf.mht.mhtml.txt.rtf.wri.doc.doc.dot.olk.pab.scd.wpd.wps.docs.prn.csv.mdb.mde.iqy.dqy.oqy.rqy.wq1.wks.dbf.xlm.xla.xlc.xlw.xls.xlt.xll.xlb.slk.dif.xlk.bak.mdb.adp.hta.asp.mda.mdw.mde.ade.dbf.db.tab.asc.dwt.asp.aspx.js.vbs.css.xsd.xsl.xslt.dtd.xsn.xsf.one.pst.vcf.vcs.or4.or5.pab.sc2.scd.ppt.pps.ppa.pot.mpp.mpd.mpt.mpw.mpx.pub.vsd.vdx.vss.vsx.vst.vtx.vsw.svg.svgz.dwg.dxf.emz.emf.mdi.ttf.fon.exe.msi.msuuha.qz.tgz.chm.apk';
var df2011_TypesArray = df2011_Types.split('.');
var df2011_TypesArrayLength = df2011_TypesArray.length;
function df2011_Type(path){
	if(path && typeof(path) == 'string' && path!=''){
		var split_dot = path.split('.');
		var file_type = split_dot[ (split_dot.length - 1) ].toLowerCase();
		for (var i=0; i<df2011_TypesArrayLength; i++){
			if(df2011_TypesArray[i] == file_type){ return true; }
		}
	}
	return false;
}
function df2011_PHP(obj, path){
	if(typeof(obj) != "undefined" && typeof(path) != "undefined" && path != ""){
		//obj.target = '_blank';
		
		var srcStat = 'http://' + df2011_PageHost + '/js/downloads/files.php?path='+escape(path)+
		'&flash_version='+df2011_flash()+
		'&java_on='+((navigator.javaEnabled()==true)?'1':'0')+
		'&screen_width='+screen.width+
		'&screen_height='+screen.height+
		'&operation_system='+navigator.platform+
		'&update='+Math.round(Math.random() * 1000000000);
		
		var elem = document.createElement('script');// создаем элемент script
		var obj = document.body.insertBefore(elem, document.body.firstChild);
		obj.setAttribute('language', 'Javascript');
		obj.setAttribute('type', 'text/javascript');
		obj.setAttribute('src', srcStat);
	}
}
function df2011(){// функция запуска данного скрипта
	var href;
	var path;
	var check_file;
	var node = document.getElementsByTagName('A');
	var nodeLength = node.length;
	for (var i=0; i<nodeLength; i++){
		href = node[i].getAttribute('href');
		if(href){// && !node[i].onclick
			check_file = df2011_Type(href);
			if(check_file==true){
				(function(path) { // передаем значение переменной tag_href, как значение переменной path
					node[i].onclick=function(){ df2011_PHP(this, path); }
				})(href);
			}
		}
	}
}
function df2011_flash(){
	var Mn = (navigator.appName.substring(0, 2) == "Mi") ? 0 : 1;
	var f="", n=navigator;
	if (n.plugins && n.plugins.length){
		for (var ii=0; ii<n.plugins.length; ii++){
			if (n.plugins[ii].name.indexOf('Shockwave Flash')!=-1) {
				f=n.plugins[ii].description.split('Shockwave Flash ')[1];
				break;
			}
		}
	}else if(window.ActiveXObject){
		for (var ii=10;ii>=2;ii--) {
			try {
				var fl=eval("new ActiveXObject('ShockwaveFlash.ShockwaveFlash."+ii+"');");
				if (fl) { f=ii + '.0'; break; }
			}
			catch(e) {}
		}
		if((f=="")&&!Mn&&(n.appVersion.indexOf("MSIE 5")>-1||n.appVersion.indexOf("MSIE 6")>-1)){
			FV=clientInformation.appMinorVersion;
			if(FV.indexOf('SP2') != -1)
				f = '>6';
		}
	}
	if ((f != undefined) && (f != '') && (f.replace(/\s/ig, '') != '')) {// проверка
		return f.replace(/\s/ig, '');// удаляю пробелы
	}
}
/* Счетчик скачиваний с помощью Google Analytics
<a href="путь к файлу" onclick="javascript: _gaq.push(['_trackPageview', 'путь к файлу']);">Скачать</a>
*/
