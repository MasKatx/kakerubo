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
$dsn = "mysql:host={$host};dbname={$dbname};charset=utf8";
?>

<!DOCTYPE html>
<html lang="ja">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>家計簿管理サイト|kakerubo-決済方法の設定</title>
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

					<h2>SETTING<span>決済方法の設定</span></h2>
					<br>
					<form method="POST" action="<?php echo es($_SERVER['PHP_SELF']); ?>" style="text-align:center;">
						<?php
						// 決済方法設定
							try{
								$pdo = new PDO($dsn, $user, $password);
								$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
								$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, PDO::ERRMODE_EXCEPTION);
								// 入力後の送信の場合は処理を実行
                                if(isSet($_POST["name"])){
                                    $settleName = $_POST["name"];
                                    $settleMemo = $_POST["memo"];
                                    $sql = "select name from settles where name = '$settleName';";
                                    $stm = $pdo->prepare($sql);
                                    $stm->execute();
                                    $result = $stm->fetchAll(PDO::FETCH_ASSOC);
									// 同一決済方法がないかどうかの確認(unique)
                                    if($result != ""){
                                        if($settleMemo = ""){
											// メモがない場合はnullで入力
                                            $sql = "insert into settles(name) values ('$settleName');";
                                            $stm = $pdo->prepare($sql);
                                            $stm->execute();
                                        } else {
											// メモがある場合はどちらも入力
                                            $sql = "insert into settles(name, memo) values ('$settleName', ?);";
                                            $stm = $pdo->prepare($sql);
                                            $stm->bindValue(1, $settleMemo);
                                            $stm->execute();
                                        }
                                        header("Location:setSettle.php");
                                    } else {
										// 同じ決済方法の場合のエラー
                                        echo "<p class='c' style='color:#FF5555;'>決済方法が重複しています</p>";
                                    }
                                }
                            } catch (Exception $e){
                                echo $e->getMessage();
                            }
								
                        try{
							// 全決済方法の表示
                            $pdo = new PDO($dsn, $user, $password);
                            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, PDO::ERRMODE_EXCEPTION);
                            $sql = "select name, memo from settles;";
                            $stm = $pdo->prepare($sql);
                            $stm->execute();
                            $result = $stm->fetchAll(PDO::FETCH_ASSOC);
                        }catch (Exception $e){
                            $e->getMessage();
                        }
                        if(count($result) == 0){
							// 初回設定時の場合
                            echo "<p class='c'>決済方法がまだ設定されていません</p>";
                        } else {
                            echo"<table class='ta1'><tr><th>決済方法名</th><th>メモ</th></tr>";
                            foreach($result as $row){
                                echo "<tr><td>".$row["name"]."</td><td>".$row["memo"]."</td><tr>";
                            }
                            echo "</table>";
                        }
                        
							echo "<table class='ta1'><tr><th>決済方法名</th>";
							echo "<td><input type='text' name='name' required></td></tr>";
							echo "<tr><th>メモ</th>";
							echo "<td><input type='text' name='memo'></td></tr>";
							echo "</table>";
						?>
						<p class="c">
							<input type="submit" value="決済方法を追加する" class="btn">
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