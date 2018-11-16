<?php
/* HTML特殊文字をエスケープする関数を定義 */
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

try {

    // バッファリングを開始
    // ob_start();

	//アップロードファイルの例外処理
	switch ($_FILES['upfile']['error']) {
	    case UPLOAD_ERR_OK: // OK
	    case UPLOAD_ERR_NO_FILE: // ファイル未選択
	        break;
		case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズ超過
	    case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過 (設定した場合のみ)
	        throw new RuntimeException('ファイルサイズが大きすぎます');
	    default:
	        throw new RuntimeException('その他のエラーが発生しました');
	}

	// 送信されたとき
	if (!empty($_POST['upload'])) {

		// 未定義である・複数ファイルである・$_FILES Corruption 攻撃を受けた
		// どれかに該当していれば不正なパラメータとして処理する
		// is_int(); 変数が整数型ならTRUE
		if (!isset($_FILES['upfile']['error']) || !is_int($_FILES['upfile']['error'])) {
			throw new RuntimeException('パラメータが不正です');
		} else {

			$rawData = file_get_contents($_FILES["upfile"]["tmp_name"]); //バイナリデータを取得
			$date = getdate(); //時刻を取得
			$mime = $_FILES["upfile"]["type"] ; //MIMEタイプを判定

			// 拡張子を決定
			switch ($mime) {
				case "image/jpeg":
					$extension = ".jpeg";
					break;
				case "image/png":
					$extension = ".png";
					break;
				case "image/gif":
					$extension = ".gif";
					break;
				case "video/mp4":
					$extension = ".mp4";
					break;
				default:
					throw new RuntimeException("非対応ファイルです");
			}

			// バイナリデータと時刻を合わせてハッシュ化
			$hashname = hash("sha256", $rawData.$date["year"].$date["mon"].$date["mday"].$date["hours"].$date["minutes"].$date["seconds"]);
			$filename = $hashname.$extension ;

			//ファイルを特定のフォルダへ移動
			if (move_uploaded_file($_FILES["upfile"]["tmp_name"], "files/" . $filename)) {
		    	$uploadMessage = $_FILES['upfile']['name']." をアップロードしました";

				if ("$mime" === "video/mp4") { //動画のとき
					$format = '<video src="/files/%s" controls autoplay></video>' ;
				} else { //画像のとき
					$format = '<img src="/files/%s">' ;
				}

			} else {
		    	$errorMessage = "ファイルをアップロードできません";
			} //フォルダ移動
		} //パラメータ確認
	} //送信されたとき

} catch (RuntimeException $e) {
	$errorMessage = $e->getMessage();
}
?>


<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>sample</title>
</head>
<body>

<div><font color="red"><?php echo h($errorMessage); ?></font></div>
<div><font color="blue"><?php echo h($uploadMessage); ?></font></div>

<form action="" method="post" enctype="multipart/form-data">
	<!--- ファイルサイズ制限、10MB --->
	<input type="hidden" name="MAX_FILE_SIZE" value="10485760">
	ファイル(10MBを超えるファイルは送信できません): <br>
	<input type="file" name="upfile" size="30" /><br>
	<br>
	<input type="submit" name="upload" value="アップロード" />
</form>

<!---
sprintf(フォーマットしたいもの, 1つ目の%sなどに入れるもの, 2つ目, etc.)
--->
<?php echo sprintf($format, $filename) ?>

<br>
<a href="">ページへ移動</a>


</body>
</html>
