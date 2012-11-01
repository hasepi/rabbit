<?PHP
/*

  �᡼��������Ϣ

*/

  class Send{

    var $debug = '';

    Function Send($debug=False){

      if( $debug ) $this->debug = True;

      $this->format();

      return ;
    }


    /*
     * �����
     */
    Function format(){

      if( $this->debug ) echo" - "._ROOT_PG_."Send.php | format() <br>\n";

      return ;
    }


    /*
     * sign_up ���󥭥塼�᡼������
     */
    Function sign_up($mailUser=False,$mailMaster=False){
      Global $libMail,$pField,$pVariable,$libBrowse;

      if( $this->debug ) echo" - "._ROOT_PG_."Send.php | sign_up() <br>\n";

      $master = mb_encode_mimeheader( _NAME_FROM_ )."<"._MAIL_FROM_.">";
      $user   = mb_encode_mimeheader( "{$pVariable->inputS['name']} ��" )."<{$pVariable->inputS['mail']}>";

      $message  = "";
      $message .= "����������\n";
      $message .= "��".date('Y/m/d H:i')."\n";
      $message .= "��{$pField->nameS['id']}\n";
      $message .= "��{$pVariable->inputS['id']}\n";
      $message .= "��{$pField->nameS['password']}\n";
      $message .= "��{$pVariable->inputS['password']}\n";
      $message .= "��{$pField->nameS['name_company']}\n";
      $message .= "��{$pVariable->inputS['name_company']}\n";
      $message .= "��{$pField->nameS['kana_company']}\n";
      $message .= "��{$pVariable->inputS['kana_company']}\n";
      $message .= "��{$pField->nameS['name']}\n";
      $message .= "��{$pVariable->inputS['name']}\n";
      $message .= "��{$pField->nameS['kana']}\n";
      $message .= "��{$pVariable->inputS['kana']}\n";
      $message .= "��{$pField->nameS['mail']}\n";
      $message .= "��{$pVariable->inputS['mail']}\n";
      $message .= "������\n";
      $message .= "����{$pVariable->inputS['zip']}\n";
      $message .= "��{$pVariable->inputS['address1']} {$pVariable->inputS['address2']}\n";
      $message .= "��{$pField->nameS['tel']}\n";
      $message .= "��{$pVariable->inputS['tel']}\n";
      $message .= "��{$pField->nameS['mobile']}\n";
      $message .= "��{$pVariable->inputS['mobile']}\n";

      $userMessage   = "";
      if( file_exists($mailUser) ){
        $load =  file($mailUser);
        foreach($load as $key=>$value){
          $value = str_replace("#InputName#",$pVariable->inputS['name'],$value);
          $value = str_replace("#InputCompany#",$pVariable->inputS['name_company'],$value);
          $value = str_replace("#InputDataS#",$message,$value);
          $userMessage .= $value;
        }
      }

      $masterMessage   = "";
      if( file_exists($mailMaster) ){
        $load =  file($mailMaster);
        foreach($load as $key=>$value){
          $value = str_replace("#InputName#",$pVariable->inputS['name'],$value);
          $value = str_replace("#InputCompany#",$pVariable->inputS['name_company'],$value);
          $value = str_replace("#InputDataS#",$message,$value);
          $masterMessage .= $value;
        }
      }
      $roots_text = "";
      if( isset($_POST['roots_text']) ) {
        $roots_text = $_POST['roots_text'];
      }
      $masterMessage .= "���褫���褿����".$_POST['roots']."��".$roots_text."\n";
      $masterMessage .= "\n";
      $masterMessage .= "��⡼�ȥ��ɥ쥹����{$libBrowse->remote_address}\n";
      $masterMessage .= "��⡼�ȥۥ��ȡ�����{$libBrowse->remote_host}\n";
      $masterMessage .= "ü�����ࡡ����������{$libBrowse->agent}\n";
      $masterMessage .= "ü���С�����󡡡���{$libBrowse->type}\n";
      $masterMessage .= "�֥饦��������������{$libBrowse->generation}\n";


      $userSubject  = "[itm-asp]�����ӥ��������꤬�Ȥ��������ޤ���";
      $libMail->normalMb_send_mail( $master, $user,  $userSubject, $userMessage, False, False, _MAIL_ERROR_);

      $masterSubject  = "�����ӥ���������ޤ���";
      if(isset($_POST['roots'])){
        $masterSubject .= "�� {$_POST['roots']}��";
      }
      $libMail->normalMb_send_mail( $user,  $master, $masterSubject, $masterMessage, False, False, _MAIL_ERROR_);
      $libMail->normalMb_send_mail( $user,  "masaki@itm.ne.jp", $masterSubject, $masterMessage, False, False, _MAIL_ERROR_);



      return ;
    }


    /*
     * �ץ���ѹ� ���󥭥塼�᡼������
     */
    Function plan($plan_id=False,$mailUser=False,$masterUser=False){
      Global $libMail,$expPostgres;

      if( $this->debug ) echo" - "._ROOT_PG_."Send.php | sign_up({$plan_id}) <br>\n";

      $query  = "SELECT * FROM tm_plan WHERE flag_open=1 AND plan_id={$plan_id}";
      if( $this->debug ) echo"<font size='1'> -  query =  {$query}</font> <br>\n";
      $pDataS = $expPostgres->getOne( $query,PGSQL_ASSOC );

      $tenpa = (($pDataS['price_month6']+$pDataS['price_first'])*0.1);
      $date = date('YmdHis');
      $a8so = $_SESSION['user']['user_id'];
      $a8Url =  "https://px.a8.net/cgi-bin/a8fly/sales?pid=s00000001947003&so={$a8so}&si={$tenpa}.1.{$tenpa}.{$pDataS['plan_id']}&ts={$date}";
      $_SESSION['a8Url'] = "<IMG SRC=\"{$a8Url}\" width=\"1\" height=\"1\">";

      $master = mb_encode_mimeheader( _NAME_FROM_ )."<"._MAIL_FROM_.">";
      $user   = mb_encode_mimeheader( "{$_SESSION['user']['name_family']} {$_SESSION['user']['name_first']} ��" )."<{$_SESSION['user']['mail']}>";


      $auto_money="������ܰʹߤθ��¼�ư����Ȥ������ʤ�\n";
      if( $_POST['inputS']['auto_money']=='t' ){
        $auto_money="������ܰʹߤθ��¼�ư����Ȥ�������\n�ʼ�ư�����Ѥ��ѻ����������פ��ޤ�����\n";
      }


      $price_month  = $pDataS['price_month']*1.05;
      $price_month6 = $price_month*6;

      if( date('Ymd')>20050731 ){
        $price_first = $pDataS['price_first']*1.05;
      }else{
        $price_first = 0;
      }
      $price_first = floor($price_first);

      $price_total = $price_first+$price_month6;



      if( date('d')<=10 ){
        $year  = date('Y');
        $month = date('m');
      }else{
        if( date('m')==12 ){
          $year  = date('Y')+1;
          $month = 1;
        }else{
          $year  = date('Y');
          $month = sprintf("%02d",date('m')+1);
        }
      }
      $org = "{$year}-{$month}-01";
      $time = strtotime( $org );
      $dst_time = mktime(0,0,0,date("m",$time)+5,date("d",$time),date("Y",$time));
      $dst = date( "Yǯm��", $dst_time );


      $message  = "";
      $message .= "����ʸ����    : ".date('Yǯm��d��')."\n";
      $message .= "�����ͣɣ�    : ".sprintf("%05d",$_SESSION['user']['user_id'])."\n";
      $message .= "�����ӥ�����  : ".date('Yǯm��d��')."��{$dst}��\n";
      $message .= "�ץ��̾      : {$pDataS['plan']}\n";
      $message .= "������λ�������                            {$price_month6} ��\n";
      $message .= "�������                                     {$price_first} ��\n";
      $message .= "--------------------------------------------------------------\n";
      $message .= "��׳�                                       {$price_total} ��\n";


      $userMessage   = "";
      if( file_exists($mailUser) ){
        $load =  file($mailUser);
        foreach($load as $key=>$value){
          $value = str_replace("#InputName1#",$pVariable->inputS['name_company'].$_SESSION['user']['name_family'].$_SESSION['user']['name_first'],$value);
          $value = str_replace("#InputName#",$_SESSION['user']['name_family'].$_SESSION['user']['name_first'],$value);
          $value = str_replace("#user_id#",sprintf("%05d",$_SESSION['user']['user_id']),$value);
          $value = str_replace("#plan#",$pDataS['plan'],$value);
          $value = str_replace("#auto_money#",$auto_money,$value);
          $value = str_replace("#InputDataS#",$message,$value);
          $value = str_replace("#day#",date("Y/m/d", strtotime("15 day")),$value);
          if($pVariable->inputS['name_company']!=""){
            $value = str_replace("#InputCompany#",$pVariable->inputS['name_company'],$value);
          }else{
            $value = str_replace("#InputCompany#","����������",$value);
          }
          $userMessage .= $value;
        }
      }

      $user_subject  = "[itm-asp]�ץ���ѹ������򾵤�ޤ���";
      $libMail->normalMb_send_mail( $master, $user,  $user_subject, $userMessage, False, False, _MAIL_ERROR_);

      $master_subject  = "�ץ���ѹ���˾������ޤ���";
      $master_message  = "\n";
      $master_message  .= "\n";
      $master_message  .= "�����Υ�å������������������ޤ���\n";
      $master_message  .= "---------------------------------------------------\n";
      $master_message  .= $userMessage;
      $master_message  .= "\n";
      $libMail->normalMb_send_mail( $user, $master, $master_subject, $master_message, False, False, _MAIL_ERROR_);
      $libMail->normalMb_send_mail( $user, "masaki@itm.ne.jp", $master_subject, $master_message, False, False, _MAIL_ERROR_);

      return ;
    }


  }

?>