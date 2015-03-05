<?php
if(!defined("__XE__")) exit();

if($called_position != 'after_module_proc' || $this->act != "dispBoardWrite") return;

$fa_icon = '<i class="fa fa-link fa-fw"></i>';

$html = sprintf('<div class="input-group" id="insert-filelink"><input class="form-control" type="text" placeholder="링크를 첨부 하실 수 있습니다." value=""><a class="input-group-addon" href="#insert-filelink">%s링크첨부</a></div>',$fa_icon);



$document_srl = Context::get('document_srl');
//시퀀스값; 새글일 경우만 시퀀스값, 아니면 글번호값
$seq = $document_srl ? $document_srl : $_SESSION[_editor_sequence_];

//ajax로 전달되는 변수들
$formData = sprintf('{mid:"%s",sequence_srl:"%s",document_srl:"%s",filelink_url:filelink_url,module_srl:"%s",filter_ext:"%s"}', $this->mid,  $seq, $document_srl,$this->module_srl,$addon_info->filter_ext);

$script =  sprintf('
			<script type="text/javascript">
			xAddEventListener(window,"load", function() { alert("%s"); } );
			jQuery(document).ready(function(){
				jQuery("input[name=tags]").after("%s");
			});

			jQuery(function($)
			{
				$("#insert-filelink a[href=#insert-filelink]").click(function(){
					var $p = $(this).closest("#insert-filelink").find("> input"),
					filelink_url = $p.val();
					if(filelink_url == undefined || !filelink_url){
						alert("Please enter the file url.\nvirtual type example: http://... #.mov");
						$p.focus();
						return false;
					}
					var formData = %s;
					var request = $.ajax({
						url: "./addons/linked_file/linked_file.ajax.php",type:"POST",dataType:"json",data:formData,
						success: function(result){
							if(result.error == 0){
								//alert(JSON.stringify(result));
								//reload_filelists(result.variables.sequence_srl);
								reloadFileList(uploaderSettings[result.variables.sequence_srl]);
							}else {
								alert(result.message);
						}},
						global:false,cache:false,headers:{"cache-control":"no-cache","pragma":"no-cache"},	async:false,	error: function(){alert("오류임");}
					});
					return false;
				});
			});
		</script>', addslashes($formData), addslashes($html), $formData);

Context::addJsFile('./addons/linked_file/js/linked_file.js', false ,'', null, 'body');
Context::addHtmlHeader($script);
