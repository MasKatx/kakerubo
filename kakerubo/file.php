<!-- ファイル処理用クラス -->
<!-- DBを使用したため使い道がほとんどなかったので、無理やり使いました -->

<?php
require_once("util.php");
?>
<?php
    class files{
        private $fileName;
        private $fileObj;
        // コンストラクタ
        public function __CONSTRUCT($fileName){
            $this->fileName = $fileName;
        }

        // 読み込み用
        public function read(){
            // オブジェクトを作って読み込み
            try{
                $this->fileObj = new SplFileObject($this->fileName, "rb");
            } catch(xception $e){
                echo "<p class='c'>エラーが発生しました。</p>";
            }
            $readdata = $this->fileObj->fread($this->fileObj->getSize());
            if($readdata){
                $readdata = es($readdata);
                return $readdata;
            } else {
                echo "<p class='c'>ファイルの読み込みに失敗しました</p>";
            }
        }

    }
?>