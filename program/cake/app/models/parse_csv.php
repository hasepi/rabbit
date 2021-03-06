<?php
    class ParseCsv extends AppModel {
        //  PHP4　ユーザ用の互換性のため
        var $name = "ParseCsv";

        var $useTable = false;

        /**
         * /app/config/database.php内で定義したどのパラメータを使用するかを指定
         * デフォルトは 'default'
         *
         * @var string
         * @access public
         */
        var $useDbConfig = "default";

        /**
         * データベーステーブルフィールドの詳細であるメタデータ
         * ※DB不使用時、formhelperを使うとエラーが起きる
         * 回避として$_schema最低限設定しておく必要があるらしいのでダミーを設定
         */
        var $_schema = array();

        /**
         * キャッシュを使用・不使用
         * @var boolean
         * @access public
         */
        var $cacheQueries = false;


        /**
         * 使用するビヘイビア
         * 不使用時はfalse
         * $actAs = array('behavior'=>'array('param')','behavior2');
         *
         * @var mixed
         * @access public
         * @link http://book.cakephp.org/ja/view/90/Using-Behaviors
         */
        var $actsAs = array('Form', 'Convert', 'ExValidate');

        /**
         * 入力チェック

         * VALID_NOT_EMPTY…入力データは必須入力である
         * VALID_NUMBER…入力データは数値でなければならない
         * VALID_EMAIL…入力データはＥメールアドレスでなければならない
         * VALID_YEAR…入力データは年数でなければならない
         */

        var $validate = array(
            'csv1' => array(
                'rule1' => array(
                    'rule' => array('isUpLoadEmpty'),
                    'message' => '配信リストcsvを選択してください'
                )
            ),
            'csv2' => array(
                'rule1' => array(
                    'rule' => array('isUpLoadEmpty'),
                    'allowEmpty' => true,
                    'message' => '削除用csvを選択してください'
                )
            )
        );

        /**
         * アップロードされたcsvファイルか調べる
         *
         * @return boolean アップロードされたcsvファイルであれば true それ以外 false を返す
         */
        function isUpLoadEmpty($data) {
            $upload_info = array_shift($data);

           
            
            
            
            if (($upload_info['size'] > 0) ||
               (is_uploaded_file($upload_info['tmp_name']))) {
               if ((pathinfo($upload_info['name'], PATHINFO_EXTENSION) === 'csv') ||
                    (pathinfo($upload_info['name'], PATHINFO_EXTENSION) === 'txt')) {
                   return true;
               }
            } else {
                return false;
            }               
        }

        /**
         * 入力画面で受け取った値をコンバート
         *
         * @return array $tempParseCsvs
         */
        function setPostConvert() {
            $tempParseCsvs = $this->data['TempParseCsv'];
            return $tempParseCsvs;
        }


        /**
         * エラーメッセージ取得
         *
         * @param array $dataError エラーメッセージ配列
         */
         function getErrorMsgs($dataError) {
             //foreachでループを回し表示用のエラーを取得
              foreach($dataError as $key=>$value){
                $errorMsgs[] = '<span style="color:#FF0000;">'.$value.'</span><br>';
              }

             return $errorMsgs;
         }

        /**
        * 配列を文字列に変換する
        * @param object $downloadCsv
        * @return $csvFile
        */
        function convertQueryArray($downloadCsv, $userSessionId) {
            $convertQueryArray = "";
            foreach ($downloadCsv as $key => $value) {
                if ($value['user_id'] === $userSessionId) {
                    foreach ($value as $key2 => $value2) {
                        if ($key2!= "id" && $key2!="user_id") {
                            $convertQueryArray .= $value2.",";
                        }
                    }
                }
                // 末尾の「,」を削除する
                $convertQueryArray = rtrim($convertQueryArray, ",");

                $convertQueryArray .= "\n";
            }
            return $convertQueryArray;
        }

    }
?>