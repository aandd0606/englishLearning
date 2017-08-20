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
	$main="<div class='row'>
		<div class='col-md-12'>";
	foreach($gradeData_arr as $k => $v){
		$main.=" <a class='btn btn-primary' href='{$_SERVER['PHP_SELF']}?grade={$k}'>{$v}</a> ";
	}
	$main.="</div></div>";
	return $main;
}

function showVerb($grade=""){
	global $gradeData_arr;

	$menu="";
	foreach($gradeData_arr as $k => $v){
		$menu.=" <a class='btn btn-primary col-md-12' href='{$_SERVER['PHP_SELF']}?grade={$k}'>{$v}</a> ";
	}

	@$main.="<h3>{$gradeData_arr[$grade]}單字列表</h3>";
	$verbArr=Capsule::table('verb')
				->where('grade',$grade)
				->orderBy('verb')
				->get();
	foreach($verbArr as $verbData){
		// $main.=oneVerbShow($verbData->verb);
		$main.=oneVerbShow($verbData->verb,false,$grade);
	}
	$user=showGoogleId();
	$body="
		<div class='row'>
			<div class='col-md-3'>{$user}{$menu}</div>
			<div class='col-md-9'>{$main}</div>
		</div>
	";
	return $body;
}



function searchForm(){
	global $gradeData_arr;
	$main="
	  <script src='https://code.jquery.com/ui/1.12.1/jquery-ui.js'></script>
	  <script src='https://code.jquery.com/ui/1.12.1/jquery-ui.js'></script>
	".jsCheckWord()."
	";
	$main.="
		<div class='row'>
  			<div class='col-md-3'>".showGoogleId()."</div>
  			<div class='col-md-9'>
				<form action='{$_SERVER['PHP_SELF']}' method='get' class='form-inline'>
					<div class='form-group'>
						<label>請輸入單字</label><input type='text' name='verb' value='' id='tags' oninput='checkWord()'>
					</div>
					<span id='checkarea'></span>
					<div class='form-group'>
						<input type='hidden' name='op' value='verbShow'>
						<input type='submit' value='查詢生字' class='btn btn-group'>
					</div>
				</form>
  			</div>
  		</div>
	";
	
	return $main;
}



$request = Request::createFromGlobals();
$op = $request->get('op');
$grade=$request->get('grade');
$verb=$request->get('verb');
$word=$request->get('word');

switch($op){
	case "ajaxCheckWord":
		ajaxCheckWord($word);
	break;

	case "searchForm":
		$main=searchForm();
	break;

	case "verbShow":
		$main=verbShow($verb);
	break;
	case 'addWord':
		addWord($word,$_SESSION['gmail']);
		header("location:{$_SERVER['PHP_SELF']}?grade={$grade}");
	break;
	
	
	//預設動作
	default:
		// $main=index();
		$main=showVerb($grade);
	break;
}

echo bootstrap($main);




?>