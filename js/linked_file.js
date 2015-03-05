// 링크 이미지가 제대로 보이도록
// xe/modules/editor/tpl/js/upload.min.js 의 함수를 수정하였음

function previewFiles(a,b){var c,d,e,f,g,h,i=jQuery;if(!b){if(c=i(a.target).parent().addBack().filter("select").find(">option:selected"),!c.length)return;b=c.attr("value")}b&&is_def(e=uploadedFiles[b])&&(d=i("#"+e.previewAreaID).html("&nbsp;"),d.length&&(f=e.download_url||"",g=f.match(/\.(?:(flv)|(swf)|(wmv|avi|mpe?g|as[fx]|mp3)|(jpe?g|png|gif))$/i),"Y"==e.direct_download&&g?g[1]?h='<embed src="'+request_uri+"common/img/flvplayer.swf?autoStart=false&file="+uploaded_filename+'" width="100%" height="100%" type="application/x-shockwave-flash" />':g[2]?h='<embed src="'+request_uri+f+'" width="100%" height="100%" type="application/x-shockwave-flash" />':g[3]?h='<embed src="'+request_uri+f+'" width="100%" height="100%" autostart="true" showcontrols="0" />':g[4]&&(h=get_code_img(f)):h='<img src="'+request_uri+'modules/editor/tpl/images/files.gif" border="0" width="100%" height="100%" />',h&&d.html(h)))}

function get_code_img(filename){
	// 첨부파일이 http로 시작하면 도메인주소값을 비움
	matchs = filename.match(/\/\/lh.*?google.*\.com\/(?:-\w).*\/?(\/.*?\/)/i);
	//alert(JSON.stringify(match)); 
	//피카사 이미지와 일반이미지를 미리보기 박스에 표시
	if(matchs) {
		filename = filename.replace(matchs[1],'/w70-h70-c/');
		html = '<img src="'+filename+'" border="0" width="100%" height="100%" />';
	} else if(filename.match(/http[s]?:\/\//gi)) {
		html = '<img src="'+filename+'" border="0" width="100%" height="100%" />';
	} else {
		html = '<img src="'+request_uri+filename+'" border="0" width="100%" height="100%" />';
	}
	return html;
}
