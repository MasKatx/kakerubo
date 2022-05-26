<!-- データの設定処理 -->

<?php
// 読み込みと自動ログアウト、DB初期設定
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
<?php
try{
	// 編集内容の更新(update)
	$pdo = new PDO($dsn, $user, $password);
	$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, PDO::ERRMODE_EXCEPTION);
	$id = (int)$_POST["id"];
	$in_out = $_POST['in_out'];
	$money = $_POST['money'];
	$settle_relation = $_POST['settle_relation'];
	$item_relation = $_POST['item_relation'];
	$memo = $_POST['memo'];
	$datesInput = $_POST["dates"]." 00:00:00";

	$sql = "update account set dates='$datesInput', in_out=$in_out, money=$money, settle_relation=$settle_relation, item_relation=$item_relation, memo='$memo' where id = $id;";
	$stm = $pdo->prepare($sql);
	$stm->execute();
	header("Location:mypage.php");
} catch (Exception $e){
	echo $e->getMessage();
}
?>