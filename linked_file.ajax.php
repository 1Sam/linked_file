<?php
define('__XE__', true);
require_once '../../config/config.inc.php';
//require_once '../../classes/object/Object.class.php';

$oContext = &Context::getInstance();
$oContext->init();
 

class post_var {
	// $_POST 로 건네받은 변수를 키값으로 정리함
	static $posts;

	public function __construct() {
	}

	public function get_Postvars_Array() {
		if ($_POST) {
			$posts = array();
			foreach ($_POST as $key => $value) {
				$posts[$key] = $value;
			}
			//$query_string = print_r($posts,true);//join("&", $kv);
		}
		else {
			$posts = $_SERVER['QUERY_STRING'];
		}
		return $posts;
	}

	public function get_Postvars_String() {
		if ($_POST) {
			$posts = array();
			foreach ($_POST as $key => $value) {
				$posts[$key] = $value;
			}
			$posts = print_r($posts,true);//join("&", $kv);
		}
		else {
			$posts = $_SERVER['QUERY_STRING'];
		}
		return $posts;
	}
}

//$_POST로 넘어온 배열 출력해보기
//아래의 주석문을 풀면 팝업으로 포스트 값을 볼 수 있습니다.
/*echo json_encode(array('error' => '0','message' => post_var::get_Postvars_Array()));
return;*/

// Object class에 연결하여 $this->add() 사용 가능 
class upload_linked_file extends Object {
	// 필요한 변수 설정
	public $editor_sequence;
	protected $document_srl;
	static $file_url;
	private $mod_srl;
	private $filter_ext;
	public function __construct()
	{
		//mid, sequence_srl, document_srl, filelink_url, module_srl
		$fileLink = new post_var;
		$post_vars = $fileLink->get_Postvars_Array();
		
		// 필요한 변수 설정
		$this->editor_sequence = $post_vars[sequence_srl]; //에디터
		$this->document_srl = $post_vars[document_srl];
		$this->file_url = $post_vars[filelink_url];
		$this->mod_srl = $post_vars[module_srl];
		$this->filter_ext = $post_vars[filter_ext];
	}

	function procInsertFileLink()
	{
		if(!preg_match("/^(https?|ftp|file|mms):[\/]{2,3}[A-Za-z0-9-]+.[A-Za-z0-9-]+[.A-Za-z0-9-]*\/.*[A-Za-z0-9]{1,}/i", $this->file_url))
			return new Object(-1, Context::getLang('msg_invalid_format') . $this->file_url."\r\nex: http, ftp, mms, file");

		$filename = basename($this->file_url);
		if(!$filename) return new Object(-1, 'msg_invalid_request');


		// direct 파일에 해킹을 의심할 수 있는 확장자가 포함되어 있으면 바로 삭제함
		// 어차피 링크 파일이라 위험 없음...
		if($this->filter_ext != 'N') {
			if(preg_match("/\.(php|phtm|html|htm|cgi|pl|exe|jsp|asp|inc)/i",$filename)) return new Object(-1, '확장자 위험경고');
		}


		if(strlen($filename) > 20 && strpos($filename, '?') > -1)
		{
			$rex = strpos($filename, '?') > 10 ? "/([^#]{1,10}).*\?.*(\#.+$)/i":"/.*\?([^#]{1,10}).*(\#.+$)/i";
			$filename = preg_replace($rex, '$1...$2', $filename);
		}

		// 업로드 권한이 없거나 정보가 없을시 종료
		if(!$_SESSION['upload_info'][$this->editor_sequence]->enabled) return new Object(-1, $this->editor_sequence.'msg_not_permitted');

		// upload_target_srl 값이 명시되지 않았을 경우 세션정보에서 추출
		if(!$this->document_srl) $this->document_srl = $_SESSION['upload_info'][$this->editor_sequence]->upload_target_srl;

		// 세션정보에도 정의되지 않았다면 새로 생성
		if(!$this->document_srl) $_SESSION['upload_info'][$this->editor_sequence]->upload_target_srl = $this->document_srl = getNextSequence();



		$filename = str_replace(array('<', '>'), array('%3C', '%3E'), $filename);

		// 이미지인지 기타 파일인지 체크
		$direct = preg_match("/\.(png|jpe?g|bmp|gif|ico|swf|flv|mp[1234]|as[fx]|wm[av]|mpe?g|avi|wav|midi?|moo?v|qt|ra?m|ra|rmm|m4v)$/i", $filename) ? 'Y' : 'N';

		// 사용자 정보를 구함
		$oLogIfo = Context::get('logged_info');

		// 파일 정보를 정리
		$args->file_srl = getNextSequence();
		$args->upload_target_srl = $this->document_srl;
		$args->module_srl = $this->mod_srl;
		$args->direct_download = $direct;
		$args->source_filename = $filename;
		$args->uploaded_filename = $this->file_url;
		$args->download_count = 0;
		$args->file_size = 0;
		$args->comment = 'link';
		$args->member_srl = (int) $oLogIfo->member_srl;
		$args->sid = md5(rand(rand(1111111, 4444444), rand(4444445, 9999999)));

		$output = executeQuery('file.insertFile', $args);
		if(!$output->toBool()) return array('error' => '-1','message' => $output);

		$this->add('sequence_srl', $this->editor_sequence);
		$this->add('document_srl', $this->document_srl);
		$this->add('file_srl', $args->file_srl);
		$this->setMessage('success_registed');
		
		return $this;
	}
 }

// 함수를 실행한 결과값은 array();
$linked_file = new upload_linked_file;
$output = $linked_file->procInsertFileLink();

echo json_encode($output);

$oContext->close();
