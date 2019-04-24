<?php
namespace r28\HolidayJp\libs;

use r28\AstroTime\AstroTime;

require_once 'HolidayBase.php';

/**
 * 固定祝日定義
 *  - 月日が固定された祝日
 *  - 設定はHJSONファイルに記載 (libs/{$name}.hjson)
 *  - 設定:
 *  [
 *    {
 *      month: <月>
 *      day: <日>
 *      name: <祝日名称>
 *      start_year: <開始年月日>
 *      end_year: <終了年月日> (現在使用中で終了年月日が定まっていない場合は 9999 など)
 *    }
 *    {
 *      ...
 *    }
 *    ...
 *  ]
 * 
 * @property AstroTime  $date       指定日付(AstroTimeオブジェクト)
 * @property integer    $unix_time  指定日付(UnixTime)
 * @property float      $jd         指定日付(ユリウス日)
 * @property string|boolean     $is_matched     祝日判定結果
 * 
 * @method void     __construct()
 * @method object   parseSetting()  設定ファイル読み込み
 */
class StationalyHoliday extends HolidayBase
{
    protected static $name = 'Stationaly_Holidays';

    /**
     * 指定の日付が固定の祝日にマッチするか
     * 
     * @param   AstroTime       $date   指定日付オブジェクト
     * @param   object          $params 設定
     * @return  string|boolean  祝日: 祝日名,  それ以外: false
     */
    public static function isMatched(AstroTime $date, $params=null) {
        $y = $date->year;
        $m = $date->month;
        $d = $date->day;

        $settings = (is_null($params)) ? static::parseSetting() : $params;
        if (! $settings) {
            throw new Exception("Cannot parse setting: 'StationalyHoliday'");
        }

        foreach($settings as $num=>$val) {
            $_m = $val->month;
            $_d = $val->day;
            if ($m == $_m && $d == $_d) {
                if ($y >= $val->start_year && $y <= $val->end_year) {
                    return $val->name;
                }
            }
        }
        return false;
    }
}