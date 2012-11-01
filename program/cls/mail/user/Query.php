<?PHP

class Query extends Html {

  function Query(){

    if( _DEBUG_ ){
      require_once(_DIR_LIB_.'debug/Debug.php');
      $this->Debug = new Debug();
    }

  }

  // DB��³
  function dbConnect(){
    require_once(_DIR_LIB_."db/Postgres.php");
    require_once(_DIR_LIB_."db/ExPostgres.php");
    $ExPostgres = new ExPostgres(_DB_NAME_,_DB_USER_,_DB_HOST_,_DB_PORT_);
    return $ExPostgres;
  }



  // *SET* Query
  function setQuery($Manager=False){

    foreach($Manager->inputS as $key=>$value ){
      $this->sqlS[$key] = $this->getSql($value);
    }

    $this->sqlS['subject'] = str_replace(":", "��", $this->sqlS['subject']);

    $query['td_log']      = $this->getInsertTdLog($this->sqlS,$Manager->uploadS);
    $query['td_message']  = $this->getInsertTdMeesage($this->sqlS,$Manager->uploadS);
    $query['td_pictmail'] = $this->getUpdateTdPictmail($this->sqlS,$Manager->uploadS);
    $query['td_mailq']    = $this->getInsertTdMailq($this->sqlS,$Manager->uploadS);

    return $query;
  }


  // *SET* update td_pictmail
  function getUpdateTdPictmail($sqlS=False,$uploadS=False){

    $query  = "UPDATE td_pictmail SET ";
    $query .= " send_now=(send_now-{$uploadS['numS']['ok']}), ";
    $query .= " date_update='now' ";
    $query .= " WHERE pictmail_id = {$_SESSION['user']['pictmail_id']} ";

    return $query;
  }


  // *SET* td_log
  function getInsertTdLog($sqlS=False,$uploadS=False){

    require_once(_DIR_LIB_."util/Browse.php");
    $Browse = new Browse();

    $column  = "";
    $values  = "";
    $month_count = "(SELECT count(*) FROM td_log WHERE user_id={$_SESSION['user']['user_id']} AND date_insert::text >= (SELECT to_char(current_timestamp, 'yyyy-mm-01')))+1";
    $column .= "log_id,";
    $values .= "{$sqlS['log_id']},";

    $column .= "user_id,";
    $values .= "{$_SESSION['user']['user_id']},";

    $column .= "message_id,";
    $values .= "{$sqlS['message_id']},";

    $column .= "name_from,";
    $values .= "'{$sqlS['name_from']}',";

    $column .= "mail_error,";
    $values .= "'{$sqlS['mail_error']}',";

    $column .= "mail_from,";
    $values .= "'{$sqlS['mail_from']}',";

    $column .= "mail_confirm,";
    $values .= "'{$sqlS['mail_confirm']}',";

    $column .= "month_count,";
    $values .= "{$month_count},";

    $column .= "send_count,";
    $values .= "{$uploadS['numS']['ok']},";

    $column .= "send_count_pc,";
    $values .= "{$uploadS['numS']['pc']},";

    $column .= "send_count_mobile,";
    $values .= "{$uploadS['numS']['mobile']},";

    $column .= "subject,";
    $values .= "'{$sqlS['subject']}',";

    $column .= "message,";
    $values .= "'#message#',";

    $column .= "message_html,";
    $values .= "'#message_html#',";

    $column .= "send_date,";
    $values .= "'{$sqlS['send_date']}',";

    $column .= "flag_pc,";
    $values .= "{$uploadS['flagS']['pc']},";

    $column .= "flag_mobile,";
    $values .= "{$uploadS['flagS']['mobile']},";

    $column .= "flag_type,";
    $values .= "{$uploadS['flagS']['type']},";

    $column .= "ip,";
    $values .= "'{$Browse->remote_address}',";

    $column .= "host,";
    $values .= "'{$Browse->remote_host}',";

    $column .= "date_pc,";
    $values .= "'now',";

    $column .= "date_mobile,";
    $values .= "'now',";

    $column .= "date_insert,";
    $values .= "'now',";

    $column = substr($column,0,-1);
    $values = substr($values,0,-1);

    $query = "INSERT INTO td_log({$column}) VALUES ({$values})";

    return $query;
  }



  // *SET* td_message
  function getInsertTdMeesage($sqlS=False,$uploadS=False){

    $column  = "";
    $values  = "";

    $column .= "message_id,";
    $values .= "{$sqlS['message_id']},";

    $column .= "user_id,";
    $values .= "{$_SESSION['user']['user_id']},";

    $column .= "count,";
    $values .= "{$uploadS['numS']['ok']}+1,";

    $column .= "email_from,";
    $values .= "'{$sqlS['mail_from']}',";

    $column .= "email_from_name,";
    $values .= "'{$sqlS['name_from']}',";

    $column .= "email_error,";
    $values .= "'{$sqlS['mail_error']}',";

    $column .= "subject,";
    $values .= "'{$sqlS['subject']}',";

    $column .= "message,";
    $values .= "'#message#',";

    $column .= "message_html,";
    $values .= "'#message_html#',";

    $column .= "flag_html,";
    $values .= "{$sqlS['flag_html']},";

    $column .= "send_date,";
    $values .= "'{$sqlS['send_date']}',";

    $column = substr($column,0,-1);
    $values = substr($values,0,-1);

    $query  = "INSERT INTO td_message({$column}) VALUES ({$values})";

    return $query;
  }


  // *SET* td_mailq
  function getInsertTdMailq($sqlS=False,$uploadS=False){

    $column  = "";
    $values  = "";

    $column .= "email,";
    $values .= "'#email#',";

    $column .= "email_name,";
    $values .= "'#name#',";

    $column .= "message_id,";
    $values .= "{$sqlS['message_id']},";

    $column .= "flag_pc,";
    $values .= "'#flag_pc#',";

    $column .= "parameter1,";
    $values .= "'#p1#',";

    $column .= "parameter2,";
    $values .= "'#p2#',";

    $column .= "parameter3,";
    $values .= "'#p3#',";

    $column .= "parameter4,";
    $values .= "'#p4#',";

    $column .= "parameter5,";
    $values .= "'#p5#',";

    $column = substr($column,0,-1);
    $values = substr($values,0,-1);

    $query  = "INSERT INTO td_mailq({$column}) VALUES ({$values})";

    return $query;
  }




}
?>
