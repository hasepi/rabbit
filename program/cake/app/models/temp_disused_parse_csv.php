<?php
    class TempDisusedParseCsv extends AppModel {
        //  PHP4���桼���Ѥθߴ����Τ���
        var $name = "TempDisusedParseCsv";

        var $useTable = "temp_disused_parse_csvs";

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
       // var $_schema = array();

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
        * cav�ե�����򥵡��С�����¸����
        * @param object $userSessionId
        * @return void
        */
        function moveCsvFile($userSessionId) {
            $tempDisusedParseCsv = $this->data['TempDisusedParseCsv'];
            $micro_time = microtime(true);
            $arr = explode('.',$micro_time);
            $this->file_name = '-del'.$arr[0].'.csv';
            // ����ե�������ư������
            if (move_uploaded_file($tempDisusedParseCsv['csv2']['tmp_name'], CSV_PATH.$userSessionId.$this->file_name)) {
                // ���¤��ѹ�����
                chmod(CSV_PATH.$userSessionId.$this->file_name, 0777);

                //���󥳡��ǥ���
                $convertCsv = mb_convert_encoding(file_get_contents(CSV_PATH.$userSessionId.$this->file_name), "EUC-JP", "SJIS");
                //ʸ�����ե��������¸
                file_put_contents(CSV_PATH.$userSessionId.$this->file_name, $convertCsv);

                //return true;
                return CSV_PATH.$userSessionId.$this->file_name;
            } else {
                throw new Exception("�����csv�ե��������¸�˼��Ԥ��ޤ�����");
            }
        }

        /**
        * cav�ե�������copy from�פǤ���褦�����Ǥο���·����
        * @param object $userSessionId
        * @return boolean
        */
        function convertCsvFile($userSessionId) {
            setlocale(LC_ALL, 'ja_JP');
            $tempDisusedParseCsv = $this->data['TempDisusedParseCsv'];
            $tmpCsvData = "";

            $fp = fopen(CSV_PATH.$userSessionId.$this->file_name, 'r');
            try {
                while (($csvData = fgetcsv($fp)) !== false) {
                	$tmpCsvData .= $userSessionId.",";
                    $tmpCsvData .= $userSessionId.",";
                    foreach ($csvData as $key => $value) {
                        //�������᡼������ʤ�
                        if (preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $value)) {
                            $tmpCsvData .= $value;
                            //�᡼�륢�ɥ쥹�����Ĥ��ä���롼�פ�ȴ����
                            break;
                        }
                    }
                    
                    //�����ʸ����إ���С���
                    //$tmpCsvData .= implode(",", $csvData);
                    $tmpCsvData .= "\n";
                }
                //ʸ�����ե��������¸
                if (!file_put_contents(CSV_PATH.$userSessionId.$this->file_name, $tmpCsvData)) {
                    $this->invalidate('error','<span style="color:#FF0000;">ͽ�����̥��顼��ȯ�����ޤ�����</span>');
                    throw new Exception("�����csv�ե��������¸�˼��Ԥ��ޤ�����");
                }

            } catch (Exception $e) {
                return false;
            }
        }

        /**
        * cav�ե������ǡ����١����˳�Ǽ����
        * @param object $up_disused_file
        * @return boolean
        */
        function saveTempDisusedParseCsvDb($userSessionId) {
            $tempDisusedParseCsv = $this->data['TempDisusedParseCsv'];
            // �ȥ�󥶥���������
            $this->begin();

            try {
                $sql = "COPY temp_disused_parse_csvs FROM'".CSV_PATH.$userSessionId.$this->file_name."' WITH CSV";

                $res = pg_query($sql);

                if (!$res) {
                    throw new Exception("�����csv�ե��������¸�˼��Ԥ��ޤ�����");
                }
                $this->commit();
            } catch(Exception $e) {
                // ������Хå�����
                $this->rollback();

                return false;
            }
        }

        /**
        * ���פˤʤä��ǡ����١�����������
        * @param object $userSessionId
        * @return boolean
        */
        function deleteData($userSessionId) {
            $TempDisusedParseCsvs = $this->data['TempDisusedParseCsv'];

            // �ȥ�󥶥���������
            $this->begin();

            try {
                $sql = " DELETE FROM temp_disused_parse_csvs WHERE user_id = '".$userSessionId."'";
                $res = pg_query($sql);

                if (!$res) {
                    throw new Exception("�ǡ����١����κ���˼��Ԥ��ޤ�����");
                }
                $this->commit();
            } catch(Exception $e) {
                // ������Хå�����
                $this->rollback();

                return false;
            }
        }

    }
?>