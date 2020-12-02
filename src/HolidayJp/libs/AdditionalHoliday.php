<?php
namespace r28\HolidayJp\libs;

use r28\AstroTime\AstroTime;

require_once 'HolidayBase.php';

/**
 * 追加固定休日定義
 *  - 年月日が固定された任意の休日(夏季/年末年始休暇など)
 *  - 全ての祝日/振替休日に優先する
 *  - 設定はHJSONファイルに記載 (libs/{$name}.hjson)
 *  - 設定:
 *  [
 *    {
 *      from_date: <期間(始)年月日(YYYY-MM-DD)>
 *      to_date: <期間(至)年月日(YYYY-MM-DD)>
 *      name: <祝日名称>
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
class AdditionalHoliday extends HolidayBase
{
    protected static $name = 'Additional_Holidays';

    /**
     * 指定の日付が休日にマッチするか
     * 
     * @param   AstroTime       $date   指定日付オブジェクト
     * @param   object          $params 設定
     * @return  string|boolean  祝日: 祝日名,  それ以外: false
     */
    public static function isMatched(AstroTime $date, $params=null) {
        $settings = (is_null($params)) ? static::parseSetting() : $params;

        // 設定がない場合はスルー
        if (empty($settings) || ! $settings) return false;

        foreach($settings as $num=>$val) {
            // from_date 空欄はスルー
            if (empty($val->from_date) || ! $val->from_date) return false;

            $from = AstroTime::createFromFormat("Y-m-d H:i:s", "{$val->from_date} 00:00:00");
            // to_date 空欄の場合は from_date と同日
            $to = (empty($val->to_date) || ! $val->to_date) ? $val->from_date : $val->to_date;
            $to = AstroTime::createFromFormat("Y-m-d H:i:s", "{$to} 23:59:59");

            if ($date->between($from, $to)) {
                return $val->name;
            }
        }
        return false;
    }
}