<?php
namespace r28\HolidayJp\libs;

use r28\AstroTime\AstroTime;

require_once 'HolidayBase.php';

/**
 *  当該年のみ、特別立法等である祝日を別の日付に移す場合
 *  - 設定はHJSONファイルに記載 (libs/{$name}.hjson)
 *  - 設定:
 * {
 *  <西暦年>: 
 *  [
 *    {
 *      month: <月> (※当該の西暦年のみこの月日が祝日)
 *      day: <日>
 *      name: <祝日名称>
 *      original:
 *        {
 *          month: <元の祝日の月> (※当該の西暦年以外はこの月日が祝日)
 *          day: <元の祝日の日>
 *        }
 *    }
 *  ]
 *  ...
 * }
 * 
 * @property AstroTime  $date       指定日付(AstroTimeオブジェクト)
 * @property integer    $unix_time  指定日付(UnixTime)
 * @property float      $jd         指定日付(ユリウス日)
 * @property string|boolean     $is_matched     祝日判定結果
 * 
 * @method void     __construct()
 * @method object   parseSetting()  設定ファイル読み込み
 */
class SpecifiedMoved extends HolidayBase
{
    protected static $name = 'Specified_Moved';

    const IS_REMOVED_TYPE = 'is_removed';

    /**
     * 指定の日付が固定の祝日にマッチするか
     * 
     * @param   AstroTime       $date   指定日付オブジェクト
     * @param   object          $params 設定
     * @return  string|boolean  祝日: 祝日名,  それ以外: { 移動した元の祝日の場合: const IS_REMOVED_TYPE, 一切マッチしない: false }
     */
    public static function isMatched(AstroTime $date, $params=null) {
        $y = $date->year;
        $m = $date->month;
        $d = $date->day;

        $settings = (is_null($params)) ? static::parseSetting() : $params;
        if (! $settings) {
            throw new Exception("Cannot parse setting: 'SpecifiedMoved'");
        }

        foreach ($settings as $year=>$vals) {
            // 当該年でない
            if ($y != $year) continue;

            foreach ($vals as $val) {
                if ($m == $val->month && $d == $val->day) {
                    return $val->name;
                }
                if ($m == $val->original->month && $d == $val->original->day) {
                    return self::IS_REMOVED_TYPE;
                }
            }
        }
        return false;
    }

}