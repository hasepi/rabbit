<?php
    class ParseCsvController extends AppController {
        /*
        SSL�λ��ѡ��Ի���
        ���ѻ� $sslflag = true;
        */
        var $sslflag = true;

        /*
        controller̾

        @var string
        @access public
        */
        var $name = "ParseCsv";

        /**
         * ���Ѥ����ǥ롣ʣ����ǥ����Ѥ�����ϡ�������Ϥ�
         * �Ի��ѻ���false
         * var $uses = array("model1","model2");
         *
         * @var mixed
         * @access public
         */
        var $uses = array('ParseCsv', 'TempParseCsv', 'TempDisusedParseCsv');

        /**
         * ���Ѥ���إ�ѡ�
         * Html, Form, Session �إ�ѡ��ϡ��ǥե���Ȥ����Ѥ��뤳�Ȥ���ǽ��
         * �Ի��ѻ���false
         * $helpers = array('helpers1','helpers2');
         *
         * @var mixed
         * @access public
         */
        var $helpers = array('Form','Javascript','AppForm');

        /**
         * ���Ѥ��륳��ݡ��ͥ��
         * �Ի��ѻ���false
         * $components = array('components1','components2');
         *
         * @var mixed
         * @access public
         */
        var $components = array('Session');

        /**
         * ���Ѥ���쥤�����ȡ�
         * app/views/layouts/��������֤���쥤������̾�����ꤹ�롣
         * ������Ƥ��ʤ����ϡ�app/view/layouts/default.ctp��ɽ��(render)���롣
         * $layout = "itm.ctp";
         * app/view/layouts/itm.ctp
         *
         * @var string
         * @access public
         */
        var $layout = "default";

        /*
        �ڡ����Υ����ȥ�
        layout����ˡ�$titlie_for_layout�Ȥ��ƻ��Ѳ�ǽ
        */
        var $pageTitle = "csv����";

        //������CSV̾
        var $temp_parse_csv_name;
        
        //�����CSV̾
        var $temp_disued_parse_csv_name;


        /**
         * construct�᥽�å�
         *
         * @param void
         * @access public
         * @return void
         */
        function __contrust() {
            parent::__construct();
        }

        /*
        �ǥե���ȤǸƤӽФ����᥽�å�
        */
        function index() {
            //submitkey�򥻥å�
            $submit = $this->data['submit'];
            $this->sub_key = @key($submit);

            switch($this->sub_key){
                case 'upload' :
                    $this->_upload();
                    break;
                default :
                    $this->_setCsvfile();
            }
        }

        /**
         * ���ϲ��̤ν���
         *
         * @param void
         * @access praivate
         * @return void
         */
        function _setCsvfile() {
            $this->render('set_csv_file');
        }

        /**
         * ������csv�Ⱥ����csv����Ͽ����
         *
         * @param void
         * @access praivate
         * @return void
         */
        function _upload() {
        //    $this->TempParseCsv->setConnection();
            //��ǥ�˥ǡ������Ϥ�
            $this->ParseCsv->create($this->data['ParseCsv']);
            $this->TempParseCsv->create($this->data['ParseCsv']);
            $this->TempDisusedParseCsv->create($this->data['ParseCsv']);

            // �桼��ID�μ���
            $userSessionId = $this->_getSessionId();
            if(!$userSessionId){
                //���顼�ڡ�����
                $this->render('access_err');
            }
            //  �������ƤΥ��顼�����å�
            $val_flag = $this->ParseCsv->validates();
            // csv�����ϥ��顼�����å�
            // ���顼���ʤ��ä���
            if($val_flag) {
                // �ǡ����١�����Ͽ��˥��顼��ȯ�����ʤ��ä���
                try {

                    //�ե��������¸
                    $this->temp_parse_csv_name = $this->TempParseCsv->moveCsvFile($userSessionId);
                    $this->temp_disued_parse_csv_name = $this->TempDisusedParseCsv->moveCsvFile($userSessionId);

                    //�ե�����Υ���С���
                    $this->TempParseCsv->convertCsvFile($userSessionId);
                    $this->TempDisusedParseCsv->convertCsvFile($userSessionId);

                    // ������csv��ǡ����١�������Ͽ����
                    $this->TempParseCsv->saveTempParseCsvDb($userSessionId);
                    // �����csv��ǡ����١�������Ͽ����
                    $this->TempDisusedParseCsv->saveTempDisusedParseCsvDb($userSessionId);

                    // �ե��������Ϥ���
                    $downloadCsv = $this->TempParseCsv->getqueryData($userSessionId);
                    $this->TempParseCsv->deleteData($userSessionId);
                    $this->TempDisusedParseCsv->deleteData($userSessionId);

                } catch (Exception $e) {
                    $this->appCakeError($e);
                }
                if ($downloadCsv == 1) {
                    //���顼���Υǥ�����
                    $this->render('error');

                } else {
                    //�����ʸ�����
                    $newCsvData = $this->ParseCsv->convertQueryArray($downloadCsv, $userSessionId);
                    $this->_deleteFile($userSessionId);
                    // �ե�����Υ����������
                    $this->_download($userSessionId, $newCsvData);

                    //��λ
                    exit;
                }
            } else {
               //���顼���Υǥ�����
                $this->render('error');
            }
        }

         /**
         * �桼��ID�μ���
         *
         * @param void
         * @access praivate
         * @return $user_ran
         */
        function _getSessionId() {
            session_cache_limiter('public');
            session_start();
            if(isset($_SESSION['user']['user_id'])){
                return $_SESSION['user']['user_id'];
            }else{
                return false;
            }
        }

        /**
         * ���顼���ν���
         *
         * @param $e
         * @access praivate
         * @return void
         */
        function appCakeError($e){
            $err_msg = $e->getMessage()."\n".$e->getFile()." on line ".$e->getLine()."\n";
            $params = array(array('err_msg'=>$err_msg,'mail_flag'=>ERROR_EMAIL_FLAG));
            $this->cakeError("error",$params);
            exit;
        }
        /**
        * ���פʥǡ�����ǡ����١�������������
        *
        * @param object $userSessionId
        * @access praivate
        * @return void
        */
        function deleteParseCsvDb($userSessionId) {
            $this->TempParseCsv->deleteAll("user_id");
            $this->TempDisusedParseCsv->deleteAll("user_id");
        }

        /**
         * �ե�����Υ����������
         *
         * @param $userSessionId, $newCsvData
         * @access praivate
         * @return void
         */
        function _download($userSessionId, $newCsvData) {
            $newCsvData = mb_convert_encoding($newCsvData, "SJIS-win", Configure::read("App.encoding"));
            $this->autoRender = false; // View��Ȥ�ʤ��褦��
            $csv_file = sprintf("%s_%s.csv", $userSessionId, date("Ymd-his"));
            header("Content-disposition: attachment; filename=".$csv_file);
            header("Content-type: application/octet-stream; name=".$csv_file);
            print $newCsvData;

            return;
        }
		
        /**
         * �ե�����κ��
         *
         * @param $userSessionId
         * @access praivate
         * @return void
         */
        function _deleteFile($userSessionId) {
            //�ե�����κ��
            unlink($this->temp_parse_csv_name);
            unlink($this->temp_disued_parse_csv_name);
        }
    }
?>