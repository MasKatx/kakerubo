<?php
session_start();
?>
<!-- 読み込みと自動ログアウト -->
<?php
require_once("util.php");
if(!isSet($_COOKIE["dbid"])){
	header("Location:search.php");
	$_SESSION["cookie_error"] = "一定時間操作がなかったためログアウトしました。";
}
// db初期設定
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
	<title>家計簿管理サイト|kakerubo-家計簿の分析</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="家計簿を手軽に、詳細に管理するウェブサイトkakerubo 
    自分の使いやすいようにアレンジしたり、設定によって使いすぎを防止したり…… あなたの生活をきっとより便利に、豊かにするシステムです。">
	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="css/slide.css">
	<script src="js/openclose.js"></script>
	<script src="js/fixmenu.js"></script>
	<script src="js/fixmenu_pagetop.js"></script>
	<script src="js/ddmenu_min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/vue@2.5.17/dist/vue.js"></script>
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>

	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>

<body>
<?php
// ラジオボタン用の関数
	function checked(string $value, array $checkedValues){
		$isChecked = in_array($value, $checkedValues);
		if($isChecked){
			echo "checked";
		}
	}
	// 表示データ内容の変更
	if(isSet($_POST["analyse"])){
		$analyse = $_POST["analyse"];
	} else {
		$analyse = "monthly_spending_item";
	}
	if($analyse == "monthly_income_item"){
		$flg = "ii";
	} else if($analyse == "monthly_income_settles"){
		$flg = "is";
	} else if($analyse == "monthly_spending_item"){
		$flg = "si";
	} else{
		$flg = "ss";
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
				<h2>RESULT<span>家計簿データの分析</span></h2>
					<?php
					// googlechartに渡す配列を作る
					$variable = array("合計");
					try{
						$pdo = new PDO($dsn, $user, $password);
						$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
						$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, PDO::ERRMODE_EXCEPTION);
						// ユーザーの選択によって取得するデータを変える
						if($flg == "ii" || $flg == "si"){
							$sql = "select name from items;";
						} else {
							$sql = "select name from settles;";
						}
						$stm = $pdo->prepare($sql);
						$stm->execute();
						$result = $stm->fetchAll(PDO::FETCH_ASSOC);
						// 各費目/決済方法の名前を格納(chartでラベルとして表示)
						if(count($result) != 0){
							foreach($result as $row){
								array_push($variable, es($row["name"]));
							}
						}
					} catch (Except $e){
						echo $e->getMessage();
					}
					//$variable = ["合計", "費目/決済方法...]
					//データ取得処理
					$moneyArray = array_fill(0, count($variable), array_fill(0, 13, array(0, 0)));	//[[["月", "費目/決済方法名"], [month, money]...], [[], []]...]
					for($i=0; $i<count($variable); $i++){
						$moneyArray[$i][0][0] = "月";
						$moneyArray[$i][0][1] = $variable[$i];
					}
					// 年月日を調節しながら一か月ごとに直近一年の取得処理
					for($j=0; $j<count($variable); $j++){
						$thisyear = (int)date("Y");
						$thismonth = (int)date("m");
						$startDatesArray = array("01/01", "02/01", "03/01", "04/01", "05/01", "06/01", "07/01", "08/01", "09/01", "10/01", "11/01", "12/01");
						$datesArray = array("01/31", "02/28", "03/31", "04/30", "05/31", "06/30", "07/31", "08/31", "09/30", "10/31", "11/30", "12/31");
						//一年間分繰り返し
						for($i=0; $i<12; $i++){
							try{
								// sql用にdatetime型の形に文字列を生成
								if($i+$thismonth >= 12){
									$today = ($thisyear)."/".$startDatesArray[($i+$thismonth)%12];
									$endday = ($thisyear)."/".$datesArray[($i+$thismonth)%12];
									$moneyArray[$j][$i+1][0] = "$thisyear"."/".(($i+$thismonth)%12 + 1);
								} else {
									$today = ($thisyear-1)."/".$startDatesArray[($i+$thismonth)%12];
									$endday = ($thisyear-1)."/".$datesArray[($i+$thismonth)%12];
									$moneyArray[$j][$i+1][0] = ($thisyear-1)."/".(($i+$thismonth)%12 + 1);
								}
								$today = $today." 00:00:00";
								$endday = $endday." 00:00:00";
								$thisname = $variable[$j];
								if($flg == "ii"){
									$sql = "select sum(money) as total from account, items where account.item_relation = items.id and dates between '$today' and '$endday' and in_out = 0 and items.name='$thisname';";
								} else if($flg == "is"){
									$sql = "select sum(money) as total from account, settles where account.settle_relation = settles.id and dates between '$today' and '$endday' and in_out = 0 and settles.name='$thisname';";
								} else if($flg == "si"){
									$sql = "select sum(money) as total from account, items where account.item_relation = items.id and dates between '$today' and '$endday' and in_out = 1 and items.name='$thisname';";
								} else {
									$sql = "select sum(money) as total from account, settles where account.settle_relation = settles.id and dates between '$today' and '$endday' and in_out = 1 and settles.name='$thisname';";
								}
								if($j == 0){
									if($flg == "ii" || $flg == "is"){
										$sql = "select sum(money) as total from account where dates between '$today' and '$endday' and in_out = 0;";
									} else {
										$sql = "select sum(money) as total from account where dates between '$today' and '$endday' and in_out = 1;";
									}
								}
								// 実行と取得
								$stm = $pdo->prepare($sql);
								$stm->execute();
								$result = $stm->fetchAll(PDO::FETCH_ASSOC);
								if(count($result[0]) != 0){
									foreach($result as $row){
										if(isSet($row["total"])){
											$moneyArray[$j][$i+1][1] = (int)es($row["total"]);
										} else {
											$moneyArray[$j][$i+1][1] = 0;
										}
									}
								} else {
									$moneyArray[$j][$i+1][1] = 0;
								}
							} catch (Exception $e){
								echo $e->getMessage();
							}
						}
					}
					// 渡す配列用に形を整える
					$output = array_fill(0, 13, array_fill(0, 0, count($variable)+1));
					$output[0][0] = "月";
					for($i=0; $i<count($variable); $i++){
						$output[0][$i+1] = $variable[$i];
					}
					for($i=1; $i<13; $i++){
						$output[$i][0] = $moneyArray[0][$i][0];
						for($j=0; $j<count($variable); $j++){
							$output[$i][$j+1] = $moneyArray[$j][$i][1];
						}
					}
					// js用に変換
					$json_array = json_encode($output);
					?>
					
					<section id="app">
						<form action="analyse.php" method="POST" style="text-align:center; padding-bottom:50px;">
							<label><input type="radio" value="monthly_income_item" name="analyse" <?php checked("monthly_income_item", [$analyse]); ?>>月毎費目別収入推移</label>
							<label><input type="radio" value="monthly_income_settle" name="analyse" <?php checked("monthly_income_settle", [$analyse]); ?>>月毎決済方法別収入推移</label>
							<label><input type="radio" value="monthly_spending_item" name="analyse" <?php checked("monthly_spending_item", [$analyse]); ?>>月毎費目別支出推移</label>
							<label><input type="radio" value="monthly_spending_settle" name="analyse" <?php checked("monthly_spending_settle", [$analyse]); ?>>月毎決済方法別支出</label>
							<input class="btn" type="submit" value="切り替える"><br>
						</form>
						<div>
							<script	script type="text/javascript">
								// ライブラリのロード
								google.load('visualization', '1', { 'packages': ['corechart'] });
								// グラフを描画する為のコールバック関数を指定
								google.setOnLoadCallback(drawChart);
								// グラフの描画
								var Arrays = <?php echo $json_array; ?>;
								function drawChart() {
									// 配列からデータの生成
									var data = google.visualization.arrayToDataTable(Arrays);
									// オプションの設定
									var options = {
										title: '<?php 
										if($flg == "ii"){
											echo "月毎費目別収入推移";
										} else if($flg == "is"){
											echo "月毎決済方法別収入推移";
										} else if($flg == "si"){
											echo "月毎費目別支出推移";
										} else{
											echo "月毎決済方法別支出";
										}
										?>'
									};
									// 指定されたIDの要素に折れ線グラフを作成
									var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
									// グラフの描画
									chart.draw(data, options);
								}
							</script>
						</div>
					</section>
					<div id="chart_div" style="width: auto; height: auto; padding-bottom:50px;"></div>
					<div style="text-align:center;"><a href="mypage.php"><input type="submit" value="マイページ" class="btn"></a></div>
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