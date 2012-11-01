#!/usr/local/bin/php

<?PHP
/*
  ����
  $this->mailS         : [ ���� ] �᡼��ǡ����ݤ���
  $this->headerS       : [ ���� ] �إå��ǡ���
  $this->bodyS         : [ ���� ] ��ʸ�ǡ���
  $this->error_mail    : [ ʸ�� ] ���顼�᡼�륢�ɥ쥹
  $this->numExplode    : [ ���� ] �إå�����ʸ�ζ��ڤ��
  $this->numLine       : [ ���� ] �᡼��ǡ������Կ�
  $this->flagAppend    : [ ���� ] ź�դ�̵ͭ True=ͭ�� Fakse=̵��
  $this->numAppend     : [ ���� ] ź�եե������
  $this->appendS       : [ ���� ] ź�եե�����ǡ���
  $this->boundary      : [ ʸ�� ] ź�դ���ʸ�ζ��ڤ�ʸ��
  $this->lineBoundaryS : [ ���� ] ź�դ���ʸ�ζ��ڤ��
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

  var $time_start = "";
  var $time_stop  = "";

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


  // * �᡼��ǡ�������
  function setMail(){
    $this->mailS = file("php://stdin");
    //$this->mailS = file("/usr/local/vpopmail/domains/rabbit-mail.jp/hataji/Maildir/new/test");
    return ;
  }  


  // * ��������
  function setNewLine($str=False){
    $str = ereg_replace("\r\n","\n",$str);
    $str = ereg_replace("\r",  "\n",$str);
    return $str;
  }

  // * �����˴�
  function unsetNewLine($str=False){
    $str = ereg_replace("\r\n","",$str);
    $str = ereg_replace("\r",  "",$str);
    $str = ereg_replace("\n",  "",$str);
    return $str;
  }

  // * ʸ������������
  function setEncode($str=False){
    $str = mb_convert_encoding( $str, "EUC-JP","JIS" );
    return $str;
  }

  // * ź��̵ͭ�����å�
  Function checkAppend(){
    if( ereg('multipart/mixed;', $this->headerS['content-type']) || ereg('boundary=', $this->headerS['content-type']) ){
      $this->flagAppend = True;
    }
    return ;
  }

  // * ź�եե����������
  Function setNumAppend(){
    $this->numAppend = (count($this->lineBoundaryS)-2);
    return ;
  }



  // * �᡼��Կ�����
  function setLineNum(){
    $this->numLine = count($this->mailS);
    return ;
  }


  // * �Х���������
  Function setBoundary(){
    if( $this->flagAppend ){
      preg_match("/\"(.*)\"/", $this->headerS['content-type'], $boundaryS);  
      $this->boundary =  str_replace("-","",$boundaryS[1]);
    }
    return ;
  }


  // * �᡼�����
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

  // * �إå�����
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



  // * ��ʸ����
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

  // * ��ʸ��ź��ʬΥ
  function setExplodeAppend(){

    // ��ʸ����
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

      // ź�ռ���
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

  // * ���顼�᡼�륢�ɥ쥹���� ���ɤ�;��ͭ��
  function setErrorMail(){
      foreach($this->bodyS as $str ){
        if((ereg("To:",$str) && !ereg("Reply-To:",$str)) || ereg("Final-Recipient:",$str)){
          $error_mail = explode(" ",$str,2);
          $this->error_mail = $error_mail[1];
         }

        if( ereg("<",$this->error_mail) || ereg(">",$this->error_mail) ){
          preg_match("/\<(.*)\>/", $this->error_mail, $errorS);  
          $this->error_mail = $errorS[1];
        }elseif( ereg("RFC",$this->error_mail) || ereg("rfc",$this->error_mail)){//Final-Recipient:�κ��


          if(ereg(" ",$this->error_mail)){
            $error_mail = explode(" ",$this->error_mail,2);//������ڤ�
            echo $this->error_mail = $error_mail[1];
          }elseif(ereg(";",$this->error_mail)){
            $error_mail = explode(";",$this->error_mail,2);//�����ڤ�
            $this->error_mail = $error_mail[1];
          }

        }
      }

    return;
  }

}


/*
* ��������ģ´ط�
*/

//DB��Ͽ
function registDbMain($id){
global $InportMail;
  //require_once ('/www/vhosts/test.itm-asp.com/html/lib/Postgres.php');
  require_once ('/www/vhosts/rabbit-mail.jp/html/program/lib/db/Postgres.php');
  $Postgres = new Postgres();

  $query = "INSERT INTO td_error_log ";
  $query .= " ( user_id , mail , error_count ) ";
  $query .= " VALUES( ";
  $query .= " {$id},";
  $query .= " '{$InportMail->error_mail}',";
  $query .= " '1'";
  $query .= " ) ";

  $Postgres->executeUpdate($query);
  $Postgres->close();
return;
}

//DB UPDATE
function updateDbMain($getData){
global $InportMail;
  //require_once ('/www/vhosts/test.itm-asp.com/html/lib/Postgres.php');
  require_once ('/www/vhosts/rabbit-mail.jp/html/program/lib/db/Postgres.php');
  $Postgres = new Postgres();

  $error_count = $getData['error_count'];
  $error_count++;

  $query = "UPDATE td_error_log SET ";
  $query .= " error_count = {$error_count} ,";
  $query .= " date_update = NOW()";
  $query .= " WHERE error_log_id = '{$getData['error_log_id']}' ";

  $Postgres->executeUpdate($query);
  $Postgres->close();
return;
}

//DB���
function delDbMain($email,$id){
global $InportMail;
  //require_once ('/www/vhosts/test.itm-asp.com/html/lib/Postgres.php');
  require_once ('/www/vhosts/rabbit-mail.jp/html/program/lib/db/Postgres.php');
  $Postgres = new Postgres();

  $query = "DELETE FROM td_mmail_member ";
  $query .= " WHERE ";
  $query .= " email =  '{$InportMail->error_mail}' AND user_id =  '{$id}'";
  echo $query ;
  $Postgres->executeUpdate($query);
  $Postgres->close();
return;
}

//��Ͽ���줿�ǡ����ʥᥢ�ɡˤ����뤫��
function selectData($id){
global $InportMail;
  //require_once ('/www/vhosts/test.itm-asp.com/html/lib/Postgres.php');
  require_once ('/www/vhosts/rabbit-mail.jp/html/program/lib/db/Postgres.php');
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



  //main
 $InportMail = new InportMail;
 $id_str = explode("@",$InportMail->headerS['to_mail'],2);
 $id = explode("-",$id_str[0],2);
 //mb_send_mail($to="hataji@itm-asp.com" ,$subject="1" ,$message="{$id[0]}\n {$InportMail->headerS['to_mail']} \n {$id_str[0]}");

 if($id[0]=="error"){
   if(ereg("^[0-9]+$", $id[1])){
       $getData =  selectData($id[1]);
       if(!$getData){
        registDbMain($id[1]);
        //echo "mail = ".$InportMail->error_mail."\n";
      }else{
        updateDbMain($getData);
      }
   }
 }


/*
  foreach($InportMail->bodyS as $str ){
    $message .= $str."\n";
    
  }
*/


 exit;
?>