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

// それぞれポストで受け取った値を設定
$SetInOut = $_POST["in_out"];
if($SetInOut == "in"){
	$sqlInOut = "and in_out.id = 0";
} else if($SetInOut == "out"){
	$sqlInOut = "and in_out.id = 1";
} else {
	$sqlInOut = "";
}

$SetItem = $_POST["item_relation"];
if($SetItem == "*ALL*"){
	$sqlItem = "";
} else {
	$sqlItem = "and items.name = '$SetItem'";
}

$SetSettle = $_POST["settle_relation"];
if($SetSettle == "*ALL*"){
	$sqlSettle = "";
} else {
	$sqlSettle = "and settles.name = '$SetSettle'";
}

$SetDatesStart = $_POST["datesStart"];
if($SetDatesStart == ""){
	$SetDatesStart = "1900-01-01";
}
$SetDatesStart = $SetDatesStart.' 00:00:00';
$SetDatesEnd = $_POST["datesEnd"];
if($SetDatesEnd == ""){
	$SetDatesEnd = "2200-12-31";
}
$SetDatesEnd = $SetDatesEnd.' 00:00:00';

// ラジオボタン用
function checked(string $value, array $checkedValues){
	$isChecked = in_array($value, $checkedValues);
	if($isChecked){
		echo "checked";
	}
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>家計簿管理サイト|kakerubo-家計簿データの検索結果</title>
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
                    <table class='ta2'>
                    <?php
					// ユーザーの入力に応じた検索結果をテーブルで表示
                    try{
                        $pdo = new PDO($dsn, $user, $password);
                        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, PDO::ERRMODE_EXCEPTION);
						// account(家計簿)からメインのデータを、in_outから収支を、itemsから費目を、settlesから決済方法を取得し、関係データベースとして結合
                        $sql = "select account.id as firstID, dates, in_out.name as firstName, money, settles.name as settle, items.name as item, account.memo as firstMemo from account, in_out, items, settles where account.in_out = in_out.id and account.settle_relation = settles.id and account.item_relation = items.id and dates between '$SetDatesStart' and '$SetDatesEnd'".$sqlInOut.";";
                        $stm = $pdo->prepare($sql);
                        $stm->execute();
                        $result = $stm->fetchAll(PDO::FETCH_ASSOC);
                        if($result != ""){
                            echo "<tr>";
							echo "<th>ID</th>";
                            echo "<th>年月日</th>";
                            echo "<th>収支</th>";
                            echo "<th>金額</th>";
                            echo "<th>決済方法</th>";
                            echo "<th>費目</th>";
                            echo "<th>メモ</th>";
                            echo "</tr>";
                        }
                        foreach($result as $row){
                            echo "<tr>";
                            echo "<td>".es($row['firstID'])."</td>";
                            $thatdates = new DateTime(es($row['dates']));
                            echo "<td>".date_format($thatdates, 'y/m/d')."</td>";
                            echo "<td>".es($row['firstName'])."</td>";
                            echo "<td>".es($row['money'])."</td>";
                            echo "<td>".es($row['settle'])."</td>";
                            echo "<td>".es($row['item'])."</td>";
                            echo "<td>".es($row['firstMemo'])."</td>";
                            echo "</tr>";
                        }
                                                    
                    } catch (Exception $e){
                        echo $e->getMessage();
                    }
                    ?>
                    </table>
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