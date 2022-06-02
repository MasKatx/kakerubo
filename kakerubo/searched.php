<?php
session_start();
?>
<?php
// 読み込みとDBの初期設定
require_once("util.php");
$user = "root";
$password = "";
$dbname = "kakerubo";
$host = "localhost:3306";
$dsn = "mysql:host={$host};dbname={$dbname};charset=utf8";
if(isSet($_COOKIE["default"]) || !$_COOKIE["default"] == "true"){
	$defaultPass = "";
} else {
	$defaultPass = $_COOKIE["defaultPass"];
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>家計簿管理サイト|kakerubo-検索結果</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="家計簿を手軽に、詳細に管理するウェブサイトkakerubo 
    自分の使いやすいようにアレンジしたり、設定によって使いすぎを防止したり…… あなたの生活をきっとより便利に、豊かにするシステムです。">
	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="css/slide.css">
	<script src="js/openclose.js"></script>
	<script src="js/fixmenu.js"></script>
	<script src="js/fixmenu_pagetop.js"></script>
	<script src="js/ddmenu_min.js"></script>
    <script src= "https://cdn.jsdelivr.net/npm/vue@2.5.17/dist/vue.js"></script>
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

					<h2>ACCOUNT BOOKS<span>家計簿の検索結果</span></h2>

					<p style="text-align: center;">
                        <form method="POST" action="<?php echo es($_SERVER['PHP_SELF']); ?>">
                            <?php
							// 戻る命令で検索画面へ
                            $post = $_POST['name'];
							if($post == "BACK"){
								header("Location:search.php");
							}
                            try{
								// ユーザーが入力したIDの家計簿を検索
                                $pdo = new PDO($dsn, $user, $password);
                                $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                                $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, PDO::ERRMODE_EXCEPTION);

                                $sql = "select * from kakerubo_dbs where id = ?";

                                $stm = $pdo->prepare($sql);

                                $stm->bindValue(1, $post);

                                $stm->execute();

                                $result = $stm->fetchAll(PDO::FETCH_ASSOC);

                                $id = "";
                                foreach($result as $row){
                                    $id = es($row['id']);
                                    $name = es($row['name']);
                                    $pass = es($row['password']);
                                }
								// それぞれ設定されていて入力が正しい場合(合致する場合)推移
								// 間違っている場合はそれに応じてエラーメッセージを表示
                                if(isSet($_POST["dbpass"])){
                                    if($_POST["dbpass"] == $pass){
                                        $_SESSION["dbid"] = $_POST["name"];
										$_SESSION["dbname"] = $name;
                                        $_SESSION["dbpass"] = $_POST["dbpass"];
										
										$dsn = "mysql:host={$host};dbname={$id};charset=utf8";
										$pdo = new PDO($dsn, $user, $password);
										$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
										$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, PDO::ERRMODE_EXCEPTION);
										$sql = "select target_value from define";
										$stm = $pdo->prepare($sql);
										$stm->execute();
										$result = $stm->fetchAll(PDO::FETCH_ASSOC);
										foreach($result as $row){
											$_SESSION["dbtarget"] = $row['target_value'];
										}
                                        header("Location:mypage.php");
                                    } else {
                                        echo "<p class='c' style='color:#FF5555'>パスワードが違います</p>";
                                    }
                                }
                                if($id == ""){
                                    echo "<table class='ta1'><tr><th>該当する家計簿が見つかりませんでした</th></tr></table>";
									echo "<input name='name' type='hidden' value='BACK'>";
                                    echo "<p class='c'><input type='submit' value='戻る' class='btn'></p>";
                                } else {
                                    echo "<input name='name' type='hidden' value='$id'>";
                                    echo "<table class='ta1'><tr><th>ID</th><th>家計簿名</th></tr><tr><th>$id</th><th>$name</th></tr></table>";
                                    echo "<p class='c'>パスワード：<input type='password' class='input' v-model.text='myText' name='dbpass'></p>";
                                    echo "<p class='c' v-bind:style='{visibility: unshow}' style='color:#FF5555'>パスワード(4~20字)を入力してください</p>";
                                    echo "<p class='c' v-bind:style='{visibility: show}'><input type='submit' value='この家計簿を使用する' class='btn'></p>";
                                }
                            } catch (Exception $e){
                                echo $e->getMessage();
                            }
                            ?>
                        </form>
                    </p>

				</section>

			</div>
			<!--/.inner-->

		</div>
		<!--/#contents-->

        <script>
			new Vue({
				el:"#contents",
				data:{
					myText: '<?php echo $defaultPass; ?>'
				},
				computed:{
					show: function(){
						if(this.myText.length >= 4 && this.myText.length <= 20){
							return "visible";
						} else {
							return "hidden";
						}
					},
					unshow: function(){
						if(this.myText.length <= 3 || this.myText.length >= 21){
							return "visible";
						} else {
							return "hidden";
						}
					}
				}
			})
		</script>

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
