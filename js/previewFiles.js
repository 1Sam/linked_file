// 링크 이미지가 제대로 보이도록
// xe/modules/editor/tpl/js/upload.js 의 함수를 수정하였음

function previewFiles(event, file_srl) {
	var $opt, $select, $preview, fileinfo, filename, match, html, $=jQuery;

	if(!file_srl) {
		$opt = $(event.target).parent().addBack().filter('select').find('>option:selected');
		if(!$opt.length) return;

		file_srl = $opt.attr('value');
	}

	if(!file_srl || !is_def(fileinfo=uploadedFiles[file_srl])) return;

	$preview = $('#'+fileinfo.previewAreaID).html('&nbsp;');
	if(!$preview.length) return;

	filename = fileinfo.download_url || '';
	match    = filename.match(/\.(?:(flv)|(swf)|(wmv|avi|mpe?g|as[fx]|mp3)|(jpe?g|png|gif))$/i);

	if(fileinfo.direct_download != 'Y' || !match) {
		html = '<img src="'+request_uri+'modules/editor/tpl/images/files.gif" border="0" width="100%" height="100%" />';
	} else if(match[1]) { // flash video file
		html = '<embed src="'+request_uri+'common/img/flvplayer.swf?autoStart=false&file='+uploaded_filename+'" width="100%" height="100%" type="application/x-shockwave-flash" />';
	} else if(match[2]) { // shockwave flash file
		html = '<embed src="'+request_uri+filename+'" width="100%" height="100%" type="application/x-shockwave-flash"  />';
	} else if(match[3]) { // movie file
		html = '<embed src="'+request_uri+filename+'" width="100%" height="100%" autostart="true" showcontrols="0" />';
	} else if(match[4]) { // image file

		// 첨부파일이 http로 시작하면 도메인주소값을 비움
		match = filename.match(/(http[s]?:\/\/)/i);
		if(match) request_uri = '';

		match = filename.match(/\/\/lh.*?google.*\.com\/(?:-\w).*\/?(\/.*?\/)/i);
		//alert(JSON.stringify(match)); 
		//피카사 이미지와 일반이미지를 미리보기 박스에 표시
		if(match) {
			filename = filename.replace(match[1],'/w70-h70-c/');
			html = '<img src="'+filename+'" border="0" width="100%" height="100%" />';
		} else if(!match) {
			html = '<img src="'+request_uri+filename+'" border="0" width="100%" height="100%" />';
		}
	}

	if(html) $preview.html(html);
}
