<?PHP
/*

  �桼�����ץ��ޥ���������

*/


  // LOAD:Common Setting
  require_once '../../common/define/setup.php';

  // SetUp
  require_once '../../cls/plan/Setup.php';

  if( _DOMAIN_=='192.168.1.210' ){
    define("_TEST_", True );
  }else{
    define("_TEST_", False );
  }
  define("_DEBUG_",False );
  define("_ADMIN_",True );

  // SET:PG Setting
  // name
  define('_NAME_', 'plan_master' );

  define('_LIST_MAX_', 20 );

  if( _TEST_ ){
    define('_NAME_FROM_',  '[TEST]ITMô����');
    define('_NAME_TO_',    '[TEST]ITMô����');
    define('_MAIL_FROM_',  'masaki@itm.ne.jp');
    define('_MAIL_TO_',    'masaki@itm.ne.jp');
    define('_MAIL_ERROR_', 'masaki@itm.ne.jp');
  }else{
    define('_NAME_FROM_',  'ITMô����');
    define('_NAME_TO_',    'ITMô����');
    define('_MAIL_FROM_',  'masaki@pictsys.com');
    define('_MAIL_TO_',    'masaki@pictsys.com');
    define('_MAIL_ERROR_', 'masaki@pictsys.com');
  }

  // Root
  define('_ROOT_PG_',      _ROOT_CLS_PLAN_);
  define('_ROOT_PG_HTML_', _ROOT_CLS_PLAN_);
  define('_ROOT_PG_DAT_',  _ROOT_CLS_PLAN_);

  // Html
  define('_HTML_PG_BASE_',    _ROOT_PG_HTML_.'base.html' );
  define('_HTML_PG_MENU_',    _ROOT_PG_HTML_.'menu.html' );
  define('_HTML_PG_LIST_',    _ROOT_PG_HTML_.'list.html' );
  define('_HTML_PG_WRITE_',   _ROOT_PG_HTML_.'write.html' );
  define('_HTML_PG_CONFIRM_', _ROOT_PG_HTML_.'confirm.html' );
  define('_HTML_PG_FINISH_',  _ROOT_PG_HTML_.'finish.html' );

  define('_HTML_PG_CONFIRM_PLAN_', _ROOT_PG_HTML_.'confirm_plan.html' );
  define('_HTML_PG_FINISH_PLAN_', _ROOT_PG_HTML_.'finish_plan.html' );

  // Url
  define('_URL_PG_',         _URL_ITM_PLAN_);
  define('_URL_PG_TOP_',     _URL_ITM_PLAN_TOP_ );
  define('_URL_PG_LIST_',    _URL_PG_TOP_.'?get=list');
  define('_URL_PG_NEW_',     _URL_PG_TOP_.'?get=new');
  define('_URL_PG_RENEW_',   _URL_PG_TOP_.'?get=renew');
  define('_URL_PG_REWRITE_', _URL_PG_TOP_.'?get=rewrite');
  define('_URL_PG_DELETE_',  _URL_PG_TOP_.'?get=delete');
  define('_URL_PG_PLAN_',    _URL_ITM_PLAN_TOP_);
  define('_URL_PG_PAY_',     _URL_ITM_PAY_TOP_);

  // Image
  define('_IMAGE_PG_', _URL_PG_.'image/');


  // LOAD:PARTS
  require_once _ROOT_PG_.'Session.php';
  require_once _ROOT_PG_.'Field.php';
  require_once _ROOT_PG_.'Variable.php';
  require_once _ROOT_PG_.'OutputMaster.php';
  require_once _ROOT_PG_.'Query.php';
  require_once _ROOT_PG_.'CheckSp.php';
  require_once _ROOT_PG_.'Send.php';


  // SET:PARTS
  $pSession  = new Session(_NAME_,_DEBUG_);
  $pField    = new Field(_DEBUG_,_TEST_);
  $pVariable = new Variable(_DEBUG_);
  $pOutput   = new OutputMaster(_HTML_PG_BASE_,'EUC-JP',_LIST_MAX_,_DEBUG_);
  $pQuery    = new Query(_DEBUG_);
  $pCheckSp  = new CheckSp(_DEBUG_);
  $pSend     = new Send(_DEBUG_);


  // Output
  switch( $expDistribution->exe ){


    // ���
    case 'delete':
      $expPostgres->registQuery( $pQuery->delete_td_user($_GET['user_id']) );
      $pSession->mode('list');
      $pSession->place('list');
      $pSession->lists();
      $pVariable->listS();
      break;

    // ������Ͽ
    case 'new':
      $pSession->mode('new');
      $pSession->place('write');
      $pVariable->write();
      break;

    // ������Ͽ
    case 'renew':
      $pSession->mode('renew');
      $pSession->place('write');
      $pVariable->renew($_GET['user_id']);
      break;

    // ��ǧ
    case 'confirm':
      $pSession->place('confirm');
      if( $pVariable->confirm($_POST) ){
        $pSession->place('error');
      }
      break;


    // ������
    case 'rewrite':
      $pSession->place('write');
      $pVariable->rewrite($_POST);
      break;

    // ��λ
    case 'finish':
      if( $_SESSION[_NAME_]['place']!='finish' ){
        $pSession->place('finish');
        $pVariable->finish($_POST,$_SESSION[_NAME_ ]['mode']);
        switch($_SESSION[_NAME_ ]['mode']){
          case 'renew': $expPostgres->registQuery( $pQuery->update_td_pictmail() ); break;
        }
      }
      break;



    // ��ǧ�ץ��
    case 'confirm_plan':
      $errorS=$pCheckSp->checkConfirmPlan($_POST);
      if( $errorS!="" ){
        $pVariable->setError($errorS);
        $pVariable->rewrite($_POST);
        $pSession->place('write');
      }else{
        $pVariable->confirm($_POST);
        $pSession->place('confirm_plan');
      }
      break;

    // ��λ
    case 'finish_plan':
      if( $_SESSION[_NAME_]['place']!='finish_plan' ){
        $pSession->place('finish_plan');
        $pVariable->finish($_POST,$_SESSION[_NAME_ ]['mode']);
        switch($_SESSION[_NAME_ ]['mode']){
          case 'renew': $expPostgres->registQuery( $pQuery->update_plan() ); break;
        }
      }
      break;

    // �ǽ��(�ꥹ��)
    default:
      if(  isset($_GET['pictmail_id']) ){
        $pSession->format();
        $pSession->mode('renew');
        $pSession->place('write');
        $pVariable->renew($_GET['pictmail_id']);
      }
      break;
  }

  $pOutput->html($_SESSION[_NAME_ ]['mode'], $_SESSION[_NAME_ ]['place']);

  // SHOW DEBUG
  $libDebug->arrayView($_SESSION[_NAME_],'$_SESSION[\''._NAME_.'\']',_DEBUG_);
  $libDebug->arrayView($_POST,'$_POST',_DEBUG_);
  $libDebug->arrayView($_GET, '$_GET',_DEBUG_);
  $libDebug->arrayView($pVariable->inputS, '$pVariable->inputS',_DEBUG_);
  $libDebug->arrayView($pVariable->writeS, '$pVariable->writeS',_DEBUG_);
  $libDebug->arrayView($pVariable->viewS,  '$pVariable->viewS',_DEBUG_);
  $libDebug->arrayView($pField->dbS, '$pField->dbS',_DEBUG_);
  $libDebug->defineView(_DEBUG_);

  exit;
?>