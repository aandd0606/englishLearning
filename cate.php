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




function index($cate1="",$cate2=""){
	global $cateArr1,$cateArr2;
	$form=addWordForm($cate1,$cate2);
	$main="
	  <script src='https://code.jquery.com/ui/1.12.1/jquery-ui.js'></script>
	  <script>
	  $( function() {
	  	document.getElementById('tags').focus();
	  });
	  
	  function checkWord(){
	  $( function() {
		var word = $('#tags').val();
		$.post('{$_SERVER['PHP_SELF']}',
		{
			op:'ajaxCheckWord',
			word:word
		},
		function(data){
			$('#checkarea').html(data);
			//alert('Data:' + data );
		});
	  });
	  }
  	</script>
	";
	$menu="<ul class='nav nav-pills nav-stacked'>";
	foreach($cateArr1 as $k => $v){
		$menu.="
			<li role='presentation' class='dropdown'>
			<a class='dropdown-toggle' data-toggle='dropdown' href='{$_SERVER['PHP_SELF']}?cate={$k}' role='button' aria-haspopup='true' aria-expanded='false'>
		      {$v}<span class='caret'></span>
		    </a>
		    <ul class='dropdown-menu'>
		    <li>
				<a href='{$_SERVER['PHP_SELF']}?cate1={$k}'>@{$v}總類</a>
		    </li>
		    ".subNav($cateArr2[$k],$k)."
		    </ul>
			</li>";
	}
	$menu.="</ul>";

	
	// $verbArr=Capsule::table('verb')
	// 			->where('cate',$cate)
	// 			->orderBy('verb')
	// 			->get();
	// foreach($verbArr as $verbData){
	// 	$main.=oneVerbShow($verbData->verb);
	// }
	$user=showGoogleId();
	@$body="<h3>目前總類別：【{$cateArr1[$cate1]}】。次類別【{$cateArr2[$cate1][$cate2]}】單字列表</h3>";

	//取得分類單字
	$content="";	
	if(empty($cate2)){
		$verbArr=Capsule::table('verb')
			->where('cate1',$cate1)
			->orderBy('grade')
			->orderBy('verb')
			->get();
	}else{
		$verbArr=Capsule::table('verb')
			->where('cate1',$cate1)
			->where('cate2',$cate2)
			->orderBy('verb')
			->get();

	}
	foreach($verbArr as $verbData){
		$content.=oneVerbShow($verbData->verb,false,$verbData->grade);
	}

	$main.="
		<div class='row'>
  			<div class='col-md-3'>{$user}{$menu}</div>
  			<div class='col-md-9'>{$form}{$body}{$content}</div>
  		</div>
	";
	
	return $main;
}

function subNav($arr=array(),$cate1=""){
	$menu="";
	foreach($arr as $k => $v){
		$menu.="
		<li>
			<a href='{$_SERVER['PHP_SELF']}?cate1={$cate1}&cate2={$k}'>{$v}</a>
		</li>";
	}
	return $menu;
}

// function ajaxCheckWord($word=""){
// 	if(!empty($word)){
// 		$num = Capsule::table('verb')
// 			->where('verb',$word)
// 			->count();
// 	}
// 	if(@$num >= 1){
// 		echo '<span class="label label-success"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span>'.$num."</span>";
// 	}else{
// 		echo '<span class="label label-danger"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></span>';
// 	}
	 
// 	exit;
// }

function addWordForm($cate1="",$cate2=""){
	global $cateArr1,$cateArr2;
	$main="
		<form action='' class='form-inline'>
			<div class='form-group'>
				<label>請輸入生字</label><input type='text' name='word' value='' id='tags' oninput='checkWord()'>
			</div>
			<span id='checkarea'></span>
			<div class='form-group'>
				<input type='hidden' name='op' value='addWordCate'>
				<input type='hidden' name='cate1' value='{$cate1}'>
				<input type='hidden' name='cate2' value='{$cate2}'>
				<input type='submit' value='新增生字' class='btn btn-group'>
			</div>
		</form>
	";
	return $main;
}

function addWordCate($word="",$cate1="",$cate2=""){
	if(empty($word)) die("修改失敗");
	Capsule::table('verb')
			->where('verb', $word)
			->update(['cate1'=>$cate1,'cate2'=>$cate2]);
}


// function showVerb($cate=""){
// 	global $cateArr1;

// 	$menu="";
// 	foreach($cateArr1 as $k => $v){
// 		$menu.=" <a class='btn btn-primary col-md-12' href='{$_SERVER['PHP_SELF']}?cate={$k}'>{$v}11</a> ";
// 	}

// 	@$main.="<h3>{$cateArr[$cate]}單字列表</h3>";
// 	$verbArr=Capsule::table('verb')
// 				->where('cate',$cate)
// 				->orderBy('verb')
// 				->get();
// 	foreach($verbArr as $verbData){
// 		$main.=oneVerbShow($verbData->verb);
// 	}
// 	$user=showGoogleId();
// 	$body="
// 		<div class='row'>
// 			<div class='col-md-3'>{$user}{$menu}</div>
// 			<div class='col-md-9'>{$main}</div>
// 		</div>
// 	";
// 	return $body;
// }

function delMyWord($word=""){
	if(!empty($word)){
		Capsule::table('myword')
			->where('word',$word)
			->where('mail',$_SESSION['gmail'])
			->delete();
	}
	
}

function listMyWord(){
	$main="";
	$allArrs=Capsule::table('myword')
			->where('mail', $_SESSION['gmail'])
			->get();
	foreach($allArrs as  $allArr){
		$main.=oneVerbShow($allArr->word,true);
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
$cate1=$request->get('cate1');
$cate2=$request->get('cate2');

switch($op){
	case "ajaxCheckWord":
		ajaxCheckWord($word);
	break;
	case 'addWordCate':
		addWordCate($word,$cate1,$cate2);
		header("location:{$_SERVER['PHP_SELF']}?cate1={$cate1}&cate2={$cate2}");
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
		$main=index($cate1,$cate2);
	break;
}

echo bootstrap($main);




?>