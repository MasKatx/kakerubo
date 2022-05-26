<?php
session_start();
?>

<!DOCTYPE html>
<html lang="ja">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>家計簿管理サイト|kakerubo-共通設定</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="家計簿を手軽に、詳細に管理するウェブサイトkakerubo 
    自分の使いやすいようにアレンジしたり、設定によって使いすぎを防止したり…… あなたの生活をきっとより便利に、豊かにするシステムです。">
	<link rel="stylesheet" href="css/style.css">
	<script src="js/openclose.js"></script>
	<script src="js/fixmenu.js"></script>
	<script src="js/fixmenu_pagetop.js"></script>
	<script src="js/ddmenu_min.js"></script>
	<?php require_once("util.php"); ?>
	<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body>

	<?php
	// submitによる送信かどうかの確認と、値の格納
	if(isSet($_POST["calendar"])){
		$calendar = $_POST["calendar"];
		$cldflg = true;
	} else {
		$calendar = "sun";
		$cldflg = false;
	}

	if(isSet($_POST["default"])){
		$default = $_POST["default"];
		$defflg = true;
	} else {
		$default = "false";
		$defflg = false;
	}

	if($cldflg || $defflg){
		// cookieの設定(切れないように長期間で設定)
		setcookie("default", $default, time() + (20 * 365 * 24 * 60 * 60));
		setcookie("calendar", $calendar, time() + (20 * 365 * 24 * 60 * 60));
	}

	function checked(string $value, array $checkedValues){
		$isChecked = in_array($value, $checkedValues);
		if($isChecked){
			echo "checked";
		}
	}
	?>

	<div id="container">

		<header>
			<h1 id="logo"><a href="index.html"><img src="images/logo.png" alt="SAMPLE COMPANY"></a></h1>
		</header>

		<!--PC用（801px以上端末）メニュー-->
		<nav id="menubar" class="nav-fix-pos">
			<ul class="inner">
				<li><a href="index.html">HOME<span>ホーム</span></a></li>
				<li><a href="about.html">ABOUT<span>当サイトについて</span></a></li>
				<li><a href="javascript:void(0)" class="cursor-default">MANAGE<span>家計簿の管理</span></a>
					<ul class="ddmenu">
						<li><a href="howtouse.html">家計簿の使い方</a></li>
						<li><a href="create.php">家計簿の新規作成</a></li>
						<li><a href="search.php">家計簿の検索</a></li>
					</ul>
				</li>
				<li><a href="setting.php">SETTING<span>基本設定</span></a></li>
				<li><a href="help.html">HELP<span>ヘルプ</span></a></li>
			</ul>
		</nav>

		<!--小さな端末用（800px以下端末）メニュー-->
		<nav id="menubar-s">
			<ul>
				<li><a href="index.html">HOME<span>ホーム</span></a></li>
				<li><a href="about.html">ABOUT<span>当サイトについて</span></a></li>
				<li><a href="javascript:void(0)" class="cursor-default">MANAGE<span>家計簿の管理</span></a>
					<ul class="ddmenu">
						<li><a href="howtouse.html">家計簿の使い方</a></li>
						<li><a href="create.php">家計簿の新規作成</a></li>
						<li><a href="search.php">家計簿の検索</a></li>
					</ul>
				</li>
				<li><a href="setting.php">SETTING<span>基本設定</span></a></li>
				<li><a href="help.html">HELP<span>ヘルプ</span></a></li>
			</ul>
		</nav>

		<div id="contents">

			<div class="inner">

				<section>

					<h2>SETTING<span>基本設定</span></h2>

					<h3>webサイト自体の設定を行うことができます</h3>

					<form method="POST" action="<?php echo es($_SERVER['PHP_SELF']); ?>">
						<table class="ta1">
							<caption>基本設定</caption>
							<tr>
								<th style="width: 220px;">家計簿カレンダーの表示</th>
								<td>
									<input type="radio" id="sunStart" name="calendar" value="sun" <?php checked("sun", [$calendar]); ?>>
									<label for="sun">日曜始まり</label><br>
									<input type="radio" id="monStart" name="calendar" value="mon" <?php checked("mon", [$calendar]); ?>>
									<label for="mon">月曜始まり</label>
								</td>
							</tr>
							<tr>
								<th>デフォルトの家計簿(デバイス毎に一つだけ設定できます)</th>
								<td>
									<?php
									// デフォルトを設定する場合はIDとパスワードを必要とする(存在するかつ一致する場合のみ登録可能)
										if($default == "true"){
											if($_POST["ID"] == "" || $_POST["pass"] == ""){
												// 未入力
												echo "<p class='c' style='color:#FF5555'>IDとパスワードを入力してください</p>";
												setcookie("default", "false", time() + (20 * 365 * 24 * 60 * 60));
											} else {
												// 一致するIDがあるかの確認と、そのパスワードを取得
												$id = $_POST["ID"];
												$user = "root";
												$password = "";
												$host = "localhost:3306";
												$dbname = "kakerubo";
												$dsn = "mysql:host={$host};dbname={$dbname};charset=utf8";
												$pdo = new PDO($dsn, $user, $password);
												$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
												$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, PDO::ERRMODE_EXCEPTION);
												$sql = "select password from kakerubo_dbs where id = '$id';";
												$stm = $pdo->prepare($sql);
												$stm->execute();
												$result = $stm->fetchAll(PDO::FETCH_ASSOC);
												if(count($result) != 0){
													// 一致IDありの場合
													foreach($result as $row){
														// パスワードの一致を確認
														if(es($row['password']) == $_POST['pass']){
															setcookie("defaultID", $id, time() + (20 * 365 * 24 * 60 * 60));
															setcookie("defaultPass", $_POST["pass"], time() + (20 * 365 * 24 * 60 * 60));
														} else {
															echo "<p class='c' style='color:#FF5555'>パスワードが一致しません</p>";
															setcookie("default", "false", time() + (20 * 365 * 24 * 60 * 60));
														}
													}
												} else {
													// 一致ID無しの場合
													echo "<p class='c' style='color:#FF5555'>IDが見つかりません</p>";
													setcookie("default", "false", time() + (20 * 365 * 24 * 60 * 60));
												}
											}
										}
									?>
									<input type="radio" id="default_true" name="default" value="true" <?php checked("true", [$default]); ?>>
									<label for="true">設定する</label><br>
									<p>家計簿のID：<input type="text" name="ID" size="30" class="ws"></p>
									<p>パスワード：<input type="text" name="pass" size="30" class="ws"></p>
									<input type="radio" id="default_false" name="default" value="false" <?php checked("false", [$default]); ?>>
									<label for="false">設定しない</label>
								</td>
							</tr>
						</table>
					
					<p class="c">
						<input type="submit" value="設定内容を反映する" class="btn">
					</p>
					</form>

				</section>

			</div>
			<!--/.inner-->

		</div>
		<!--/#contents-->

		<footer>

			<div id="footermenu" class="inner">
				<ul>
					<li class="title">タイトル</li>
					<li><a href="#">メニューサンプル</a></li>
					<li><a href="#">メニューサンプル</a></li>
					<li><a href="#">メニューサンプル</a></li>
					<li><a href="#">メニューサンプル</a></li>
					<li><a href="#">メニューサンプル</a></li>
				</ul>
				<ul>
					<li class="title">タイトル</li>
					<li><a href="#">メニューサンプル</a></li>
					<li><a href="#">メニューサンプル</a></li>
					<li><a href="#">メニューサンプル</a></li>
					<li><a href="#">メニューサンプル</a></li>
					<li><a href="#">メニューサンプル</a></li>
				</ul>
				<ul>
					<li class="title">タイトル</li>
					<li><a href="#">メニューサンプル</a></li>
					<li><a href="#">メニューサンプル</a></li>
					<li><a href="#">メニューサンプル</a></li>
					<li><a href="#">メニューサンプル</a></li>
					<li><a href="#">メニューサンプル</a></li>
				</ul>
				<ul>
					<li class="title">タイトル</li>
					<li><a href="#">メニューサンプル</a></li>
					<li><a href="#">メニューサンプル</a></li>
					<li><a href="#">メニューサンプル</a></li>
					<li><a href="#">メニューサンプル</a></li>
					<li><a href="#">メニューサンプル</a></li>
				</ul>
			</div>
			<!--/#footermenu-->

			<div id="copyright">
				<ul class="icon">
					<li><a href="#"><img src="images/icon_facebook.png" alt="Facebook"></a></li>
					<li><a href="#"><img src="images/icon_twitter.png" alt="Twitter"></a></li>
					<li><a href="#"><img src="images/icon_instagram.png" alt="Instagram"></a></li>
					<li><a href="#"><img src="images/icon_youtube.png" alt="TouTube"></a></li>
				</ul>
				<small>Copyright&copy; <a href="index.html">Masato Katogi</a> All Rights Reserved.</small>
				<span class="pr"><a href="https://template-party.com/" target="_blank">《Web
						Design:Template-Party》</a></span>
			</div>
			<!--/#copyright-->

		</footer>

	</div>
	<!--/#container-->

	<p class="nav-fix-pos-pagetop"><a href="#">↑</a></p>

	<!--メニュー開閉ボタン-->
	<div id="menubar_hdr" class="close"></div>

	<!--メニューの開閉処理-->
	<script>
		open_close("menubar_hdr", "menubar-s");
	</script>

	<!--「WORKS」の子メニュー-->
	<script>
		open_close("menubar_hdr2", "menubar-s2");
	</script>

</body>

</html>