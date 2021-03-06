<?PHP
/*

  出力関連


*/

  class OutputMaster {

    var $debug = "";
    var $base = "";
    var $html = "";
    var $page_max    = 20;
    var $code_output = "EUC-JP";

    // * コンストラクタ
    Function OutputMaster($base=False,$code_output=False,$page_max=False,$debug=False){

      if( $debug )       $this->debug       = True;
      if( $base )        $this->base        = $base;
      if( $page_max )    $this->page_max    = $page_max;
      if( $code_output ) $this->code_output = $code_output;

      return ;
    }


    /*
     * html出力
     */
    function html($mode=False,$place=False){
      global $libUtil, $libCode, $expUtil, $pField, $pVariable;

      if( $this->debug ) echo" - "._ROOT_PG_."OutputMaster.php | html({$mode},{$place}) <br>\n";

      $html = "";
      switch( $mode ){

        // 新規登録
        // 修正登録
        case 'new':
        case 'renew':
          switch( $place ){
            case   'write': $html = _HTML_PG_WRITE_;   break;
            case 'confirm': $html = _HTML_PG_CONFIRM_; break;
            case 'rewrite': $html = _HTML_PG_WRITE_;   break;
            case   'error': $html = _HTML_PG_WRITE_;   break;
            case  'finish': $html = _HTML_PG_FINISH_;  break;
          }
          break;

        // マスターリスト
        case 'list':
          switch( $place ){
            case  'list': $html = _HTML_PG_LIST_; break;
            case  'list_mail': $html = _HTML_PG_LIST_MAIL_; break;
            case  'list_mail_temp': $html = _HTML_PG_LIST_MAIL_TEMP_; break;
          }
          break;

      }

      if( !$html ) die('NOT HTML');

      // 表示・出力用文字変換
      $pField->nameS     = $libCode->encodeBase( $pField->nameS,    $this->code_output, 'EUC-JP');
      $pVariable->viewS  = $libCode->encodeBase( $pVariable->viewS, $this->code_output, 'EUC-JP');
      $pVariable->writeS = $libCode->encodeBase( $pVariable->writeS,$this->code_output, 'EUC-JP');


      require_once _HTML_PG_BASE_ ;

      return;
    }


    /*
     * リスト出力
     */
    function lists($listS,$mode=False,$mailList=False){
      global $libCode,$libDaytime,$mstAry,$expPostgres,$expUtil,$pField;

      if( $this->debug ) echo"<font size='1'> - "._ROOT_PG_."OutputMaster.php | lists({$listS})</font> <br>\n";

      $column  = "td_user.user_id,";
      $column .= "td_user.name_company,";
      $column .= "td_user.kana_company,";
      $column .= "td_user.name_family,";
      $column .= "td_user.name_first,";
      $column .= "td_user.kana_family,";
      $column .= "td_user.kana_first,";
      $column .= "td_user.mail,";
      $column .= "td_user.flag_pictmail,";
      $column .= "td_user.flag_stepmail,";
      $column .= "td_user.date_insert,";

      $column .= "td_pictmail.pictmail_id,";
      $column .= "td_pictmail.plan_pictmail_id,";
      $column .= "td_pictmail.flag_dm,";
      $column .= "td_pictmail.flag_permission,";
      $column .= "td_pictmail.month_max,";
      $column .= "td_pictmail.month_now,";
      $column .= "td_pictmail.send_max,";
      $column .= "td_pictmail.send_now,";

      $column .= "tm_plan.plan";


      $baseQuery   = "SELECT {$column} FROM td_user ";

      $joinQuery   = " JOIN td_pictmail ON td_pictmail.user_id=td_user.user_id ";
      $joinQuery  .= " JOIN tm_plan ON tm_plan.plan_id=td_pictmail.plan_pictmail_id ";

      $whereQuery  = " WHERE td_pictmail.flag_del!=1 ";
      if( $mode=='temp' ){
        $whereQuery .= " AND td_pictmail.plan_pictmail_id=1 ";
      }else{
        $whereQuery .= " AND td_pictmail.plan_pictmail_id!=1 ";
      }
      if($mailList){
        $whereQuery .= " AND td_pictmail.flag_dm=1 ";
      }

      $orderbyQuery .= " ORDER BY td_user.user_id DESC";

      $query = $baseQuery.$joinQuery.$whereQuery.$orderbyQuery;

      if( $this->debug ) echo"<font size='1'> -  query =  {$query}</font> <br>\n";
      $uDataS = $expPostgres->getPlurality( $query,PGSQL_ASSOC );
      $uDataS = $libCode->encodeBase( $uDataS, $this->code_output, 'EUC-JP');

      if( $uDataS['count']==0 ){
        
      }else{

        if($mailList){
           echo "
              
              <table width='600' height='25' border='0' cellpadding='3' cellspacing='0' bgcolor='#DDDDDD'>
                <tr bgcolor='#FFFFFF' class='gray12'> 
                  <td align='left'>
                    <b>{$uDataS['count']}</b> ユーザー
                  </td>
                </tr>
              </table>

              <table width='600' height='25' border='0' cellpadding='3' cellspacing='1' bgcolor='#DDDDDD'>
                <tr bgcolor='#EEEEEE' class='gray12' align='center'> 
                  <td>
                    {$pField->nameS['name']},{$pField->nameS['mail']},{$pField->nameS['name_company']}
                  </td>
                </tr>
          ";
        }else{


           echo "
              
              <table width='750' height='25' border='0' cellpadding='3' cellspacing='0' bgcolor='#DDDDDD'>
                <tr bgcolor='#FFFFFF' class='gray12'> 
                  <td align='left'>
                    現在 <b>{$uDataS['count']}</b> ユーザーの登録
                  </td>
                </tr>
              </table>

              <table width='750' height='25' border='0' cellpadding='3' cellspacing='1' bgcolor='#DDDDDD'>
                <tr bgcolor='#EEEEEE' class='gray12' align='center'> 
                  <td>
                    ID
                  </td>
                  <td>
                    登録日
                  </td>
                  <td>
                    {$pField->nameS['name']}<br>
                    {$pField->nameS['kana']}
                  </td>
                  <td width=''>
                    プラン
                  </td>
                  <td>
                    所属<br>
                    フリガナ
                  </td>
                  <td>
                    送信数<br>
                    現在/最大
                  </td>
                  <td>
                    プラン変更
                  </td>
                  <td>
                    修正・変更
                  </td>
                  <td>
                    削除
                  </td>
                  <td>
                    使用許可
                  </td>
                </tr>
            ";
        }



        $i=1;
        while ( $i<=$uDataS['count'] ){



          if($mailList){
             echo "
                  <tr bgcolor='#FFFFFF' class='gray12' align='center'> 
                    <td>
                  {$uDataS[$i]['name_family']} {$uDataS[$i]['name_first']},{$uDataS[$i]['mail']},{$uDataS[$i]['name_company']}
                    </td>
                  </tr>
            ";
          }else{


            $permissionS = $mstAry->permissionAry();
            $flag_permission = "";
            foreach( $permissionS as $num=>$value ){
              if( $uDataS[$i]['flag_permission']==$value['id'] ){
                $flag_permission = $value['id'].$value['name'];
                break;
              }
            }

            $dmS = $mstAry->dmAry();
            $flag_dm = "";
            foreach( $dmS as $num=>$value ){
              if( $uDataS[$i]['flag_dm']==$value['id'] ){
                $flag_dm = $value['name'];
                break;
              }
            }

            $urlPlan   = "<a href='"._URL_PG_PLAN_."?pictmail_id={$uDataS[$i]['pictmail_id']}'>[ 変更 ]</a>";
            $urlPay    = "<a href='"._URL_PG_PAY_."?user_id={$uDataS[$i]['user_id']}'>[ 確認・修正 ]</a>";
            $urlRenew  = "<a href='"._URL_PG_RENEW_."&user_id={$uDataS[$i]['user_id']}'>[ 修正・変更 ]</a>";
            $urlDelete = "<a href='"._URL_PG_DELETE_."&user_id={$uDataS[$i]['user_id']}&".SID."' onclick=\"return confirm('この会員を削除しますか？');\" >[ 削除 ]</a>";

            $bgcolor="#FFFFFF";
            if( $uDataS[$i]['flag_permission']==1 ){
              $urlPermission = "<a href='"._URL_PG_PERMISSION2_."&user_id={$uDataS[$i]['user_id']}&".SID."' onclick=\"return confirm('この会員の使用を不可にしますか？');\" >[ 不可にする ]</a>";
            }

            if( $uDataS[$i]['flag_permission']==2 ){
              $bgcolor="#CCCCCC";
              $urlPermission = "<a href='"._URL_PG_PERMISSION1_."&user_id={$uDataS[$i]['user_id']}&".SID."' onclick=\"return confirm('この会員の使用を許可しますか？');\" >[ 許可する ]</a>";
            }

            $date_insert_date = $libDaytime->getDateFromTimestamp( $uDataS[$i]['date_insert'],"/","/" );
            $date_insert_time = $libDaytime->getTimeFromTimestamp( $uDataS[$i]['date_insert'],":",":" );
            //$date_insert.= "<br>".substr($libDaytime->getTimeFromTimestamp( $uDataS[$i]['date_insert'],":",":" ),0,5);

            echo "
              <tr bgcolor='{$bgcolor}' class='black10' align='center'> 
                <td>
                  {$uDataS[$i]['user_id']}
                </td>
                <td>
                  {$date_insert_date}<br>
                  {$date_insert_time}
                </td>
                <td>
                  {$uDataS[$i]['name_family']} {$uDataS[$i]['name_first']}<br>
                  {$uDataS[$i]['kana_family']} {$uDataS[$i]['kana_first']}
                </td>
                <td>
                  {$uDataS[$i]['plan']}
                </td>
                <td>
                  {$uDataS[$i]['name_company']}<br>
                  {$uDataS[$i]['kana_company']}
                </td>
                <td>
                  {$uDataS[$i]['send_now']} / {$uDataS[$i]['send_max']}
                </td>
                <td>
                  {$urlPlan}
                </td>
                <td>
                  {$urlRenew}
                </td>
                <td>
                  {$urlDelete}
                </td>
                <td>
                  {$urlPermission}
                </td>
              </tr>
            ";


          }
          $i++;
        }
        

        echo "
          </table>
        ";

      }

      return;
    }



    /*
     * 都道府県セレクトボックス出力
     */
    function selectboxKen($name=False,$selected=False){
      global $libCode,$expUtil;

      if( $this->debug ) echo"<font size='1'> - "._ROOT_PG_."OutputMaster.php | selectBoxKen({$name},{$selected})</font> <br>\n";

      echo $libCode->encodeBase( $expUtil->selectboxKen( $name, $selected ), $this->code_output, 'EUC-JP');

      return;
    }


    /*
     * エラー文 出力
     */
    Function error(){
      global $libUtil, $libCode, $expUtil, $pField, $pVariable;

      if( $this->debug ) echo"<font size='1'> - "._ROOT_PG_."OutputMaster.php | error()</font> <br>\n";

      $error = "";

      if( $pVariable->errorS ){
        $error = "<br>";
        if( isset($pVariable->errorS) && $pVariable->errorS ){
          foreach($pVariable->errorS as $key=>$word){
            $error .= "{$word}<br>\n";
          }
        }
        $error .= "<br>";

      }
      echo $libCode->encodeBase( $error, $this->code_output, 'EUC-JP');

      return ;
    }


    /*
     * hidden出力
     */
    Function hidden($next=False ){
      global $libUtil, $libCode, $expUtil, $pField, $pVariable;

      if( $this->debug ) echo"<font size='1'> - "._ROOT_PG_."OutputMaster.php | hidden({$next})</font> <br>\n";

      $hidden = "";
      $hidden .= "<input type='hidden' name='".session_name()."' value='".session_id()."'>\n";
      $hidden .= "<input type='hidden' name='encoding'           value='もじこーどはんていようていすう'>\n";

      if( $next ) $hidden .= "<input type='hidden' name='hidden' value='{$next}'>\n";

      if( isset($pVariable->hiddenS) && is_array($pVariable->hiddenS) ){
        foreach($pVariable->hiddenS as $name=>$value ){
          $hidden .= "<input type='hidden' name='{$name}' value='{$value}'>\n";
          if( $this->debug ) echo"<font size='2'>＜input type='hidden' name='{$name}' value='{$value}'＞</font><br>\n";
        }
      }

      echo $libCode->encodeBase( $hidden, $this->code_output, 'EUC-JP');

      return;
    }

  }
?>
