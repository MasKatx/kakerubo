<!-- 新規ユーザー用DB登録処理 -->
<?php
    require_once("util.php");
    session_start();
?>

<?php
// ID生成用
    $toId = explode(" ", wordwrap("abcdefghijklmnopqrstuvwxyz",1," ",true));
    $name = $_SESSION["name"];
    $pass = $_SESSION["password"];

    // DB基本設定
    $user = "root";
    $password = "";
    $dbname = "kakerubo";
    $host = "localhost:3306";
    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8";

    try{
        $pdo = new PDO($dsn, $user, $password);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, PDO::ERRMODE_EXCEPTION);

        // 最終更新DBの情報を読み込み
        $sql = "select id from current_db";

        $stm = $pdo->prepare($sql);

        $stm->execute();

        $result = $stm->fetchAll(PDO::FETCH_ASSOC);

        $nowId = "";
        foreach($result as $row){
            $nowId = es($row['id']);
        }
        $nowIds = explode(" ", wordwrap($nowId,1," ",true));

        // IDを生成
        $indexs = [array_search($nowIds[0], $toId), array_search($nowIds[1], $toId), array_search($nowIds[2], $toId), array_search($nowIds[3], $toId), 
        array_search($nowIds[4], $toId), array_search($nowIds[5], $toId), array_search($nowIds[6], $toId), array_search($nowIds[7], $toId)];
        for($i=7; $i>=0; $i--){
            $indexs[$i] = ($indexs[$i]+1)%count($toId);
            if($indexs[$i] != 0){
                break;
            }
        }
        $resultId = ["", "", "", "", "", "", "", ""];
        for($i=0; $i<8; $i++){
            $resultId[$i] = $toId[$indexs[$i]];
        }
        $idName = $resultId[0].$resultId[1].$resultId[2].$resultId[3].$resultId[4].$resultId[5].$resultId[6].$resultId[7];

        // DB作成
        $sql = "create database $idName";
        $stm = $pdo->prepare($sql);
        $stm->execute();

        // 最終更新DBをこのDBに変更
        $sql = "update current_db set id = '$idName', name = '$name', password = '$pass'; insert into kakerubo_dbs values ('$idName', '$name', '$pass')";
        $stm = $pdo->prepare($sql);
        $stm->execute();

        $sql = "use $idName";
        $stm = $pdo->prepare($sql);
        $stm->execute();

        // テーブルなどの初期化
        $sql = "create table define(
            id char(8) primary key default 'aaaaaaaa',
            name varchar(20) not null default 'NONE',
            password varchar(20) not null default '00000000',
            target_value int default null
            );
            
            insert into define values('$idName', '$name', '$pass', null);
            
            create table account(
            id int primary key auto_increment,
            dates datetime not null default current_timestamp,
            in_out int(1) not null default 0,
            money int not null default 0,
            settle_relation int default null,
            item_relation int default null,
            memo varchar(100) default null
            );
            
            create table in_out(
            id int(1) primary key default 0,
            name char(2) not null default '収入'
            );

            insert into in_out values(0, '収入');
            insert into in_out values(1, '支出');
            
            create table items(
            id int primary key auto_increment,
            name varchar(20) not null unique default 'NONE',
            memo varchar(100) default null
            );
            
            create table settles(
            id int primary key auto_increment,
            name varchar(20) not null unique default 'NONE',
            memo varchar(100) default null
            );";
        $stm = $pdo->prepare($sql);
        $stm->execute();
        $result = true;
        $sql = "select id from define;";
        $stm = $pdo->prepare($sql);
        $stm->execute();
        $result = $stm->fetchAll(PDO::FETCH_ASSOC);
        foreach($result as $row){
            $id = es($row['id']);
        }
    } catch (Exception $e){
        echo $e->getMessage();
        $result = false;
        
    }
?>

<?php
    if($result){
        
        $_SESSION["dbid"] = $id;
        $_SESSION["dbname"] = $name;
        $_SESSION["dbpass"] = $pass;
        $_SESSION["dbtarget"] = 0;
        header("Location:mypage.php");
    } else {
        echo "エラーが発生しました";
    }
?>