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
$target = $_COOKIE["dbtarget"];
$user = "root";
$password = "";
$host = "localhost:3306";
$dbname2 = "kakerubo";
$dsn = "mysql:host={$host};dbname={$dbname2};charset=utf8";
?>

<!DOCTYPE html>
<html lang="ja">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>家計簿管理サイト|kakerubo-家計簿の編集</title>
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

					<h2>SETTING<span>家計簿の設定</span></h2>
					<div style="text-align:center;">
						<a href="setItem.php"><input type="submit" class="btn" value="費目の設定はこちら"></a>
						<a href="setSettle.php"><input type="submit" class="btn" value="決済方法の設定はこちら"></a>
					</div>
					<br>
					<form method="POST" action="<?php echo es($_SERVER['PHP_SELF']); ?>" style="text-align:center;">
						<?php
						// 家計簿毎の詳細設定
						if(isSet($_POST["nowpass"]) && isSet($_POST["newpass"]) && isSet($_POST["re-newpass"])){
							// 入力後であれば処理実行
							try{
								$pdo = new PDO($dsn, $user, $password);
								$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
								$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, PDO::ERRMODE_EXCEPTION);
								$sql = "select password from kakerubo_dbs where id = '$dbname';";
								$stm = $pdo->prepare($sql);
								$stm->execute();
								$result = $stm->fetchAll(PDO::FETCH_ASSOC);
								foreach($result as $row){
									// パスワードが正しいかどうか -> 正しい場合は更新処理を実行
									if(es($row['password']) == $_POST['nowpass']){
										// 確認用のパスワード入力の一致を確認
										if($_POST["newpass"] == $_POST["re-newpass"]){
											$NAME = $_POST["name"];
											$PASS = $_POST["newpass"];
											$sql = "update kakerubo_dbs set name = '$NAME', password = '$PASS' where id = '$dbname'";
											$stm = $pdo->prepare($sql);
											$stm->execute();
											$sql = "update current_db set name = '$NAME', password = '$PASS' where id = '$dbname'";
											$stm = $pdo->prepare($sql);
											$stm->execute();

											$dsn = "mysql:host={$host};dbname={$dbname};charset=utf8";
											$pdo = new PDO($dsn, $user, $password);
											$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
											$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, PDO::ERRMODE_EXCEPTION);
											$sql = "update define set name = '$NAME', password = '$PASS' where id = '$dbname'";
											$stm = $pdo->prepare($sql);
											$stm->execute();
										} else {
											echo "<p class='c' style='color:#FF5555'>新しいパスワードの入力と再入力が一致しません</p>";
										}
									} else {
										echo "<p class='c' style='color:#FF5555'>現在のパスワードが違います</p>";
									}
								}
							} catch (Exception $e){
								echo $e->getMessage();
							}
						}
						
						
							echo "<table class='ta1'><tr style='display:none;'><th>家計簿名</th>";
							echo "<td><input type='text' name='name' value='$name' required></td></tr>";
							echo "<tr><th>現在のパスワード</th>";
							echo "<td><input type='password' name='nowpass' class='ws' oncopy='return false' onpaste='return false' oncontextmenu='return false' minlength='8' maxlength='20' required></td></tr>";
							echo "<tr><th>新しいパスワード</th>";
							echo "<td><input type='password' name='newpass' class='ws' oncopy='return false' onpaste='return false' oncontextmenu='return false' minlength='8' maxlength='20' required></td></tr>";
							echo "<tr><th>新しいパスワード(再入力)</th>";
							echo "<td><input type='password' name='re-newpass' class='ws' oncopy='return false' onpaste='return false' oncontextmenu='return false' minlength='8' maxlength='20' required></td></tr>";
							echo "</table>";
						?>
						<p class="c">
							<input type="submit" value="内容を更新する" class="btn">
						</p>
					</form>

					<form method="POST" action="<?php echo es($_SERVER['PHP_SELF']); ?>" style="text-align:center;">
						<?php
						if(isSet($_POST["name"])){
							try{
								// 名前の更新(そのままの場合は同じ名前で上書きされる)
								$pdo = new PDO($dsn, $user, $password);
								$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
								$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, PDO::ERRMODE_EXCEPTION);
								$NAME = $_POST["name"];
								$sql = "update kakerubo_dbs set name = '$NAME' where id = '$dbname'";
								$stm = $pdo->prepare($sql);
								$stm->execute();
								$sql = "update current_db set name = '$NAME' where id = '$dbname'";
								$stm = $pdo->prepare($sql);
								$stm->execute();

								$dsn = "mysql:host={$host};dbname={$dbname};charset=utf8";
								$pdo = new PDO($dsn, $user, $password);
								$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
								$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, PDO::ERRMODE_EXCEPTION);
								$sql = "update define set name = '$NAME' where id = '$dbname'";
								$stm = $pdo->prepare($sql);
								$stm->execute();
								setcookie("dbname", $NAME, time() + (60 * 60));
							} catch (Exception $e){
								echo $e->getMessage();
							}
						}
							echo "<table class='ta1'><tr><th>家計簿名</th>";
							echo "<td><input type='text' name='name' value='$name' required></td></tr>";
							echo "</table>";
						?>
						<p class="c">
							<input type="submit" value="内容を更新する" class="btn">
						</p>
					</form>

					<form method="POST" action="<?php echo es($_SERVER['PHP_SELF']); ?>" style="text-align:center;">
						<?php
						if(isSet($_POST["target"])){
							try{
								// 目標金額の設定
								$TARGET = $_POST["target"];
								$dsn = "mysql:host={$host};dbname={$dbname};charset=utf8";
								$pdo = new PDO($dsn, $user, $password);
								$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
								$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, PDO::ERRMODE_EXCEPTION);
								$sql = "update define set target_value = '$TARGET' where id = '$dbname'";
								$stm = $pdo->prepare($sql);
								$stm->execute();
							} catch (Exception $e){
								echo $e->getMessage();
							}
						}
							echo "<table class='ta1'><tr><th>月毎目標金額</th>";
							echo "<td><input type='number' name='target' value='$target' required></td></tr>";
							echo "</table>";
						?>
						<p class="c">
							<input type="submit" value="内容を更新する" class="btn">
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