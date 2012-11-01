<?php
    class ParseCsv extends AppModel {
        //  PHP4���桼���Ѥθߴ����Τ���
        var $name = "ParseCsv";

        var $useTable = false;

        /**
         * /app/config/database.php�����������ɤΥѥ�᡼������Ѥ��뤫�����
         * �ǥե���Ȥ� 'default'
         *
         * @var string
         * @access public
         */
        var $useDbConfig = "default";

        /**
         * �ǡ����١����ơ��֥�ե�����ɤξܺ٤Ǥ���᥿�ǡ���
         * ��DB�Ի��ѻ���formhelper��Ȥ��ȥ��顼��������
         * ����Ȥ���$_schema��������ꤷ�Ƥ���ɬ�פ�����餷���Τǥ��ߡ�������
         */
        var $_schema = array();

        /**
         * ����å������ѡ��Ի���
         * @var boolean
         * @access public
         */
        var $cacheQueries = false;


        /**
         * ���Ѥ���ӥإ��ӥ�
         * �Ի��ѻ���false
         * $actAs = array('behavior'=>'array('param')','behavior2');
         *
         * @var mixed
         * @access public
         * @link http://book.cakephp.org/ja/view/90/Using-Behaviors
         */
        var $actsAs = array('Form', 'Convert', 'ExValidate');

        /**
         * ���ϥ����å�

         * VALID_NOT_EMPTY�����ϥǡ�����ɬ�����ϤǤ���
         * VALID_NUMBER�����ϥǡ����Ͽ��ͤǤʤ���Фʤ�ʤ�
         * VALID_EMAIL�����ϥǡ����ϣť᡼�륢�ɥ쥹�Ǥʤ���Фʤ�ʤ�
         * VALID_YEAR�����ϥǡ�����ǯ���Ǥʤ���Фʤ�ʤ�
         */

        var $validate = array(
            'csv1' => array(
                'rule1' => array(
                    'rule' => array('isUpLoadEmpty'),
                    'message' => '�ۿ��ꥹ��csv�����򤷤Ƥ�������'
                )
            ),
            'csv2' => array(
                'rule1' => array(
                    'rule' => array('isUpLoadEmpty'),
                    'allowEmpty' => true,
                    'message' => '�����csv�����򤷤Ƥ�������'
                )
            )
        );

        /**
         * ���åץ����ɤ��줿csv�ե����뤫Ĵ�٤�
         *
         * @return boolean ���åץ����ɤ��줿csv�ե�����Ǥ���� true ����ʳ� false ���֤�
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
         * ���ϲ��̤Ǽ�����ä��ͤ򥳥�С���
         *
         * @return array $tempParseCsvs
         */
        function setPostConvert() {
            $tempParseCsvs = $this->data['TempParseCsv'];
            return $tempParseCsvs;
        }


        /**
         * ���顼��å���������
         *
         * @param array $dataError ���顼��å���������
         */
         function getErrorMsgs($dataError) {
             //foreach�ǥ롼�פ��ɽ���ѤΥ��顼�����
              foreach($dataError as $key=>$value){
                $errorMsgs[] = '<span style="color:#FF0000;">'.$value.'</span><br>';
              }

             return $errorMsgs;
         }

        /**
        * �����ʸ������Ѵ�����
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
                // �����Ρ�,�פ�������
                $convertQueryArray = rtrim($convertQueryArray, ",");

                $convertQueryArray .= "\n";
            }
            return $convertQueryArray;
        }

    }
?>