<?php
require 'setup.php';
// 解析網頁原始檔案
include('class/simplehtmldom_1_5/simple_html_dom.php');
use Symfony\Component\HttpFoundation\Request;
use Nette\Forms\Form;
//資料庫操作
use Illuminate\Database\Capsule\Manager as Capsule;
//代理瀏覽器
use GuzzleHttp\Client;
ini_set("memory_limit","300M");




function index(){
	global $gradeData_arr;
	$form=addWordForm();
	$main="
	  <script src='https://code.jquery.com/ui/1.12.1/jquery-ui.js'></script>
  <script>
	  <script src='https://code.jquery.com/ui/1.12.1/jquery-ui.js'></script>
	".jsCheckWord()."
  </script>
	";
	$main.="
		<div class='row'>
  			<div class='col-md-3'>".showGoogleId()."</div>
  			<div class='col-md-9'>
				<span class='badge alert-danger'>單字等級</span>
				<span class='badge alert-success'>查詢次數</span>
				<span class='badge alert-warning'>進入生字簿次數</span>
  				{$form}
  				".listMyWord()."
  			</div>
  		</div>
	";
	
	return $main;
}





function addWordForm(){
	$main="
			<form action='' class='form-inline'>
				<div class='form-group'>
					<label>請輸入生字</label><input type='text' name='word' value='' id='tags' oninput='checkWord()'>
				</div>
				<span id='checkarea'></span>
				<div class='form-group'>
					<input type='hidden' name='op' value='addWord'>
					<input type='submit' value='新增生字' class='btn btn-group'>
				</div>
			</form>
	";
	return $main;
}

function delMyWord($word=""){
	if(!empty($word)){
		Capsule::table('myword')
			->where('word',$word)
			->where('mail',$_SESSION['gmail'])
			->update(['showword' => 'false']);
	}
	
}

function listMyWord(){
	$main="";
	$allArrs=Capsule::table('myword')
			->join('verb', 'verb.verb', '=', 'myword.word')
			->where('myword.mail', $_SESSION['gmail'])
			->where('myword.showword', 'true')
			->orderBy('verb.grade')
			->get();
	foreach($allArrs as  $allArr){
		$main.=oneVerbShow($allArr->word,true,$allArr->grade);
	}
	return $main;
	
}

function getAllVerb(){
	$mainArr = array();
	$verbArr=Capsule::table('verb')->get();
	foreach($verbArr as $verbData){
		$mainArr[]=$verbData->verb;
	}
	$main=json_encode($mainArr);
	return $main;
}

$request = Request::createFromGlobals();
$op = $request->get('op');
$word=$request->get('word');
$verb=$request->get('verb');

switch($op){
	case "ajaxCheckWord":
		ajaxCheckWord($word);
	break;
	case 'addWord':
		addWord($word,$_SESSION['gmail']);
		header("location:{$_SERVER['PHP_SELF']}");
	break;
	case "verbShow":
		$main=verbShow($verb);
	break;
	case 'delMyWord':
		delMyWord($word);
		header("location:{$_SERVER['PHP_SELF']}");
	break;
	case 'getAllVerb':
		$main=getAllVerb();
		break;


	//預設動作
	default:
		// $main=index();
		$main=index();
	break;
}

echo bootstrap($main);




?>