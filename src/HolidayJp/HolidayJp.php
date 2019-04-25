<?php
/**
 * Japanese Holiday judgement library
 *  Copyright (c) r28 (https://redmagic.cc)
 *
 * @require r28/AstroTime       : https://github.com/r28/AstroTime
 * @require laktak/hjson        : https://packagist.org/packages/laktak/hjson
 * @require settings/*.hjson    : Setting files for any holidays
 * 
 * @using Simple arithmetic expression for Equinox:
 *  http://addinbox.sakura.ne.jp/holiday_topic.htm#syunbun
 * 
 *  Licensed under The MIT License
 *  Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) r28 (https://redmagic.cc)
 * @link          https://redmagic.cc Redmagic
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */


namespace r28\HolidayJp;

set_include_path(get_include_path().':'.dirname(__FILE__).'/libs/');
set_include_path(get_include_path().':'.dirname(__FILE__).'/settings/');

use r28\AstroTime\AstroTime;
require_once 'libs/StationalyHoliday.php';
require_once 'libs/HappyMonday.php';
require_once 'libs/SpecifiedMoved.php';
require_once 'libs/Equinox.php';

/**
 * 日本の祝日判定
 * 
 * @require r28\AstroTime
 */
class HolidayJp
{
    /**
     * TimezoneName (日本が前提)
     * @constant
     */
    const TIMEZONE_NAME = 'Asia/Tokyo';

    /**
     * 休日の名称
     * @constant
     */
    const MONDAY_MAKEUP_HOLIDAY_NAME = '振替休日';
    const NATIONAL_HOLIDAY_NAME = '国民の休日';

    /**
     * 祝日法改正 => 振替休日制の施行日
     * @constant
     */
    const MONDAY_MAKEUP_HOLIDAY_START = '1973-04-12';

    /**
     * 祝日法改正 => 2つの祝日に挟まれた平日を休日とする国民の休日の施行日
     * @constant
     */
    const NATIONAL_HOLIDAY_START = '1985-12-27';

    /**
     * 対象日付AstroTimeオブジェクト
     * @var AstroTime
     */
    public $date;

    /**
     * 対象日付文字列
     * @var string
     */
    public $date_tring;

   /**
    * 固定の休日
    * @var array
    */
    static $stationaly_holidays = [];

    /**
     * ハッピーマンデー
     * @var array
     */
    static $happy_mondays = [];

    /**
     * 当該年のみ、特別立法等である祝日を別の日付に移す場合
     * 'original' は元の祝日の日付
     * @var array
     */
    static $specified_moved = [];

    /**
     * 特別に当該日が当該年のみ祝日でなくなったフラグ
     * @var boolean
     */
    //private $is_specified_removed = false;

    /**
     * 春分日簡易計算用定数
     *  - キー の 西暦年 は、当該期間最後の年 (この年以下の年が当該の定数を適用される)
     *
     * @var array   [ '西暦年' => '定数' ]
     */
    static $vernal_consts = [];

    /**
     * 秋分日簡易計算用定数
     * @var array
     */
    static $autumnal_consts = [];

    public $params = [
        'stationaly_holiday',
        'happy_monday',
        'equinox_holiday',
        'specified_moved',
    ];

    /**
     * Constructor
     * 
     * @param   AstroTime|string    $date   AstroTimeオブジェクト または 日付文字列
     */
    public function __construct($date=null) {
        date_default_timezone_set(static::TIMEZONE_NAME);
        if(! empty($date)) {
            $this->setDate($date);
        }

        $this->setParams();
    }

    /**
     * 対象日付セット
     * 
     * @param   AstroTime|string    $date   AstroTimeオブジェクト または 日付文字列
     * @return  HolidayJp
     */
    public function setDate($date) {
        $dt = (gettype($date) == 'object') ? $date : new AstroTime($date, static::TIMEZONE_NAME);
        $this->date = $dt;
        return $this;
    }

    /**
     * 各祝日種別用設定読込
     * 
     * @return  HolidayJp
     */
    public function setParams() {
        $this->params = [
            'stationaly_holiday'    => libs\StationalyHoliday::parseSetting(),
            'happy_monday'          => libs\HappyMonday::parseSetting(),
            'equinox_holiday'       => libs\Equinox::parseSetting(),
            'specified_moved'       => libs\SpecifiedMoved::parseSetting(),
        ];
        return $this;
    }


    /**
     * 祝日判定
     *
     * @param   AstroTime       対象日付のAstroTimeオブジェクト
     * @throws  Exception       $dateが指定されていない場合
     * @return  string|boolean  祝日名 (祝日でない場合: false)
     */
    public function holidayName(AstroTime $date=null) {
        if (is_null($date)) {
            if (! is_null($this->date)) {
                $date = $this->date;
            } else {
                throw new \Exception("Date: AstroTime is not granted");
            }
        }

        $holiday = false;
        $is_specified_removed = false;

        // 特別措置法等により当該年のみの措置がある場合
        $holiday = self::specifiedMovedHoliday($date, $this->params['specified_moved']);
        if ($holiday === libs\SpecifiedMoved::IS_REMOVED_TYPE) {
            $is_specified_removed = true;
            $holiday = false;
        }
        if ($holiday && ! $is_specified_removed) {
            return $holiday;
        }

        if (! $is_specified_removed) {
            // 固定/春秋分/ハッピーマンデー判定
            // (特別措置法によって当該年のみ他の日に移動した場合は除く)
            $holiday = self::stationalyHoliday($date, $this->params['stationaly_holiday']);
            if (! $holiday) {
                $holiday = self::equinoxHoliday($date, $this->params['equinox_holiday']);
            }
            if (! $holiday) {
                $holiday = self::happyMonday($date, $this->params['happy_monday']);
            }
        }

        if (! $holiday) {
            // 振替休日判定
            $holiday = (self::isMondayMakeupHoliday($date, $this->params['stationaly_holiday'])) 
                ? self::MONDAY_MAKEUP_HOLIDAY_NAME : false;
        }

        if (! $holiday) {
            // 国民の休日判定
            $holiday = (self::isNationalHoliday($date, $this->params['stationaly_holiday']))
                ? self::NATIONAL_HOLIDAY_NAME : false;
        }
        return $holiday;
    }

    /**
     *  当該年のみ、特別立法等である祝日を別の日付に移す場合
     *  'original' は元の祝日の日付
     *
     * @param   AstroTime   $date   判定対象日時(AstroTime Object)
     * @param   object      $params 設定
     * @return  string|boolean  祝日名
     */
    public static function specifiedMovedHoliday(AstroTime $date, $params=null) {
        $holiday = libs\SpecifiedMoved::isMatched($date, $params);
        return $holiday;
    }

    /**
     * 固定休日判定
     *
     * @param   AstroTime       $date   判定対象日時(AstroTime Object)
     * @param   object          $params 設定
     * @return  string|boolean  休日名
     */
    public static function stationalyHoliday(AstroTime $date, $params=null) {
        $holiday = libs\StationalyHoliday::isMatched($date, $params);
        return $holiday;
    }

    /**
     * 春分日/秋分日判定
     *
     * @param   AstroTime       $date   判定対象日時(AstroTime Object)
     * @param   object          $params 設定
     * @return  string|boolean  休日名
     */
    public static function equinoxHoliday(AstroTime $date, $params=null) {
        $holiday = libs\Equinox::isMatched($date, $params);
        return $holiday;
    }

    /**
     * ハッピーマンデー判定
     * 
     * @param   AstroTime       $date   判定対象日時(AstroTime Object)
     * @param   object          $params 設定
     * @return  string|boolean  休日名
     */
    public static function happyMonday(AstroTime $date, $params) {
        $holiday = libs\HappyMonday::isMatched($date, $params);
        return $holiday;
    }

    /**
     * 振替休日判定
     *  - 日曜が祝日の場合、翌日の月曜日以降の国民の祝日でない祝日の翌日
     *
     * @param   AstroTime   $date   判定対象日時(AstroTime Object)
     * @param   object      $stationaly_params 設定
     * @param   boolean     $is_check_holiday   当日が祝日かどうか判定するか?
     * @return  boolean
     */
    public static function isMondayMakeupHoliday(AstroTime $date, $stationaly_params=null, $is_check_holiday=false) {
        // 施行日より前
        $enforce = new AstroTime(self::MONDAY_MAKEUP_HOLIDAY_START." 00:00:00", static::TIMEZONE_NAME);
        if ($date < $enforce) return false;

        if ($is_check_holiday && self::stationalyHoliday($date, $stationaly_params)) return false;

        $y = $date->year;
        $dt = clone $date;

        // 2007年改正法 => 7日間遡る
        $n = ($y < 2007) ? 1 : 7;
        $dt = $dt->subDay(1);
        for ($i=0; $i<$n; $i++) {
            // 祝日かつ日曜の場合は振替休日
            $holiday = self::stationalyHoliday($dt, $stationaly_params);
            if ($holiday && in_array($dt->dayOfWeek, [0,7])) return true;
            if (! $holiday) break;
            $dt = $dt->subDay(1);
        }
        return false;
    }

    /**
     * 国民の休日判定
     *  - 前後が祝日(振替休日は含まない)に挟まれている場合は国民の休日
     *
     * @param   AstroTime   $date   判定対象日時(AstroTime Object)
     * @param   object      $params 設定
     * @return  boolean
     */
    public static function isNationalHoliday(AstroTime $date, $stationaly_params=null) {
        // 施行日より前
        if ($date < self::NATIONAL_HOLIDAY_START) return false;

        $prev = $date->subDay(1);
        $next = $date->addDay(1);

        if (self::stationalyHoliday($prev, $stationaly_params) && self::stationalyHoliday($next, $stationaly_params)) return true;
        return false;
    }

    /**
     * 祝日判定 (日付文字列指定)
     * 
     * @param   string  $date_string    対象日付
     * @return  string|boolean          祝日名
     */
    public static function holidayNameFromDate($date_string) {
        $hd   = new HolidayJp($date_string);
        return $hd->holidayName();
    }

    /**
     * 祝日判定 (Unix Timestamp指定)
     * 
     * @param   integer         $timestamp  Unix Timestamp
     * @return  string|boolean  祝日名
     */
    public static function holidayNameFromTimestamp($timestamp) {
        $date = AstroTime::createFromTimestamp($timestamp, static::TIMEZONE_NAME, false);
        $hd   = new HolidayJp($date);
        return $hd->holidayName();
    }

    /**
     * 祝日判定 (ユリウス日指定)
     * 
     * @param   float   $jd         ユリウス日
     * @return  string|boolean      祝日名
     */
    public static function holidayNameFromJulian($jd) {
        $date = AstroTime::createFromJulian($jd, static::TIMEZONE_NAME, false);
        $hd   = new HolidayJp($date);
        return $hd->holidayName();
    }

    /**
     * 祝日一覧 (年指定)
     * 
     * @param   integer     $year   指定年 (null: 実行日の年)
     * @param   string      $key    返却される一覧のキー:
     *                              - date_string: 年月日 (Y-m-d)
     *                              - date_short : 年月日 (Ymd)
     *                              - jd         : ユリウス日 (但し、PHPの配列のキーはIntegerに丸められることに注意)
     *                              - timestamp  : Unix Timestamp
     * @param   boolean     $is_only_holiday    true: 祝日の日のみのarrayを返却する
     * 
     * @return  array
     */
    public static function holidayNamesFromYear($year=null, $key='date_string', $is_only_holiday=false) {
        if (is_null($year) || ! is_numeric($year)) {
            $time = AstroTime::create(null, null, null, null, null, null, static::TIMEZONE_NAME, false);
            $start = $time->startOfYear();
            $end   = $time->endOfYear();
        } else {
            $start = new AstroTime("{$year}-01-01", static::TIMEZONE_NAME, false);
            $end   = new AstroTime("{$year}-12-31", static::TIMEZONE_NAME, false);
        }

        return self::itteratePeriodsFromDate($start, $end, $key, $is_only_holiday);
    }

    /**
     * 祝日一覧 (月指定)
     * 
     * @param   integer     $year   指定年 (null: 実行日の年)
     * @param   integer     $month  指定月 (null: 実行日の月)
     * @param   string      $key    返却される一覧のキー:
     *                              - date_string: 年月日 (Y-m-d)
     *                              - date_slash : 年月日 (Y/m/d)
     *                              - date_short : 年月日 (Ymd)
     *                              - jd         : ユリウス日 (但し、PHPの配列のキーはIntegerに丸められることに注意)
     *                              - timestamp  : Unix Timestamp
     * @param   boolean     $is_only_holiday    true: 祝日の日のみのarrayを返却する
     * 
     * @return  array
     */
    public static function holidayNamesFromYearMonth($year=null, $month=null, $key='date_string', $is_only_holiday=false) {
        if (is_null($year) || is_null($month) || ! is_numeric($year) || ! is_numeric($month)) {
            $time = AstroTime::create(null, null, null, null, null, null, static::TIMEZONE_NAME, false);
            $start = $time->startOfMonth();
            $end   = $time->endOfMonth();
        } else {
            $start = new AstroTime("{$year}-{$month}-01", static::TIMEZONE_NAME, false);
            $end   = $start->endOfMonth();
        }

        return self::itteratePeriodsFromDate($start, $end, $key, $is_only_holiday);
    }

    /**
     * 指定期間(開始日=>終了日)について毎日の祝日判定、指定キーの一覧を返却
     * 
     * @param   AstroTime|string    $start  期間開始日のAstroTimeオブジェクト (または日付文字列)
     * @param   AstroTime|string    $end    期間終了日のAstroTimeオブジェクト (または日付文字列)
     * @param   string              $key    返却される一覧のキー:
     * @param   boolean             $is_only_holiday    true: 祝日の日のみのarrayを返却する
     * @return  array
     */
    public static function itteratePeriodsFromDate($start, $end, $key, $is_only_holiday=false) {
        if (gettype($start)=='string') {
            $start = new AstroTime($start, static::TIMEZONE_NAME, false);
        }
        if (gettype($end)=='string') {
            $end = new AstroTime($end, static::TIMEZONE_NAME, false);
        }

        $hd = new HolidayJp;
        $date = $start;
        $holidays = [];

        while($date->lte($end)) {
            $holiday = $hd->holidayName($date);
            $_key = static::dateTimeForKey($date, $key);

            if (! $is_only_holiday || $holiday) {
                $holidays[$_key] = $holiday;
            }

            // Add 1 day
            $date = $date->addDays(1);
        }
        return $holidays;
    }

    /**
     * 祝日一覧の配列用のキー変換
     * 
     * @param   AstroTime   $date   日付オブジェクト
     * @param   string      $key    キー文字列
     * @return  float|integer|string
     */
    private static function dateTimeForKey(AstroTime $date, $key=null) {
        if ($key == 'jd' || $key == 'julian') {
            // Julian day
            $res = $date->jd;

        } else if ($key == 'timestamp') {
            // Unix Timestamp
            $res = $date->timestamp;

        } else if ($key == 'date_short') {
            // Date (Ymd)
            $res = $date->format('Ymd');

        } else if ($key == 'date_slash') {
            $res = $date->format('Y/m/d');
        } else {
            // Date (Y-m-d)
            $res = $date->format('Y-m-d');
        }

        return $res;
    }
}
