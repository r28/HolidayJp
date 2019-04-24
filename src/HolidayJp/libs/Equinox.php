<?php
namespace r28\HolidayJp\libs;

use r28\AstroTime\AstroTime;

require_once 'HolidayBase.php';

/**
 * 春分日/秋分日判定
 *  - 設定はHJSONファイルに記載 (libs/{$name}.hjson)
 *  - 設定:
 * 
 * @property AstroTime  $date       指定日付(AstroTimeオブジェクト)
 * @property integer    $unix_time  指定日付(UnixTime)
 * @property float      $jd         指定日付(ユリウス日)
 * @property string|boolean     $is_matched     祝日判定結果
 * 
 * @method void     __construct()
 * @method object   parseSetting()  設定ファイル読み込み
 */
class Equinox extends HolidayBase
{
    protected static $name = 'Equinox';

    /**
     * 指定日付(AstroTimeオブジェクト)
     * @var AstroTime
     */
    public $date;


    /**
     * 指定の日付が春分日/秋分日にマッチするか
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
            throw new Exception("Cannot parse setting: 'Equinox'");
        }

        if ($m == 3) {
            // 3月 => 春分の日チェック
            $vernal_day = self::vernalEquinox($y, $settings->vernal->constants);
            if ($vernal_day == $d) return $settings->vernal->name;
        } else if ($m == 9) {
            // 9月 => 秋分の日チェック
            $autumnal_day = self::autumnalEquinox($y, $settings->autumnal->constants);
            if ($autumnal_day == $d) return $settings->autumnal->name;
        }

        return false;
    }

    /**
     * 春分の日 (簡易計算式)
     *  http://addinbox.sakura.ne.jp/holiday_topic.htm#syunbun
     * 
     * @param	integer     $year   対象西暦年
     * @param   object      $constant
     * @return	integer     日(3月)
     */
    public static function vernalEquinox($year, $constant=null) {
        if (is_null($constant)) {
            $settings = self::parseSetting();
            $constant = $settings->vernal->constants;
        }
        $const = self::filterValueForKeyLess((array)$constant, $year);
    	return floor($const + 0.242194 * ($year - 1980) - floor(($year - 1980) / 4));
    }

    /**
     * 秋分の日 (簡易計算式)
     *  http://addinbox.sakura.ne.jp/holiday_topic.htm#syunbun
     * 
     * @param	integer     $year   対象西暦年
     * @return	integer     日(9月)
     */
    public static function autumnalEquinox($year, $constant) {
        if (is_null($constant)) {
            $settings = self::parseSetting();
            $constant = $settings->vernal->constants;
        }
        $const = static::filterValueForKeyLess((array)$constant, $year);
    	return floor($const + 0.242194 * ($year - 1980) - floor(($year - 1980) / 4));
    }


    /**
     * 連想配列中のキーが指定した値以下の場合にそのキーに対応する値を返す
     *
     * @param   Array   $arr        連想配列
     * @param   Integer $search_val
     * @return  String
     */
    private static function filterValueForKeyLess($arr, $search_val) {
        ksort($arr);
        foreach ($arr as $key=>$val) {
            if ((int) $key >= (int) $search_val) {
                return $val;
            }
        }
    }
}