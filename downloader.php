<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>image downloader</title>
<style>
body {font-family: sans-serif;}
#container {width: 580px;margin: 10px auto 0;padding: 10px 20px;}
#content {padding: 20px;border: 5px solid #f1f1f1;}
#url,#category {width: 380px;padding: 10px;border: 1px solid #ddd;}
#category {width: 60px;text-align: center;}
#url:focus,:focus {border-color: #bbb;}
#submit {cursor: pointer;padding: 10px;border: 1px solid #ddd;background: #f1f1f1;}
#submit:hover {background: #ddd;}
#msgBox {max-height: 200px;padding: 10px;border: 1px solid #ddd;overflow: auto;}
#msgBox ul {font-size: 14px;margin: 0;padding: 0;list-style: none;}
#msgBox ul li {word-wrap: break-word;}
.atn {color:#f00;}
</style>
</head>
 
<body>
<div id="container">
<div id="header">
<h1>image downloader</h1>
</div>
 
<div id="content">
<p>[サイトURL][カテゴリ]</p>
<form action="downloader.php" method="post">
<p>
<input type="text" name="url" id="url" placeholder="URLを入力">
<select name="category" size="1" id="category">
<option value="2D">2D</option>
<option value="3D">3D</option>
<option value="flv">flv</option>
</select>
<input type="submit" name="submit" id="submit" value="収集">
</p>
</form>
<?php
error_reporting(1);

//mysql連携
$my_Con = mysql_connect("localhost", "shibu", "tachyon0526");
if($my_Con == false) die("MySQLの接続に失敗しました");
if(!mysql_select_db("test", $my_Con)) die("データベースの選択に失敗しました");                                                                                                                     

if ($_POST["submit"] && !empty($_POST["url"])) {
	$url = h($_POST["url"]);
	$category = $_POST["category"];

	if(!$my_Row = mysql_query("SELECT no FROM " . $category, $my_Con)) die(mysql_error());
	$row = mysql_fetch_array($my_Row);
	$num = $row["no"];    

	//保存先
	$dir = "./Images/2D/";
	if($category == "3D") { 
		$dir = "./Images/3D/";
	}else if($category == "flv"){
		$dir = "./Images/flv/";
	}

	if (!file_exists($dir)) {
		mkdir($dir,0777);
		chmod($dir,0777);
	}

	
	$siteData = file_get_contents($url);
	//猶予は2分間
	set_time_limit(120);

	//動画
	if($category == "flv"){
		//リンク先に指定されている動画を抽出
		preg_match_all("/flv_url=http[0-9a-zA-Z_\/\.\%\&\?-]+\.flv[0-9a-zA-Z_\/\.\%\&\?-]+;/",$siteData,$flvUrl);
		if (!empty($flvUrl[0])) {
			print "<div id=\"msgBox\">\n<ul>\n";
			$val = str_replace("flv_url=", "", $flvUrl[0][0]);
			$val = urldecode($val);
			file_put_contents($dir.$num.".flv",file_get_contents($val));
			print "<li>{$val}を保存しました。</li>\n";
			$num++;
			$my_Rtn = mysql_query("UPDATE " . $category . " SET no=" . $num, $my_Con);
			print "</ul>\n</div>\n";
		} else {
			print "<p class=\"atn\">※保存出来る動画がありません。</p>\n";
		}
	//画像
	}else{
		//リンク先に指定されている画像を抽出
		preg_match_all("/\<[a,A] href=\"http:\/\/[0-9a-zA-Z_\/\.\%\&\?-]+\.(jpg|png|gif)\"(\>|[0-9a-zA-Z_=\"\[\]\(\)\,\%\&\?\s-]+\>)/",$siteData,$imgUrl);
		if (!empty($imgUrl[0])) {
			print "<div id=\"msgBox\">\n<ul>\n";
			foreach ($imgUrl[0] as $val) {
				$val = preg_replace("/\<[a,A] href=\"/","",$val);
				$val = preg_replace("/\"(\>|[0-9a-zA-Z_=\"\[\]\(\)\,\%\&\?\s-]+\>)/","",$val);
				$ext = substr($val,-4);
				//連番で保存
				file_put_contents($dir.$num.$ext,file_get_contents($val));
				print "<li>{$val}を保存しました。</li>\n";
				$my_Rtn = mysql_query("UPDATE " . $category . " SET no=" . $num, $my_Con);
				$num++;
			}
			print "</ul>\n</div>\n";
		} else {
			print "<p class=\"atn\">※保存出来る画像がありません。</p>\n";
		}
	}	
}
 
function h($str) {
	return htmlspecialchars($str,ENT_QUOTES,"utf-8");
}
?>
</div>
<div id="footer">
<p><small>&copy; shibu_t</small></p>
</div>
</div>
</body>
</html>

