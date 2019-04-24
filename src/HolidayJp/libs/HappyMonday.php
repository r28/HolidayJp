<?php
namespace r28\HolidayJp\libs;

use r28\AstroTime\AstroTime;

require_once 'HolidayBase.php';

/**
 * ハッピーマンデー定義
 *  - 第 X 週の月曜が祝日になる
 *  - 設定はHJSONファイルに記載 (libs/{$name}.hjson)
 *  - 設定:
 *  [
 *    {
 *      month: <月>
 *      week: <週>
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
class HappyMonday extends HolidayBase
{
    protected static $name = 'Happy_Mondays';

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
        $w = $date->dayOfWeek;
        $wm = self::getWeek($date);
        if ($w != 1) return false;

        $settings = (is_null($params)) ? static::parseSetting() : $params;
        if (! $settings) {
            throw new Exception("Cannot parse setting: 'HappyMonday'");
        }

        foreach ($settings as $val) {
            if ($m == $val->month && $wm == $val->week) {
                if ($y >= $val->start_year && $y <= $val->end_year) {
                    return $val->name;
                }
            }
        }
        return false;
    }

    /**
     * 指定した年月日が月の第何週かを返す
     *
     * @param  AstroTime    $date   対象日時(AstroTime Object)
     * @return Integer  週の数字
     */
    public static function getWeek(AstroTime $date) {
        $day_num = (int)$date->day;
        $day_of_week_num = $date->dayOfWeek;
        $firstday_week_num = $date->copy()->day(1)->dayOfWeek;

        if ((int) ($day_num % 7) != 0) {
            $week_num = (int) ($day_num / 7) + 1;
        } else {
            $week_num = (int) ($day_num / 7);
        }
        if (($firstday_week_num != 0) && ($firstday_week_num <= $day_of_week_num)) {
            $week_num--;
            $week_num++;
        }
        return $week_num;
    }
}