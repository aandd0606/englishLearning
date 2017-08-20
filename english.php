<?php
require 'setup.php';
include('class/simplehtmldom_1_5/simple_html_dom.php');
use GuzzleHttp\Client;
$url="http://61.219.112.16";
//資料庫操作
use Illuminate\Database\Capsule\Manager as Capsule;

//取得所有單字，寫入資料庫中
getVerbArrDoSt();
function getVerbArrDoSt(){
	//寫入單字解釋、音檔、存入音檔
	//UPDATE `english`.`verb` SET `verbcheck` = '1' WHERE `verb`.`sn` = 1;
	$allArrs=Capsule::table('verb')
			->where('voice_url', "")
			->get();
	foreach($allArrs as  $allArr){
		getWordVoicetubeVoice($allArr->verb);
	}
	
	// $allArrs=Capsule::table('verb')
	// 		->where('verbcheck', "0")
	// 		->get();
	// foreach($allArrs as  $allArr){
	// 	getVerbVoiceUrlExplain($allArr->verb);
	// 	Capsule::table('verb')
	// 		->where('verb', $allArr->verb)
	// 		->update(['verbcheck'=>1]);
	// }
	
	
	// //寫入longman例句資料getSentenceSanMin
	// $allArrs=Capsule::table('verb')
				// ->where('longman', "0")
				// ->get();
	// foreach($allArrs as  $allArr){
		// echo $allArr->verb;
		// // getVerbVoiceUrlExplain($allArr->verb);
		// //已經寫過的資料不要再讀取
		// if($allArr->longman==1) continue;
		// getWordFromLongman($allArr->verb);
		// Capsule::table('verb')
				// ->where('verb', $allArr->verb)
				// ->update(['longman'=>1]);
	// }
	
	// //寫入三民書局例句資料getSentenceSanMin
	// $allArrs=Capsule::table('verb')
				// ->where('sammin', "0")
				// ->get();
	// foreach($allArrs as  $allArr){
		// // // echo $allArr->verb;
		// // getVerbVoiceUrlExplain($allArr->verb);
		// //已經寫過的資料不要再讀取
		// if($allArr->sammin==1) continue;
		// getSentenceSanMin($allArr->verb);
		// Capsule::table('verb')
				// ->where('verb', $allArr->verb)
				// ->update(['sammin'=>1]);
	// }	
	
}


//取得單字的中文解釋(奇摩字典)、發音(劍橋字典)
// getVerbVoiceUrlExplain("black");
function getVerbVoiceUrlExplain($word=""){
	$verbArr=Capsule::table('verb')
				->where('verb',$word)
				->get();
	foreach($verbArr as $verbData){}
	// die(var_dump($verbData->voice_url));
	if(!empty($verbData->voice_url)) return;//若有資料不要寫入
	
	$explain1=getWordYahoo($word);
	$voice_url=getWordFromCB($word);
	Capsule::table('verb')
				->where('verb', $word)
				->update(['voice_url'=>$voice_url,'explain1'=>$explain1]);
}



// getSentenceSanMin('twelve');
//從三民書局取得資料
function getSentenceSanMin($word=""){
	echo $word;
	//取得查詢單字頁面
	$client = new Client();
	$response = $client->request('POST', 'http://61.219.112.16/english/query.asp', [
		'form_params' => [
			'keyword' => $word,
			'B1' => '精確搜尋'
			]
	]);
	$body = $response->getBody();
	$pageHtml=$body->getContents();
	$pageHtml=iconv('big5','utf-8//IGNORE',$pageHtml);
	
	//取得內容資料
	$html = new simple_html_dom();
	$html->load($pageHtml);
	
	//先判斷有無內容
	$ret = $html->find('p',0)->innertext;
	// die(strip_tags($ret));
	if(strip_tags($ret) == "抱歉！未找到相關例句，請回上一頁重新輸入搜尋條件。") return;
		
	$ret = $html->find('table',0)->find('tr');
	foreach($ret as $trs){
		$arr=null;
		$tr=$trs->innertext;
		$tr_dom = new simple_html_dom();
		$tr_dom->load($tr);
		@$getVerb=strip_tags($tr_dom->find('td',3)->innertext);
		if($getVerb!=$word) continue;
		@$verb_tds=$tr_dom->find('td',3)->innertext;
		@$sentence_tds=$tr_dom->find('td',6)->innertext;
		$verb=$word;
		$sentenceArr=explode("<br>",$tr_dom->find('td',6)->innertext);
		$sentence=strip_tags($sentenceArr[0]);
		$explanation=strip_tags($sentenceArr[1]);
		$voice_url=getVoiceLink($sentence_tds);
		$voice_path=parse_url($voice_url);
		// echo $voice_path['path'];
		$voice_path="voice{$voice_path['path']}";
			$arr=array(
				"verb"=>$verb,
				"sentence"=>$sentence,
				"explanation"=>$explanation,
				"voice_url"=>$voice_path,
				"source"=>"sammin"
			);
		$num=Capsule::table('sentence')->where("voice_url",$voice_path)->count();
		if($num<1){
			Capsule::table('sentence')->insert($arr);
			downloadFile($voice_path,$voice_url);
		}
		
		// // // // echo @strip_tags($tr_dom->find('td',3)->innertext) . " | ";
		// // // // //echo getVoiceLink($verb_tds) . " | ";//單字音檔連結不需要
		// // // // echo @strip_tags($tr_dom->find('td',4)->innertext) . " | ";
		// // // // echo @strip_tags($tr_dom->find('td',5)->innertext) . " | ";
		// // // // //echo @$tr_dom->find('td',6)->innertext . " | ";
		// // // // echo @$sentence . " | ";
		// // // // echo @$explaination . " | ";
		// echo getVoiceLink($sentence_tds) . " | <br>\n";
		// // die($voice_path);
		
		
	}
	sleep(1);
}
// getWordYahoo("difficult");
//從YAHOO字典取得單字解釋
function getWordYahoo($word=""){
	//取得查詢單字頁面
	$client = new Client();
	$response = $client->request('GET', "https://tw.dictionary.yahoo.com/dictionary?p={$word}");
	$body = $response->getBody();
	$pageHtml=$body->getContents();
	// $pageHtml=iconv('big5','utf-8//IGNORE',$pageHtml);
	// echo $pageHtml;
	// //取得內容資料
	$html = new simple_html_dom();
	$html->load($pageHtml);
	// // //取得音檔連結
	// // $voice=$html->find('span[class=circle circle-btn sound audio_play_button us]',0)->attr;
	// // echo $voice['data-src-mp3'];
	// // // echo $voice;
	//取得奇摩字典的解釋
	$explaination=$html->find('div[class=DictionaryResults]',2)->innertext;
	return $explaination;
}

// getWordVoicetubeVoice("book");
// //從YAHOO字典取得同義反義詞性變化單字解釋
// function getWordVoicetubeVoice($word=""){
// 	//取得查詢單字頁面
// 	$client = new Client();
// 	$response = $client->request('GET', "https://tw.voicetube.com/definition/{$word}");
// 	$body = $response->getBody();
// 	$pageHtml=$body->getContents();

// 	$html = new simple_html_dom();
// 	$html->load($pageHtml);
// 	echo $html;
// 	// $explaination=$html->find('div[class=synonym grammar]')->innertext;
// 	// $explaination=$html->find('div[class=pronun]',0)->innertext;
// 	// var_dump($explaination);

// 	// return $explaination;
// }


// getWordFromCB("red");
//從劍橋字典取得單字音檔
function getWordFromCB($word=""){
	//取得查詢單字頁面
	$client = new Client();
	$response = $client->request('GET', "http://dictionary.cambridge.org/zht/%E8%A9%9E%E5%85%B8/%E8%8B%B1%E8%AA%9E-%E6%BC%A2%E8%AA%9E-%E7%B9%81%E9%AB%94/{$word}");
	$body = $response->getBody();
	$pageHtml=$body->getContents();
	// $pageHtml=iconv('big5','utf-8//IGNORE',$pageHtml);
	// echo $pageHtml;
	// //取得內容資料
	$html = new simple_html_dom();
	$html->load($pageHtml);
	//取得音檔連結
	$voice=$html->find('span[class=circle circle-btn sound audio_play_button us]',0)->attr;
	$voice_url=strip_tags($voice['data-src-mp3']);
	$voice_path=strip_tags(str_replace("http://dictionary.cambridge.org/zht/media/english-chinese-traditional","",$voice_url));
	if(!empty($voice_path)){
		$voice_path="voice{$voice_path}";
	}
	// $voice_path=($voice_path)?"voice{$voice_path}":"";
	downloadFile($voice_path,$voice_url);
	return $voice_path;
	
}

//取得聲音檔的連結
function getVoiceLink($tds=""){
	global $url;
	$td_dom = new simple_html_dom();
	$td_dom->load($tds);
	$link=$td_dom->find('a',0)->onmouseover;
	$link=substr($link,6,-2);
	$Link_test=substr($link,0,4);
	if($Link_test != "http"){
		$link=$url.$link;
	}
	
	return $link;
}
// getWordVoicetube("book");
// //取得voicetube單字音檔、例句、中文解釋，但無發音
// function getWordVoicetube($verb=""){
// 	$client = new Client();
// 	$response = $client->request('GET', "https://tw.voicetube.com/definition/{$verb}");
// 	$body = $response->getBody();
// 	$pageHtml=$body->getContents();
// 	$html = new simple_html_dom();
// 	$html->load($pageHtml);
// 	$ret = $html->find('div[id=definition]',0)->innertext;
// 	echo $ret;
// }

// getWordVoicetubeVoice("evilly");
//取得voicetube單字音檔、例句、中文解釋，但無發音
function getWordVoicetubeVoice($verb=""){
	$client = new Client();
	$response = $client->request('GET', "https://tw.voicetube.com/definition/{$verb}");
	$body = $response->getBody();
	$pageHtml=$body->getContents();
	$html = new simple_html_dom();
	$html->load($pageHtml);
	$ret = $html->find('a[class=audioButton]',0)->attr;
	$voice_url = "https://tw.voicetube.com".strip_tags($ret['href']);
	$voice_path = strip_tags($ret['href']);
	if(!empty($voice_path)){
		$voice_path="voice{$voice_path}";
	}
	echo $voice_url;
	// var_dump(curl_get_contents($voice_url));
	downloadFile($voice_path,$voice_url);
	Capsule::table('verb')
			->where('verb', $verb)
			->update(['voice_url'=>$voice_path]);
	// return $voice_path;
}

function curl_get_contents($url)
{
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}


//取得例句以及音檔，但是沒有中文翻譯
//http://www.ldoceonline.com/dictionary/book
// getWordFromLongman("black");
function getWordFromLongman($verb=""){
	//取得查詢單字頁面
	$client = new Client();
	$response = $client->request('GET', "http://www.ldoceonline.com/dictionary/{$verb}");
	$body = $response->getBody();
	$pageHtml=$body->getContents();
	
	$html = new simple_html_dom();
	$html->load($pageHtml);
	$ret = $html->find('span[class=EXAMPLE]');
	foreach($ret as $spans){
		$arr=null;
		$span=$spans->innertext;
		$span_dom = new simple_html_dom();
		$span_dom->load($span);
		// $span_dom->find('span');
		if($span_dom->find('span')){
			$link=$span_dom->find('span',0)->attr;
			$span=strip_tags($span);
			if(empty($link['data-src-mp3'])) continue;
			$sentence=strip_tags($span);
			$explanation="";
			$voice_url=trim(str_replace("&#10;","",strip_tags($link['data-src-mp3'])));
			$voice_path=parse_url($voice_url);
			// if(!empty($voice_path['path'])){
				// $voice_path['path']="voice{$voice_path}";
			// }
			// $voice_path['path']=(!empty($voice_path['path']))?"voice{$voice_path}":"";
			$voice_path['path']="voice".$voice_path['path'];
			$arr=array(
				"verb"=>$verb,
				"sentence"=>$sentence,
				"explanation"=>$explanation,
				"voice_url"=>$voice_path['path'],
				"source"=>"longman"
			);
			$num=Capsule::table('sentence')->where("voice_url",$voice_path['path'])->count();
			if($num<1){
				Capsule::table('sentence')->insert($arr);
				downloadFile($voice_path['path'],$voice_url);
			}
			
			// var_dump($arr);
			//成功取得$span例句句子，以及連結音檔$link['data-src-mp3']
			// echo strip_tags("{$span}{$link['data-src-mp3']}<br>\n");
		 }
	}
}


// copyRemote('http://61.219.112.16/snd/3m/99/h/101/wp10.wav','../voice/snd/3m/99/h/101/wp10.wav');
// downloadFile('http://61.219.112.16/snd/3m/99/h/101/wp10.wav','../voice/snd/3m/99/h/101/wp10.wav');
// file_put_contents("voice/snd/3m/99/h/101/wp10.wav", file_get_contents("http://61.219.112.16/snd/3m/99/h/101/wp10.wav"));
// downloadFile("voice/snd/3m/99/h/101/wp10.wav","http://61.219.112.16/snd/3m/99/h/101/wp10.wav");

function downloadFile($path, $url){
	if(!is_dir(dirname($path))){
		mkdir(dirname($path).'/', 0777, TRUE);
	} 

	// 平常用這個下載
	// file_put_contents($path,file_get_contents($url));
	// 若有擋agent使用這個	
	file_put_contents($path,curl_get_contents($url));

}



// function downloadUrlToFile($url, $outFileName)
// {   
    // if(is_file($url)) {
        // copy($url, $outFileName); 
    // } else {
        // $options = array(
          // CURLOPT_FILE    => fopen($outFileName, 'w'),
          // CURLOPT_TIMEOUT =>  28800, // set this to 8 hours so we dont timeout on big files
          // CURLOPT_URL     => $url
        // );

        // $ch = curl_init();
        // curl_setopt_array($ch, $options);
        // curl_exec($ch);
        // curl_close($ch);
    // }
// }

// //下載聲音檔的連結
// // function copyRemote($fromUrl, $toFile) {
    // // try {
        // $client = new Client();
        // $response = $client->get("http://61.219.112.16/snd/3m/99/h/101/wp10.wav")
            // // ->setAuth('login', 'password') // in case your resource is under protection
            // ->setResponseBody("../voice/snd/3m/99/h/101/wp10.wav")
            // ->send();
        // // return true;
    // // } catch (Exception $e) {
        // // // Log the error or something
        // // return false;
    // // }
// // // }
?>