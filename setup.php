<?php
session_start();
date_default_timezone_set("Asia/Taipei");
require 'vendor/autoload.php';
use Illuminate\Database\Capsule\Manager as Capsule;
//資料庫設定
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'english',
    'username'  => 'root',
    'password'  => 'aandd!@#$%^aandd0606',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);
// Set the event dispatcher used by Eloquent models... (optional)
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
$capsule->setEventDispatcher(new Dispatcher(new Container));
// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();
// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();


$gradeData_arr=array(
	"3" =>		"3年級",
	"4" =>		"4年級",
	"5" =>		"5年級",
	"6" =>		"6年級",
	"7" =>		"7年級",
	"8" =>		"8年級",
	"9" =>		"9年級",
	"10" =>		"10年級",
	"11" =>		"11年級",
	"12" =>		"12年級",
	"13" =>	"大學1年級",
	"14" =>	"大學2年級",
	"15" =>	"大學3年級",
	"16" =>	"大學4年級"
);

$menu_arr=[
	'首頁'=>'index.php',
	'單字查詢'=>'index.php?op=searchForm',
	'我的生字'=>'myword.php',
	'分類單字'=>'cate.php',
	];
$cateArr1=array(
	'food'=>'飲食類',
	'life'=>'日常生活',
	'leisure'=>'休閒',
	'Arts'=>'文藝',
	'family'=>'家與家庭',
	'medicine'=>'醫療與疾病',
	'body'=>'身體與活動',
	'psychological'=>'心理活動',
	'Personality'=>'人格、人生與宗教',
	'Academic'=>'學術',
	'natural'=>'自然與生物',
	'society'=>'社會科學',
	'political'=>'政治與軍事',
	'traffic'=>'交通與建築',
	'status'=>'時空、狀態與程度',
	'Finance'=>'財經、商務與管理',
	'communication'=>'通訊與傳播',
	'industry'=>'產業',
	'other'=>'虛詞與其他',
	);
$cateArr2=array(
	'food'=>array(
		'fruit'=>'水果',
		'dessert'=>'點心甜品',
		'drink'=>'飲品',
		'vegetables'=>'蔬菜豆類與堅果',
		'dish'=>'餐點與菜餚',
		'cooking'=>'烹飪與調味',
		'food'=>'飲食',
		),
	'life'=>array(
		'daily'=>'日常生活',
		'computer'=>'電腦',
		'mail'=>'郵件',
		'electricity'=>'電與動力',
		'hair'=>'美容美髮',
		'clothes'=>'衣著與時尚',
		'material'=>'紡織材料與質地',
		'jewelry'=>'珠寶',
		'shapes'=>'形狀與圖樣',
		'colors'=>'顏色',
		'sounds'=>'聲音',
		'education'=>'教育',
		'insurance'=>'保險',
		),
	'leisure'=>array(
		'film'=>'電影',
		'sport'=>'運動',
		'baseball'=>'棒球',
		'gardening'=>'園藝',
		'recreation'=>'遊藝',
		'outdoor'=>'戶外活動',
		'leisure'=>'休閒',
		'tourism'=>'旅遊',
		),
	'Arts'=>array(
		'arts'=>'藝術',
		'painting'=>'繪畫',
		'performing'=>'表演',
		'theater'=>'戲劇',
		'photography'=>'攝影',
		'music'=>'音樂',
		'tradition'=>'民俗與傳說',
		),
	'family'=>array(
		'family'=>'家庭關係',
		'household'=>'家用設備',		
		'house'=>'住宅',		
		'domestic'=>'家務',		
		'children'=>'兒童',		
		'youth'=>'青少年',		
		'tools'=>'工具',		
		'cleaning'=>'清潔',		
		'utensils'=>'器具',		
		),
	'medicine'=>array(
		'hospital'=>'醫院',
		'illness'=>'疾病與失能',		
		'medicine'=>'藥品與藥物濫用',		
		'tobacco'=>'菸品',				
		),
	'body'=>array(
		'human'=>'人體',
		'movement'=>'人體動作',		
		'activites'=>'活動',		
		'expression'=>'表達',		
		),
	'psychological'=>array(
		'thoughts'=>'想法',
		'willingness'=>'意願',		
		'emotion'=>'情緒與態度',		
		),
	'Personality'=>array(
		'personality'=>'性格',
		'gender'=>'性別',		
		'ability'=>'能力',		
		'outlook'=>'外型',		
		'interpersonal'=>'人際',		
		'birth'=>'生育',		
		'death'=>'死亡',		
		'religion'=>'宗教',			
		),
	'Academic'=>array(
		'anthropology'=>'人類學',
		'astronomy'=>'天文學',		
		'biology'=>'生物學',		
		'chemistry'=>'化學',		
		'elements'=>'元素',		
		'economics'=>'經濟學',		
		'history'=>'歷史學',		
		'linguistics'=>'語言學',		
		'grammar'=>'文法',		
		'literature'=>'文學',		
		'math'=>'數學',		
		'meteorology'=>'氣象學',		
		'nutrition'=>'營養學',		
		'philosophy'=>'哲學',		
		'physics'=>'物理學',		
		'politics'=>'政治學',		
		'psychology'=>'心理學',		
		'sociology'=>'社會學',		
		'geography'=>'地理ˇ',		
		'science'=>'科學',		
		),
	'natural'=>array(
		'animals'=>'動物',
		'aquatics animals'=>'水生動物',		
		'birds'=>'鳥類',		
		'insects'=>'昆蟲',		
		'plants'=>'植物',		
		'environment'=>'環境保護',		
		'nature'=>'自然',		
		'odors'=>'氣味',		
		'air'=>'空氣',		
		'water'=>'水',		
		),
	'society'=>array(
		'citizenship'=>'公民',
		'nationality'=>'國籍與種族',
		'organization'=>'組織',
		'crime'=>'罪與罰',
		'law'=>'法律',
		'police'=>'警察',
		'welfare'=>'福利',
		),
	'political'=>array(
		'government'=>'政府',
		'official'=>'官員',
		'voting'=>'投票',
		'weapons'=>'武器',
		'military'=>'軍事',
		),
	'traffic'=>array(
		'motor'=>'汽機車',
		'roads'=>'道路',
		'transport'=>'運輸',
		'buildings'=>'建築與建設',
		),
	'status'=>array(
		'frequency'=>'時間順序與頻率',
		'space'=>'空間位置與方位',
		'measurement'=>'測量與單位ˋ',
		'numbers'=>'數字與數量',
		'conditions'=>'狀態與性質',
		'degree'=>'程度',
		),
	'Finance'=>array(
		'banking'=>'貨幣與銀行',
		'finance'=>'財務',
		'management'=>'管理',
		'commerce'=>'貿易',
		'enterprise'=>'企業',
		'employment'=>'聘僱',
		),
	'communication'=>array(
		'marketing'=>'廣告與行銷',
		'telephone'=>'電話與電報',
		'television'=>'電視與電台',
		'visual'=>'視覺',
		),
	'industry'=>array(
		'agriculture'=>'農業',
		'industry'=>'工業',
		'newspapers'=>'報紙與出版業',
		'technology'=>'科技',
		'occupation'=>'各行各業',
		),
	'other'=>array(
		'articles'=>'冠詞',
		'conjunctions'=>'連接詞',
		'prepositions'=>'介係詞',
		'pronouns'=>'代名詞',
		'miscellaneous'=>'其他',
		),
	);

	
$top_nav=dy_nav($menu_arr);

/********************* 自訂函數 *********************/
function arrayToSelect($arr,$option=true,$default_val="",$use_v=false,$validate=false){
	if(empty($arr))return;
	$opt=($option)?"<option value=''>請選擇</option>\n":"";
	foreach($arr as $i=>$v){
		//false則以陣列索引值為選單的值，true則以陣列的值為選單的值
		$val=($use_v)?$v:$i;
		$selected=($val==$default_val)?'selected="selected"':"";        //設定預設值
		$validate_check=($validate)?"class='required'":"";
		$opt.="<option value='$val' $selected $validate_check>$v</option>\n";
	}
	return  $opt;
}

function arrayToRadio($arr,$use_v=false,$name="default",$default_val=""){
    	if(empty($arr))return;
    	$opt="";
    	foreach($arr as $i=>$v){
    		$val=($use_v)?$v:$i;
    		$checked=($val==$default_val)?"checked='checked'":"";
    		$opt.="<input type='radio' name='{$name}' id='{$val}' value='{$val}' $checked><label for='{$val}' style='display:inline;margin-right:15px;'> $v</label>";
    	}
    	return $opt;
}

function arrayToRadioBS2($arr,$use_v=false,$name="default",$default_val=""){
    	if(empty($arr))return;
    	$opt="";
    	foreach($arr as $i=>$v){
    		$val=($use_v)?$v:$i;
    		$checked=($val==$default_val)?"checked='checked'":"";
    		$opt.="<label class='radio inline'><input type='radio' name='{$name}' id='{$val}' value='{$val}' $checked>$v</label>";
    	}
    	return $opt;
}

function arrayToCheckbox($arr,$name,$default_val="",$use_v=false){
	//<input type="checkbox" name="option1" value="Milk">\
	$default_valarr=explode(",",$default_val);
	//die(var_dump($default_valarr));
	if(empty($arr))return;
	foreach($arr as $i=>$v){
		//false則以陣列索引值為選單的值，true則以陣列的值為選單的值
		$val=($use_v)?$v:$i;
		$selected=(in_array($val,$default_valarr))?"checked":"";        //設定預設值
		$opt.=" <label class='checkbox' for='stu_{$val}'><input type='checkbox' name='{$name}' value='{$val}' id='stu_{$val}' {$selected}>{$v}</label> ";
	}
	return  $opt;
}


//自定輸出錯誤訊息
function die_content($content=""){
    $main="
		<!DOCTYPE html>
		<html lang='zh-Hant-tw'>
		<head>
		<meta charset='utf-8'>
		<title>輸出錯誤訊息</title>
		<meta name='viewport' content='width=device-width, initial-scale=1.0'>
		<meta name='description' content='輸出錯誤訊息'>
		<meta name='author' content='aandd'>
		<!--引入JQuery CDN-->
		<script src='https://code.jquery.com/jquery-2.1.4.min.js'></script>
		<!--引入Bootstrap 3 CDN---->
		<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css'>
		<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css'>
		<script src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js'></script>
		</head>
		<body>
		<!--放入網頁主體-->
		<div class='container'>
		  <!-- 主要內容欄位開始 -->
		  <div class='row'>
			<div class='col-md-12 col-sm-12'>
				<div class='jumbotron'>
				  <h1>輸出錯誤訊息</h1>
				  <p>{$content}</p>
				</div>
			</div>
		  </div>
		  <!-- 主要內容欄位結束 -->
		</div> 
		<!-- 主要內容欄位結束 -->
		</body>
		</html>
	";
    die($main);
}

//產生動態導覽列
function dy_nav($page_menu=array()){
    global $title;
    $main="
    <!-- Fixed navbar -->
    <nav class='navbar navbar-default navbar-fixed-top'>
      <div class='container'>
        <div class='navbar-header'>
          <button type='button' class='navbar-toggle collapsed' data-toggle='collapse' data-target='#navbar' aria-expanded='false' aria-controls='navbar'>
            <span class='sr-only'>Toggle navigation</span>
            <span class='icon-bar'></span>
            <span class='icon-bar'></span>
            <span class='icon-bar'></span>
          </button>
          <a class='navbar-brand' href='#'>{$title}</a>
        </div>
        <div id='navbar' class='navbar-collapse collapse'>
          <ul class='nav navbar-nav'>";
		  //$file_name=basename($_SERVER['PHP_SELF']);
			$file_name=basename($_SERVER['REQUEST_URI']);
			foreach($page_menu as $i=>$v){
				$class=($file_name==$v)?"class='active'":"";
				$main.="<li {$class}><a href='{$v}'>{$i}</a></li>";
			}
          $main.="</ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>	
	
	
	";
	
	
	
	
    return $main;
}

function bootstrap($content="",$js_link="",$css_link="",$js_fun=""){
    global $top_nav,$title;
	$main="
	<!DOCTYPE html>
	<html lang='zh-Hant-tw'>
	<head>
	<meta charset='utf-8'>
	<title>{$title}</title>
	 <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <meta name='description' content='{$title}'>
        <meta name='author' content='aandd'>
        <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css'>
		<link href='https://maxcdn.bootstrapcdn.com/bootswatch/3.3.5/cerulean/bootstrap.min.css' rel='stylesheet'>	
		<script src='https://code.jquery.com/jquery-2.1.4.min.js'></script>
		<script src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js'></script>
        <style type='text/css'>
          body {
            padding-top: 60px;
            padding-bottom: 20px;
          }
        </style>
        <!--引入額外的css檔案以及js檔案開始-->
        {$js_link}
        {$css_link}
        <!--引入額外的css檔案以及js檔案結束-->
        <!--jquery語法開始-->
        {$js_fun}
        <!--jquery語法結束-->
        </head>
        <body>
	<!--放入網頁主體-->
	{$top_nav}
	<div class='container'>
	  <!-- 主要內容欄位開始 -->
	  {$content}
	  <!-- 主要內容欄位結束 -->
	</div> 
	<!-- 主要內容欄位結束 -->
	</body>
	</html>
	
	";

    return $main;
}

function showGoogleId(){
  if(isset($_SESSION['picture']) AND isset($_SESSION['name']) AND isset($_SESSION['gmail'])){
    $main="
    <img class='img-circle center-block' src='{$_SESSION['picture']}'>
    <a href='http://ptenglish.ptc.edu.tw/login.php?logout=logout'><img src='img/logout.png' style='width:100px' class='center-block'></a>
    Hellow：{$_SESSION['name']}。Mail：{$_SESSION['gmail']}
    
  ";
    return $main;
  }else{
    header("location:http://ptenglish.ptc.edu.tw/login.php");
  }
}

function oneVerbShow($word="",$showDel=false,$grade=""){
	$allArrs=Capsule::table('myword')
			->join('verb', 'verb.verb', '=', 'myword.word')
			->where('myword.mail', $_SESSION['gmail'])
			->where('myword.word', $word)
			->get();
	foreach($allArrs as $allArr){
	}	

	if($showDel){
		$del="<a href='{$_SERVER['PHP_SELF']}?op=delMyWord&word={$word}' onclick=\"return confirm('是否確認刪除這筆資料');\">
		<span class='glyphicon glyphicon-remove'></span>
		</a>";
	}else{
		$del="";
	}
	$grade_content=$grade?$grade:"";
	 $main="
		<blockquote class='col-md-4'>
		<p>
			<a href='{$_SERVER['PHP_SELF']}?op=verbShow&verb={$word}'>
				{$word} 
			</a>
			<span class='badge alert-danger'> {$grade_content} </span>
			<span class='badge alert-success'> ". @$allArr->readtime ." </span>
			<span class='badge alert-warning'> ". @$allArr->inserttime ." </span>
			{$del}
		</p>
		</blockquote>
	 ";
	 return $main;
}
//顯示一個單字資訊
function verbShow($verb=""){
	addReadTime($verb);
	$verbArr=Capsule::table('verb')
				->where('verb',$verb)
				->get();
	foreach($verbArr as $verbData){
	// var_dump($verbData);	
	$content="";
	$sentenceArr=Capsule::table('sentence')
				->where('verb',$verb)
				->get();
	foreach($sentenceArr as $sentenceData){
		$content.="<p>
				<span class='sentance' data-ssn={$sentenceData->s_sn}>{$sentenceData->sentence}</span>
				<audio id='voice_{$sentenceData->s_sn}' src='http://ptenglish.ptc.edu.tw/{$sentenceData->voice_url}'  preload='auto'></audio>
				<br>
				{$sentenceData->explanation}
		</p>";
	}
	if($_SERVER['PHP_SELF'] != "/myword.php"){
		$addMyWord="<a href='{$_SERVER['PHP_SELF']}?op=addWord&word={$verb}'><span class='glyphicon glyphicon-question-sign'></span></a>";
	}else{
		$addMyWord="";
	}
	$main="
	<script>
		$(document).ready(function(){
			$('.sentance').click(function(){
				var ssn = \$(this).attr('data-ssn');
				var vocice_id = 'voice_' + ssn;
				var voice = $('#' + vocice_id);
				var src = voice.attr('src');
				document.getElementById(vocice_id).src =src;
				document.getElementById(vocice_id).play();
			});
			
			$('.verb').click(function(){
				var sn = \$(this).attr('data-sn');
				var verb_id = 'verb_' + sn;
				//alert(verb_id);
				var verb = $('#' + verb_id);
				var src = verb.attr('src');
				document.getElementById(verb_id).src =src;
				document.getElementById(verb_id).play();
			});
		});
	</script>
	<h1 class='verb' data-sn='{$verbData->sn}'>
		<audio id='verb_{$verbData->sn}' src='http://ptenglish.ptc.edu.tw/{$verbData->voice_url}'  preload='auto'>
		</audio>
		{$verb}
		{$addMyWord}
	</h1>
	<div class=row>
		<div class='col-md-6'>
		{$verbData->explain1}
		</div>
		<div class='col-md-6'>
		<h1 title='點選例句可以發出聲音'>例句</h1>
		{$content}
		</div>			
	</div>
	";

	return $main;
	}
}

function addReadTime($word=""){
	if(!empty($word)){
		$num=Capsule::table('myword')
			->where("word",$word)
			->where("mail",$_SESSION['gmail'])
			->count();
		if($num>=1){
			Capsule::table('myword')
				->where('word', $word)
				->where("mail",$_SESSION['gmail'])
				->increment('readtime');
		}
	}
}

function addWord($word="",$mail=""){
	//先檢查是否有這個字
	$u=Capsule::table('verb')
			->where("verb",$word)
			->count();
	if($u<1){
		die_content("本系統沒有這個單字");
	}

	$arr=array(
		"word"=>$word,
		"mail"=>$mail,
		"inserttime"=>'1'
	);
	// die(var_dump($arr));	
	if(isset($word) AND isset($mail)){
		$num=Capsule::table('myword')
			->where("word",$word)
			->where("mail",$_SESSION['gmail'])
			->count();
		if($num>=1){
			Capsule::table('myword')
				->where('word', $word)
				->where("mail",$_SESSION['gmail'])
				->update(['showword' => 'true']);
			Capsule::table('myword')
				->where('word', $word)
				->where("mail",$_SESSION['gmail'])
				->increment('inserttime');
		}else{
			Capsule::table('myword')->insert($arr);

		}
	}	
}
function ajaxCheckWord($word=""){
	if(!empty($word)){
		$num = Capsule::table('verb')
			->where('verb',$word)
			->count();
	}
	if(@$num >= 1){
		echo '<span class="label label-success"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span>'.$num."</span>";
	}else{
		echo '<span class="label label-danger"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></span>';
	}
	 
	exit;
}

function jsCheckWord(){
	$main="
	  <script>
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
	return $main;
}


?>