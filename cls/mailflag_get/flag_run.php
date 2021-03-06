#!/usr/local/bin/php

<?PHP
/*
  説明
  $this->mailS         : [ 配列 ] メールデータ丸ごと
  $this->headerS       : [ 配列 ] ヘッダデータ
  $this->bodyS         : [ 配列 ] 本文データ
  $this->error_mail    : [ 文字 ] エラーメールアドレス
  $this->numExplode    : [ 数字 ] ヘッダと本文の区切り行
  $this->numLine       : [ 数字 ] メールデータ総行数
  $this->flagAppend    : [ 真偽 ] 添付の有無 True=有り Fakse=無し
  $this->numAppend     : [ 数字 ] 添付ファイル数
  $this->appendS       : [ 配列 ] 添付ファイルデータ
  $this->boundary      : [ 文字 ] 添付と本文の区切り文字
  $this->lineBoundaryS : [ 配列 ] 添付と本文の区切り行
*/


class InportMail{

  var $mailS       = "";
  var $headerS     = "";
  var $bodyS       = "";
  var $error_mail  = "";
  var $numLine     = "";
  var $numExplode  = "";
  var $flagAppend  = "";
  var $numAppend   = "";
  var $appendS     = "";
  var $boundary    = "";
  var $lineBoundaryS = "";


  function InportMail(){
    $this->setMail();
    $this->setLineNum();
    $this->setFormat();
    $this->setHeader();
    $this->checkAppend();
    $this->setBoundary();
    $this->setBody();
    $this->setErrorMail();
    $this->setNumAppend();
    $this->setExplodeAppend();
    //$this->registDbMain();
  }


  // * メールデータ取得
  function setMail(){
    $this->mailS = file("php://stdin");
    //$this->mailS = file("/usr/local/vpopmail/domains/rabbit-mail.jp/test/Maildir/new/test");
    //$this->mailS = file("/usr/local/vpopmail/domains/rabbit-mail.jp/hataji/Maildir/new/test");
    return ;
  }  


  // * 改行統一
  function setNewLine($str=False){
    $str = ereg_replace("\r\n","\n",$str);
    $str = ereg_replace("\r",  "\n",$str);
    return $str;
  }

  // * 改行破棄
  function unsetNewLine($str=False){
    $str = ereg_replace("\r\n","",$str);
    $str = ereg_replace("\r",  "",$str);
    $str = ereg_replace("\n",  "",$str);
    return $str;
  }

  // * 文字コード統一
  function setEncode($str=False){
    $str = mb_convert_encoding( $str, "EUC-JP","JIS" );
    return $str;
  }

  // * 添付有無チェック
  Function checkAppend(){
    if( ereg('multipart/mixed;', $this->headerS['content-type']) || ereg('boundary=', $this->headerS['content-type']) ){
      $this->flagAppend = True;
    }
    return ;
  }

  // * 添付ファイル数取得
  Function setNumAppend(){
    $this->numAppend = (count($this->lineBoundaryS)-2);
    return ;
  }



  // * メール行数取得
  function setLineNum(){
    $this->numLine = count($this->mailS);
    return ;
  }


  // * バウンダリ取得
  Function setBoundary(){
    if( $this->flagAppend ){
      preg_match("/\"(.*)\"/", $this->headerS['content-type'], $boundaryS);  
      $this->boundary =  str_replace("-","",$boundaryS[1]);
    }
    return ;
  }


  // * メール形成
  function setFormat(){

    $i=0;
    while ($i<=$this->numLine) {
        $str = $this->mailS[$i];
        $str = $this->setEncode($str);
        $str = $this->setNewLine($str);
        $this->mailS[$i]=$str;
      $i++;
    }
    $this->numExplode = array_search("\n", $this->mailS);

    return ;
  }

  // * ヘッダ取得
  function setHeader(){

    $i=0;
    $key="";
    $val="";

    while ($i<=$this->numExplode){
      if(strpos($this->mailS[$i],":")){
        list($key, $val) = explode(": ", $this->mailS[$i],2);
        $key = strtolower(trim($key));
        $val = trim(mb_decode_mimeheader($val));
        $headerS[$key] = $val;
      }else if($key!=""){
        $headerS[$key] .= mb_decode_mimeheader($this->mailS[$i]);
      }
      $i++;
    }
    $this->headerS = $headerS;

    $this->setHeaderEx();

    return ;
  }

  function setHeaderEx(){
    if( ereg("<",$this->headerS['from']) || ereg(">",$this->headerS['from']) ){
      preg_match("/\<(.*)\>/", $this->headerS['from'], $fromS);
      $this->headerS['from_mail'] = $fromS[1];
    }else{
      $this->headerS['from_mail'] = $this->headerS['from'];
    }

    if( ereg("<",$this->headerS['to']) || ereg(">",$this->headerS['to']) ){
      preg_match("/\<(.*)\>/", $this->headerS['to'], $toS);  
      $this->headerS['to_mail'] = $toS[1];
    }else{
      $this->headerS['to_mail'] = $this->headerS['to'];
    }

    return ;
  }



  // * 本文取得
  function setBody(){
    $numBoundary=0;
    $lineBoundaryS="";
    $line=0;
    $i=$this->numExplode;
    $key="";
    $val="";
    while ($i<=$this->numLine){
      if( isset($this->boundary) && $this->boundary!="" && ereg($this->boundary, $this->mailS[$i]) ){
        $numBoundary++;
        $lineBoundaryS[$numBoundary] = $line;
      }
      $bodyS[$line] = $this->unsetNewLine($this->mailS[$i]);
      $line++;
      $i++;
    }
    $this->bodyS         = $bodyS;
    $this->lineBoundaryS = $lineBoundaryS;
    return ;
  }

  // * 本文・添付分離
  function setExplodeAppend(){

    // 本文取得
    if(isset($this->lineBoundaryS) && is_array($this->lineBoundaryS) ){
      $line=0;
      $i=$this->lineBoundaryS[1];
      while ($i<=$this->lineBoundaryS[2]-1){
        $bodyS[$line] = $this->bodyS[$i];
        $line++;
        $i++;
      }
      $numExplode = array_search("", $bodyS);

      $i=0;
      while ($i<=$numExplode){
        unset($bodyS[$i]);
        $i++;
      }

      // 添付取得
      $numTemp=1;
      while ($numTemp<=$this->numAppend){

        $line=0;
        $i=$this->lineBoundaryS[1+$numTemp]+1;
        while ($i<=$this->lineBoundaryS[2+$numTemp]-1){
          $appendS[$numTemp]['data'][$line] = $this->bodyS[$i];
          $line++;
          $i++;
        }

        $numExplode = array_search("", $appendS[$numTemp]['data']);

        $fileName ="";
        $i=0;
        while ($i<=$numExplode){

          if( ereg("filename=",$appendS[$numTemp]['data'][$i]) ){
            $fileType = strchr( $appendS[$numTemp]['data'][$i],"." );
            $fileType = str_replace("\"","",$fileType);
            $fileType = str_replace(".","",$fileType);
          }

          unset($appendS[$numTemp]['data'][$i]);
          $i++;
        }
        $appendS[$numTemp]['type'] = $fileType;

        $numTemp++;
      }

      $this->bodyS   = $bodyS;
      $this->appendS = $appendS;

    }

    return ;
  }

  // * エラーメールアドレス取得 改良の余地有。
  function setErrorMail(){
      foreach($this->bodyS as $str ){
        if((ereg("To:",$str) && !ereg("Reply-To:",$str)) || ereg("Final-Recipient:",$str)){
          $error_mail = explode(" ",$str,2);
          $this->error_mail = $error_mail[1];
         }

        if( ereg("<",$this->error_mail) || ereg(">",$this->error_mail) ){
          preg_match("/\<(.*)\>/", $this->error_mail, $errorS);  
          $this->error_mail = $errorS[1];
        }elseif( ereg("RFC",$this->error_mail) || ereg("rfc",$this->error_mail)){//Final-Recipient:の削除


          if(ereg(" ",$this->error_mail)){
            $error_mail = explode(" ",$this->error_mail,2);//空白で切る
            echo $this->error_mail = $error_mail[1];
          }elseif(ereg(";",$this->error_mail)){
            $error_mail = explode(";",$this->error_mail,2);//；で切る
            $this->error_mail = $error_mail[1];
          }

        }
      }

    return;
  }

}


/*
* ここからＤＢ関係
*/


//DB登録
function registDbMain($id){
global $InportMail;
  //require_once ('/www/vhosts/test.rabbit-mail.jp/html/lib/Postgres.php');
  //require_once ('/www/html/program/lib/db/Postgres.php');
  require_once ('/var/www/vhosts/www.rabbit-mail.jp/html/program/lib/db/Postgres.php');

  $Postgres = new Postgres();
/*
  $query = "INSERT INTO td_error_log ";
  $query .= " ( user_id , mail , error_count ) ";
  $query .= " VALUES( ";
  $query .= " {$id},";
  $query .= " '{$InportMail->error_mail}',";
  $query .= " '1'";
  $query .= " ) ";

  $Postgres->executeUpdate($query);
  $Postgres->close();
*/
return;
}


//DB UPDATE
function updateDbMain($field,$field2,$flag,$log_id){
global $InportMail;
  //require_once ('/www/vhosts/test.rabbit-mail.jp/html/lib/Postgres.php');
  //require_once ('/www/html/program/lib/db/Postgres.php');
  require_once ('/var/www/vhosts/www.rabbit-mail.jp/html/program/lib/db/Postgres.php');

  $Postgres = new Postgres();

  //flagの確認
  $query_s  = "SELECT log_id , flag_type ,flag_pc , flag_mobile ";
  $query_s .= "FROM td_log WHERE log_id = {$log_id} ";

  //flagのUPDATE
  $query = "UPDATE td_log SET ";
  $query .= " {$field} = {$flag} ,";
  $query .= " {$field2} = NOW()";
  $query .= " WHERE log_id = '{$log_id}' ";


  //flagの確認
	$getFlagData = '' ;
		$dataS = $Postgres->executeQuery($query_s);
		while ($tmp  = pg_fetch_array($dataS)){
			$getFlagData = $tmp;
	}

  if($getFlagData[$field] != '99'){
     $Postgres->executeUpdate($query);
  }

  $Postgres->close();
return;
}

//mail送信
function sendMailMain($log_id){
global $InportMail;

  //require_once ('/www/vhosts/test.rabbit-mail.jp/html/lib/Postgres.php');
  //require_once ('/www/html/program/lib/db/Postgres.php');
  require_once ('/var/www/vhosts/www.rabbit-mail.jp/html/program/lib/db/Postgres.php');

  $Postgres = new Postgres();

  require_once ('/var/www/vhosts/www.rabbit-mail.jp/html/program/lib/mail/Mail.php');
  $Mail = new  Mail();
  
  $query  = "SELECT mail_from , mail_confirm ,log_id , subject , date_insert, flag_type ,flag_pc , flag_mobile ";
  $query .= "FROM td_log WHERE log_id = {$log_id} ";

  //$aData = $Postgres->executeQuery($query);
  //$Postgres->close();
  
	$getData = '' ;
		$dataS = $Postgres->executeQuery($query);
		while ($tmp  = pg_fetch_array($dataS)){
			$getData = $tmp;
		}

  //色々設定
  $str = explode(".",$getData['date_insert']);
  $getData['date_insert'] = $str[0];
  
  $subject = "[rabbit-mail]メール配信完了のお知らせ";
	$from="info@rabbit-mail.jp";
	$to=$getData['mail_confirm'];

	$message = "配信したメールは下記のとおりです。\n";
	$message .= "\n";
	$message .= "件名(subject) 　：　{$getData['subject']} \n";
	$message .= "配信元アドレス　：　{$getData['mail_from']}\n";
	$message .= "配信予約日時　　：　{$getData['date_insert']}\n";
	$message .= "\n";
	$message .= "\n";
	$message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
	$message .= "■本メールに関するご連絡先\n";
	$message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
	$message .= "ご不明な点・お問合せ等がございましたら、\n";
	$message .= "contact@rabbit-mail.jpまでご連絡ください。\n";
	$message .= "\n";
	$message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━";
  $message = mb_convert_encoding( $message,"JIS" ) ;


  /*
  * 完了メール送信の条件
  */
  if(($getData['flag_type']=='1' && $getData['flag_pc'] == '99') 
     || ($getData['flag_type']=='2' && $getData['flag_mobile'] == '99')
     || ($getData['flag_type']=='3' && $getData['flag_pc'] == '99' && $getData['flag_mobile'] == '99')
     || ($getData['flag_type']=='3' && $getData['flag_pc'] == '3' && $getData['flag_mobile'] == '99') //PCキャンセルノ場合
     || ($getData['flag_type']=='3' && $getData['flag_pc'] == '99' && $getData['flag_mobile'] == '3') //Mobileキャンセルノ場合
     ){
		$Mail->normalMb_send_mail($from, $to, $subject, $message );
  }
return;
}


/*
//DB削除
function delDbMain($email,$id){
global $InportMail;
  require_once ('/www/vhosts/test.rabbit-mail.jp/html/lib/Postgres.php');
  $Postgres = new Postgres();

  $query = "DELETE FROM td_mmail_member ";
  $query .= " WHERE ";
  $query .= " email =  '{$InportMail->error_mail}' AND user_id =  '{$id}'";
  echo $query ;
  $Postgres->executeUpdate($query);
  $Postgres->close();
return;
}
*/


//登録されたデータ（メアド）があるか？
/*
function selectData($id){
global $InportMail;
  require_once ('/www/vhosts/test.rabbit-mail.jp/html/lib/Postgres.php');
  $Postgres = new Postgres();

  $query  = "SELECT * FROM  td_error_log ";
  $query .= " WHERE mail = '{$InportMail->error_mail}' AND user_id = {$id}";
  $result  = $Postgres->executeQuery($query);
    while ( $data = pg_fetch_array($result) ) {
      $getData = $data;
    }
  $Postgres->close();
return($getData);
}
*/


  //main
 $InportMail = new InportMail;
 $id_str = explode("@",$InportMail->headerS['to_mail'],2);
 $id = explode("-",$id_str[0],3);


 /*
 * flag-pc_start-0001@rabbit-mail.jp    PCメールスタート
 * flag-pc_finish-0001@rabbit-mail.jp   PCメール終了
 * flag-mo_start-0001@rabbit-mail.jp    携帯メールスタート
 * flag-mo_finish-0001@rabbit-mail.jp   携帯メール終了
 */

	print_r($id);

 if($id[0]=="flag"){
		switch($id[1]){
		  case "pc_start":
		     updateDbMain($field="flag_pc" , $field2="date_pc" , $flag="2",$log_id=$id[2]);
		  break;

		  case "pc_finish":
		     updateDbMain($field="flag_pc" , $field2="date_pc" , $flag="99",$log_id=$id[2]);
		     sendMailMain($log_id);
		  break;

		  case "mo_start":
		     updateDbMain($field="flag_mobile" , $field2="date_mobile" , $flag="2",$log_id=$id[2]);
		  break;

		  case "mo_finish":
		     updateDbMain($field="flag_mobile" , $field2="date_mobile" , $flag="99",$log_id=$id[2]);
		     sendMailMain($log_id);
		  break;
		}
 }

 exit;
?>
