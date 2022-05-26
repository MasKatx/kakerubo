<?php
session_start();
?>
<?php
// 読み込みと自動ログアウト、DBの初期設定
require_once("util.php");
if(!isSet($_COOKIE["dbid"])){
	header("Location:search.php");
	$_SESSION["cookie_error"] = "一定時間操作がなかったためログアウトしました。";
}
$dbname = $_COOKIE["dbid"];
$name = $_COOKIE["dbname"];
$pass = $_COOKIE["dbpass"];
$user = "root";
$password = "";
$host = "localhost:3306";
$dsn = "mysql:host={$host};dbname={$dbname};charset=utf8";

// ラジオボタン判定用
function checked(string $value, array $checkedValues){
	$isChecked = in_array($value, $checkedValues);
	if($isChecked){
		echo "checked";
	}
}

// 初回更新時かどうかと、値が入力されているかどうかの確認と設定
if(isSet($_POST["in_out"])){
	$in_out = $_POST["in_out"];
	$in_outArray = array("in", "out");
	$in_outInput = array_search($in_out, $in_outArray);
} else {
	$in_out = "out";
}
if(isSet($_POST["money"])){
	$money = (int)$_POST["money"];
} else {
	$money = 0;
}
if(isSet($_POST["dates"])){
	$dates = $_POST["dates"];
	$datesInput = $dates." 00:00:00";
}
if(isSet($_POST["item_relation"])){
	$item_relation = (int)$_POST["item_relation"];
	if($item_relation == ""){
		$item_relation = "null";
	}
}
if(isSet($_POST["settle_relation"])){
	$settle_relation = (int)$_POST["settle_relation"];
	if($settle_relation == ""){
		$settle_relation = "null";
	}
}
if(isSet($_POST["memo"])){
	$memo = $_POST["memo"];
	if($memo == ""){
		$memo = "null";
	}
} else {
	$memo = "";
}

// 値が正しく入力されていることの確認(金額0は許可しない)
if($money > 0 && $dates != ""){
	// 正しく入力されている場合は値を家計簿(account)にインサートしてマイページへ遷移
	try{
		$pdo = new PDO($dsn, $user, $password);
		$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, PDO::ERRMODE_EXCEPTION);

		$sql = "insert into account(dates, in_out, money, settle_relation, item_relation, memo) values ('$datesInput', $in_outInput, $money, $settle_relation, $item_relation, '$memo')";
		echo $sql;
		$stm = $pdo->prepare($sql);

		$stm->execute();
		header("Location:mypage.php");
	} catch (Exception $e){
		echo $e->getMessage();
	}
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>家計簿管理サイト|kakerubo-データの新規追加</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="家計簿を手軽に、詳細に管理するウェブサイトkakerubo 
    自分の使いやすいようにアレンジしたり、設定によって使いすぎを防止したり…… あなたの生活をきっとより便利に、豊かにするシステムです。">
	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="css/slide.css">
	<script src="js/openclose.js"></script>
	<script src="js/fixmenu.js"></script>
	<script src="js/fixmenu_pagetop.js"></script>
	<script src="js/ddmenu_min.js"></script>
	<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body>

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

					<h2>ADD<span>家計簿データの入力</span></h2>
					<form method="POST" action="<?php echo es($_SERVER['PHP_SELF']); ?>">
						<div style="text-align:center;">
							<caption>※は必須項目です</caption>
						</div>
						<table class="ta1">
							<tr>
								<th>収入/支出※</th>
								<td>
									<input type="radio" name="in_out" value="in" <?php checked("in", [$in_out]); ?>>
									<label for="in">収入</label><br>
									<input type="radio" name="in_out" value="out" <?php checked("out", [$in_out]); ?>>
									<label for="out">支出</label>
								</td>
							</tr>
							<tr>
								<th>金額※</th>
								<td><input type="number" name="money" value="<?php echo $money;?>" size="30" class="ws" required></td>
							</tr>
							<tr>
								<th>日付(入力なしで今日)</th>
								<td><input type="date" name="dates" value="<?php echo $dates; ?>" size="30" class="ws"></td>
							</tr>
							<tr>
								<th>費目</th>
								<td>
									<select name='item_relation' value="<?php echo $item_relation; ?>">
										<?php
											try{
												$pdo = new PDO($dsn, $user, $password);
												$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
												$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, PDO::ERRMODE_EXCEPTION);
										
												$sql = "select * from items;";
												echo $sql;
												$stm = $pdo->prepare($sql);
												$stm->execute();
												$result = $stm->fetchAll(PDO::FETCH_ASSOC);
												foreach($result as $row){
													$thisID = es($row['id']);
													$thisName = es($row['name']);
													echo "<option value='$thisID'>$thisName</option>";
												}
											} catch (Exception $e){
												echo $e->getMessage();
											}
										?>
									</select>
								</td>
							</tr>
							<tr>
								<th>決済方法</th>
								<td>
									<select name='settle_relation' value="<?php echo $settle_relation; ?>">
										<?php
											try{
												$pdo = new PDO($dsn, $user, $password);
												$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
												$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, PDO::ERRMODE_EXCEPTION);
										
												$sql = "select * from settles;";
												echo $sql;
												$stm = $pdo->prepare($sql);
												$stm->execute();
												$result = $stm->fetchAll(PDO::FETCH_ASSOC);
												foreach($result as $row){
													$thisID = es($row['id']);
													$thisName = es($row['name']);
													echo "<option value='$thisID'>$thisName</option>";
												}
											} catch (Exception $e){
												echo $e->getMessage();
											}
										?>
									</select>
								</td>
							</tr>
							<tr>
								<th>メモ</th>
								<td><input type="text" name="memo" value="<?php echo $memo; ?>" size="30" class="ws"></td>
							</tr>
						</table>

						<p class="c">
							<input type="submit" value="追加する" class="btn">
						</p>
					</form>
					<div style="text-align:center;"><a href="mypage.php"><input type="submit" value="マイページ" class="btn"></a></div>
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