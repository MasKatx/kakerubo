<?php
session_start();
?>

<!DOCTYPE html>
<!-- setting -> cookie -->
<html lang="ja">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>家計簿管理サイト|kakerubo-マイページ</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="家計簿を手軽に、詳細に管理するウェブサイトkakerubo 
    自分の使いやすいようにアレンジしたり、設定によって使いすぎを防止したり…… あなたの生活をきっとより便利に、豊かにするシステムです。">
	<link rel="stylesheet" href="css/style.css">
	<script src="js/openclose.js"></script>
	<script src="js/fixmenu.js"></script>
	<script src="js/fixmenu_pagetop.js"></script>
	<script src="js/ddmenu_min.js"></script>
	<?php require_once("util.php"); require_once("file.php") ?>
	<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body>

	<?php
    // それぞれのcookieの設定(家計簿操作に使用、1時間操作なしで無効)
    $dbid = $_SESSION["dbid"];
    setcookie("dbid", $dbid, time() + (60 * 60));
    $dbname = $_SESSION["dbname"];
    setcookie("dbname", $dbname, time() + (60 * 60));
    $dbpass = $_SESSION["dbpass"];
    setcookie("dbpass", $dbpass, time() + (60 * 60));
    $dbtarget = $_SESSION["dbtarget"];
    if($dbtarget == ""){
        // 存在しない場合は0を設定
        $dbtarget = 0;
    }
    setcookie("dbtarget", $dbtarget, time() + (60 * 60));
    // カレンダー表示用
    if(isSet($_COOKIE["calendar"])){
        $monsun = $_COOKIE["calendar"];
    } else {
        $monsun = "sun";
    }
    
    // 日時格納
    $nowYear = date('Y');
    $nowMonth = date('m');
    $nowDate = date('d');

    // 日時指定があったかどうか(あった場合切り替え)
    if(isSet($_POST["year-month"]) && $_POST["year-month"] != ""){
        $y = intval(explode("-", $_POST["year-month"])[0]);
        $m = intval(explode("-", $_POST["year-month"])[1]);
    } else {
        $m = $nowMonth;
        $y = $nowYear;
    }

    // 翌月と先月の設定
    if($m == 12){
        $nextMonth = 1;
        $nextYear = $y+1;
    } else {
        $nextMonth = $m+1;
        $nextYear = $y;
    }

    if($m == 1){
        $prevMonth = 12;
        $prevYear = $y-1;
    } else {
        $prevMonth = $m-1;
        $prevYear = $y;
    }
	?>

    <?php
    
    // 曜日取得用の関数
    function getW($year, $month, $date){
        $timestamp = mktime(0, 0, 0, $month, $date, $year);
        return date('w', $timestamp);
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

				<section style="text-align:center;">

                <h3><?php $a = new files("files/title.txt"); echo $a->read(); ?></h3>

					<h2>MY PAGE<span>家計簿トップ</span></h2>

					<h3>家計簿の閲覧や編集等ができます</h3>
                    <caption><?php echo $y/1; ?>年 <?php echo $m/1; ?>月</caption>
                    
                    <?php
                    // 閏年かどうか
                    if(date('L')){
                        $leap = 29;
                    } else {
                        $leap = 28;
                    }
                    // 月末の設定
                    $max = array(31, $leap, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
                    // カレンダーの初期化
                    $d = array_fill(0, 37, "-");
                    // 曜日取得
                    $w = getW($y, $m, 1);
                    // 1日から月末までfor
                    for($i=0; $i<$max[$m-1]; $i++){
                        // 日曜始まりか月曜始まりか
                        if($monsun == "sun"){
                            $d[$i+$w] = $i + 1;
                        } else {
                            $d[$i+$w-1] = $i + 1;
                        }
                    }
                    ?>

                    <table class="ta2">
                        <tr>
                            <?php
                            // 日曜始まり
                            if($monsun == "sun"){
                                echo "<th>日</th>";
                            }
                            ?>
                            <th>月</th>
                            <th>火</th>
                            <th>水</th>
                            <th>木</th>
                            <th>金</th>
                            <th>土</th>
                            <?php
                            // 月曜始まり
                            if($monsun == "mon"){
                                echo "<th>日</th>";
                            }
                            ?>
                        </tr>
                        <tr>
                            <!-- カレンダー表示 -->
                            <td><?php echo $d[0]; ?></td>
                            <td><?php echo $d[1]; ?></td>
                            <td><?php echo $d[2]; ?></td>
                            <td><?php echo $d[3]; ?></td>
                            <td><?php echo $d[4]; ?></td>
                            <td><?php echo $d[5]; ?></td>
                            <td><?php echo $d[6]; ?></td>
                        </tr>
                        <tr>
                            <td><?php echo $d[7]; ?></td>
                            <td><?php echo $d[8]; ?></td>
                            <td><?php echo $d[9]; ?></td>
                            <td><?php echo $d[10]; ?></td>
                            <td><?php echo $d[11]; ?></td>
                            <td><?php echo $d[12]; ?></td>
                            <td><?php echo $d[13]; ?></td>
                        </tr>
                        <tr>
                            <td><?php echo $d[14]; ?></td>
                            <td><?php echo $d[15]; ?></td>
                            <td><?php echo $d[16]; ?></td>
                            <td><?php echo $d[17]; ?></td>
                            <td><?php echo $d[18]; ?></td>
                            <td><?php echo $d[19]; ?></td>
                            <td><?php echo $d[20]; ?></td>
                        </tr>
                        <tr>
                            <td><?php echo $d[21]; ?></td>
                            <td><?php echo $d[22]; ?></td>
                            <td><?php echo $d[23]; ?></td>
                            <td><?php echo $d[24]; ?></td>
                            <td><?php echo $d[25]; ?></td>
                            <td><?php echo $d[26]; ?></td>
                            <td><?php echo $d[27]; ?></td>
                        </tr>
                        <tr>
                            <td><?php echo $d[28]; ?></td>
                            <td><?php echo $d[29]; ?></td>
                            <td><?php echo $d[30]; ?></td>
                            <td><?php echo $d[31]; ?></td>
                            <td><?php echo $d[32]; ?></td>
                            <td><?php echo $d[33]; ?></td>
                            <td><?php echo $d[34]; ?></td>
                        </tr>
                        <tr>
                            <td><?php echo $d[35]; ?></td>
                            <td><?php echo $d[36]; ?></td>
                            <td><?php echo "-"; ?></td>
                            <td><?php echo "-"; ?></td>
                            <td><?php echo "-"; ?></td>
                            <td><?php echo "-"; ?></td>
                            <td><?php echo "-"; ?></td>
                        </tr>
                    </table>
                    <div style="display: inline-block;">
                        <form method="POST" action="<?php echo es($_SERVER['PHP_SELF']); ?>">
                            <input type="month" value="<?php echo $y.'-'.$m; ?>" name=year-month>
                            <input type="submit" value="決定" class="btn" class="btn">
                        </form>
                    </div>
                    <br><br>
                    <a href="insertData.php"><input type='submit' value='データ入力' class='btn'></a>
                    <a href="searchData.php"><input type='submit' value='データ検索' class='btn'></a>
                    <a href="editData.php"><input type='submit' value='データ編集' class='btn'></a>
                    <a href="setDB.php"><input type='submit' value='家計簿設定' class='btn'></a>
                    <a href="analyse.php"><input type='submit' value='家計簿分析' class='btn'></a>
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