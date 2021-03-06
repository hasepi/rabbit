<?PHP
/*

  メール一括送信

*/
  ini_set("display_errors",1);

  // SetUp
  require_once '../../cls/mail/Setup.php';

  if( _DOMAIN_=='test.itm-asp.com' || getenv("REMOTE_ADDR")=='221.246.192.14' ){

    ini_set("display_errors", 1);
    define("_TEST_", True );
  }else{
    define("_TEST_", False );
  }
  define("_DEBUG_",False );

  $expAttest->check();

  // SET:PG Setting
  // name
  define('_NAME_', 'mail' );

  define('_LIST_MAX_', 20 );

  if( _TEST_ ){
    define('_NAME_FROM_',  'itm-asp');
    define('_NAME_TO_',    'itm-asp');
    define('_MAIL_FROM_',  'masaki@itm.ne.jp');
    define('_MAIL_TO_',    'masaki@itm.ne.jp');
    define('_MAIL_ERROR_', 'masaki@itm.ne.jp');
  }else{
    define('_NAME_FROM_',  'itm-asp');
    define('_NAME_TO_',    'itm-asp');
    define('_MAIL_FROM_',  'info@itm-asp.com');
    define('_MAIL_TO_',    'info@itm-asp.com');
    define('_MAIL_ERROR_', 'info@itm-asp.com');
  }


  // Root
  define('_ROOT_PG_',      _ROOT_CLS_MAIL_);
  define('_ROOT_PG_HTML_',      _ROOT_CLS_MAIL_."html/");
//  define('_ROOT_PG_HTML_', _ROOT_MEMBER_.'mail/');
  define('_ROOT_PG_DAT_',  _ROOT_DAT_MAIL_);

  // Html
  define('_HTML_PG_BASE_',    _ROOT_PG_HTML_.'base.html' );
  define('_HTML_PG_MENU_',    _ROOT_PG_HTML_.'menu.html' );
  define('_HTML_PG_LIST_',    _ROOT_PG_HTML_.'list.html' );
  define('_HTML_PG_WRITE_',   _ROOT_PG_HTML_.'write.html' );
  define('_HTML_PG_CONFIRM_', _ROOT_PG_HTML_.'confirm.html' );
  define('_HTML_PG_FINISH_',  _ROOT_PG_HTML_.'finish.html' );
  define('_HTML_PG_ERROR_',  _ROOT_PG_HTML_.'error.html' );

  // Url
  define('_URL_PG_',         _URL_MEMBER_MAIL_);
  define('_URL_PG_TOP_',     _URL_MEMBER_MAIL_TOP_);
  define('_URL_PG_LIST_',    _URL_PG_TOP_.'?get=list');
  define('_URL_PG_NEW_',     _URL_PG_TOP_.'?get=new');
  define('_URL_PG_RENEW_',   _URL_PG_TOP_.'?get=renew');
  define('_URL_PG_REWRITE_', _URL_PG_TOP_.'?get=rewrite');
  define('_URL_PG_DELETE_',  _URL_PG_TOP_.'?get=delete');

  // Image
  define('_IMAGE_PG_', _URL_PG_.'image/');

  // LOAD:PARTS
  require_once _ROOT_PG_.'Session.php';
  require_once _ROOT_PG_.'Field.php';
  require_once _ROOT_PG_.'Variable.php';
  require_once _ROOT_PG_.'FileUp.php';
  require_once _ROOT_PG_.'Output.php';
  require_once _ROOT_PG_.'Send.php';
  require_once _ROOT_PG_.'Query.php';


  // SET:PARTS
  $pSession  = new Session(_NAME_,_DEBUG_);
  $pField    = new Field(_DEBUG_,_TEST_);
  $pVariable = new Variable(_DEBUG_);
  $pFileUp   = new FileUp($_SESSION['user']['send_max'],'',_DEBUG_);
  $pOutput   = new Output(_HTML_PG_BASE_,'EUC-JP',_LIST_MAX_,_DEBUG_);
  $pSend     = new Send(_DEBUG_);
  $pQuery    = new Query(_DEBUG_);

  $pSession->relogin();

  // Output
  if($_SESSION['user']['send_now']<=0){

    $pSession->mode('send');
    $pSession->place('over');

  }else{

    switch( $expDistribution->exe ){

      // テスト
      case 'test':
        $pSession->place('write');
        if( $pVariable->test($_POST,$_FILES['file_mail']) ){
          $pSession->place('error');
        }else{
          $pSend->test();
        }
        break;

      // テストHTML
      case 'test_html':
        $pSession->place('write');
        if( $pVariable->test($_POST,$_FILES['file_mail']) ){
          $pSession->place('error');
        }else{
          $pSend->test_html();
        }
        break;

      // 確認
      case 'confirm':
        $pSession->place('confirm');
        if( $pVariable->confirm($_POST,$_FILES['file_mail']) ){
          $pSession->place('error');
        }
        if( $pFileUp->tempMail($_FILES['file_mail'], _ROOT_PG_DAT_.$pVariable->inputS['file_mail']) ){
          $pVariable->tempMailError($pFileUp->errorWord,'Input_files');
          $pSession->place('error');
        }
        break;

      // 再入力
      case 'rewrite':
        $pSession->place('write');
        $pVariable->rewrite($_POST);
        $pFileUp->delete(_ROOT_PG_DAT_.$pVariable->inputS['file_mail']);
        break;

      // 終了
      case 'finish':
        if( $_SESSION[_NAME_]['place']!='finish' ){
          $pSession->place('finish');
          $pVariable->finish($_POST);
          $pSend->runBatch();
        }
        break;

      // 最初期(入力)
      default:
        $pSession->format();
        $pSession->mode('send');
        $pSession->place('write');
        $pVariable->write();
    }
  }
  $pOutput->html($_SESSION[_NAME_ ]['mode'], $_SESSION[_NAME_ ]['place']);

  // SHOW DEBUG
  $libDebug->arrayView($_SESSION[_NAME_],'$_SESSION[\''._NAME_.'\']',_DEBUG_);
  $libDebug->arrayView($_POST,'$_POST',_DEBUG_);
  $libDebug->arrayView($_GET, '$_GET',_DEBUG_);
  $libDebug->arrayView($pVariable->inputS, '$pVariable->inputS',_DEBUG_);
  $libDebug->arrayView($pField->dbS, '$pField->dbS',_DEBUG_);
  $libDebug->defineView(_DEBUG_);

  exit;
?>
