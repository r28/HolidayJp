<?php

namespace r28\HolidayJp;

set_include_path(get_include_path().':'.dirname(__FILE__).'/libs/');
set_include_path(get_include_path().':'.dirname(__FILE__).'/settings/');

use r28\AstroTime\AstroTime;
require_once 'HolidayJp.php';
require_once 'libs/StationalyHoliday.php';
require_once 'libs/HappyMonday.php';
require_once 'libs/SpecifiedMoved.php';
require_once 'libs/Equinox.php';
require_once 'libs/ExportCsv.php';

/**
 * 日本の祝日判定用設定
 * 
 * @require r28\AstroTime
 */
class HolidayJpSetting
{
    public $holidayJp;

    public function __construct() {
        $this->holidayJp = new HolidayJp;
    }

    /**
     * 設定ファイル編集メニュー表示
     * 
     */
    public function editSettingMainMenu() {
        echo PHP_EOL;
        echo str_repeat("=", 20).PHP_EOL;
        echo "[祝日設定]".PHP_EOL;
        echo str_repeat("=", 20).PHP_EOL;
        echo PHP_EOL;

        $n = 1;
        $params = [];
        foreach ($this->holidayJp->params as $key=>$param) {
            $params[$n] = $key;
            echo " \033[0;32m{$n} ) {$param['desc']}\033[0m".PHP_EOL;
            $n++;
        }

        echo PHP_EOL;
        echo "  設定する祝日の番号を選んでください > ";
        $num = (int) trim(fgets(STDIN));
        if (empty($num) || ! isset($params[$num])) {
            echo "\033[0;31m  終了します!\033[0m".PHP_EOL;
            exit;
        }

        $selected = $this->holidayJp->params[$params[$num]];
        echo "  選択 : {$selected['desc']}".PHP_EOL;

        $class = 'r28\\HolidayJp\\libs\\' . $selected['class_name'];
        $csv = $class::parseCsv();
        print_r($csv);
    }

}