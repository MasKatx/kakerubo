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
?>

<!DOCTYPE html>
<html lang="ja">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>家計簿管理サイト|kakerubo-データの編集</title>
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

					<h2>RESULT<span>家計簿データの検索結果</span></h2>
						<?php
						// 結果表示用sqlの準備
						try{
							$pdo = new PDO($dsn, $user, $password);
							$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
							$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, PDO::ERRMODE_EXCEPTION);
							$ID = (int)$_POST["ID"];

							$sql = "select dates, in_out, money, settle_relation, item_relation, memo from account where id = $ID;";
							$stm = $pdo->prepare($sql);
							$stm->execute();
							$result = $stm->fetchAll(PDO::FETCH_ASSOC);
							if(count($result) != 0){
								// 値が見つかった場合はテーブルの設定
								echo "<form method='POST' action='editSetData.php' style='text-align:center;'><table class='ta2'><tr>";
								echo "<th>年月日</th>";
								echo "<th>収支</th>";
								echo "<th>金額</th>";
								echo "<th>決済方法</th>";
								echo "<th>費目</th>";
								echo "<th>メモ</th>";
								echo "</tr>";
								
								foreach($result as $row){
									// 値の表示
									echo "<tr>";
									echo "<td style='display:none;'><input type='number' name='id' value=$ID></td>";
									$thatdates = new DateTime(es($row['dates']));
									$strthatdates = date_format($thatdates, 'Y-m-d');
									$data1 = es($row['in_out']);
									$data2 = es($row['money']);
									$data3 = es($row['settle_relation']);
									$data4 = es($row['item_relation']);
									$data5 = es($row['memo']);
									echo "<td><input type='date' name='dates' value=$strthatdates class='ws' required></td>";
									echo "<td><select name='settle_relation' value='$data1'>";
									// 収支
									try{
										$pdo = new PDO($dsn, $user, $password);
										$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
										$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, PDO::ERRMODE_EXCEPTION);
								
										$sql = "select * from in_out;";
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
									"</select></td>";
									echo "<td><input type='number' name='money' value=$data2 class='ws' required></td>";
									echo "<td><select name='settle_relation' value='$data3'>";
									// 決済方法
									try{
										$pdo = new PDO($dsn, $user, $password);
										$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
										$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, PDO::ERRMODE_EXCEPTION);
								
										$sql = "select * from settles;";
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
									"</select></td>";
									
									echo "<td><select name='item_relation' value='$data4'>";
									// 費目
									try{
										$pdo = new PDO($dsn, $user, $password);
										$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
										$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, PDO::ERRMODE_EXCEPTION);
								
										$sql = "select * from items;";
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
									"</select></td>";
									
									echo "<td><input type='text' name='memo' value=$data5 class='ws'></td>";
									echo "</tr></table>";
									echo "<p class='c'><input type='submit' value='更新する' class='btn'></p></form>";
								}
							} else {
								// IDが間違っていた場合の処理
								echo "<p class='c'>データが見つかりませんでした</p>";
								echo "<div style='text-align:center;'><a href='editData.php'><input type='submit' value='検索画面に戻る' class='btn'></a></div>";
							}
							
														
						} catch (Exception $e){
							echo $e->getMessage();
						}
						?>
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