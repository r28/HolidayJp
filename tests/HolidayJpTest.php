<?php

use r28\AstroTime\AstroTime;
use r28\HolidayJp\HolidayJp;

class HolidayJpTest extends PHPUnit\Framework\TestCase
{
    /**
     * 内閣府発表の祝日 (休日=>振替休日など修正)
     *  https://www8.cao.go.jp/chosei/shukujitsu/gaiyou.html
     * 
     * @var array
     */
    protected static $announcedHolidays = [
        2019 => [
            '2019/01/01' => '元日',
            '2019/01/14' => '成人の日',
            '2019/02/11' => '建国記念の日',
            '2019/03/21' => '春分の日',
            '2019/04/29' => '昭和の日',
            '2019/04/30' => '国民の休日',
            '2019/05/01' => '天皇即位の日',
            '2019/05/02' => '国民の休日',
            '2019/05/03' => '憲法記念日',
            '2019/05/04' => 'みどりの日',
            '2019/05/05' => 'こどもの日',
            '2019/05/06' => '振替休日',
            '2019/07/15' => '海の日',
            '2019/08/11' => '山の日',
            '2019/08/12' => '振替休日',
            '2019/09/16' => '敬老の日',
            '2019/09/23' => '秋分の日',
            '2019/10/14' => '体育の日',
            '2019/10/22' => '即位礼正殿の儀',
            '2019/11/03' => '文化の日',
            '2019/11/04' => '振替休日',
            '2019/11/23' => '勤労感謝の日',
        ],
        2020 => [
            '2020/01/01' => '元日',
            '2020/01/13' => '成人の日',
            '2020/02/11' => '建国記念の日',
            '2020/02/23' => '天皇誕生日',
            '2020/02/24' => '振替休日',
            '2020/03/20' => '春分の日',
            '2020/04/29' => '昭和の日',
            '2020/05/03' => '憲法記念日',
            '2020/05/04' => 'みどりの日',
            '2020/05/05' => 'こどもの日',
            '2020/05/06' => '振替休日',
            '2020/07/23' => '海の日',
            '2020/07/24' => 'スポーツの日',
            '2020/08/10' => '山の日',
            '2020/09/21' => '敬老の日',
            '2020/09/22' => '秋分の日',
            '2020/11/03' => '文化の日',
            '2020/11/23' => '勤労感謝の日',
        ],
    ];

    protected static $announcedHolidaysForItterate = [
        '2019/04/29' => '昭和の日',
        '2019/04/30' => '国民の休日',
        '2019/05/01' => '天皇即位の日',
        '2019/05/02' => '国民の休日',
        '2019/05/03' => '憲法記念日',
        '2019/05/04' => 'みどりの日',
        '2019/05/05' => 'こどもの日',
        '2019/05/06' => '振替休日',
        '2019/07/15' => '海の日',
        '2019/08/11' => '山の日',
        '2019/08/12' => '振替休日',
    ];

    protected static $target_date = [ 'year'=>'2019', 'month'=>'05', 'day'=>'06', 
                                      'timestamp'=>1557068400, 'julian'=>2458609.125 ];
    protected static $month_holidays = [
        '2019/05/01' => '天皇即位の日',
        '2019/05/02' => '国民の休日',
        '2019/05/03' => '憲法記念日',
        '2019/05/04' => 'みどりの日',
        '2019/05/05' => 'こどもの日',
        '2019/05/06' => '振替休日',
    ];

    static $date_string;
    static $announced_date;

    public function setUp() {
        date_default_timezone_set(HolidayJp::TIMEZONE_NAME);
        self::$date_string = self::$target_date['year']."/".self::$target_date['month']."/".self::$target_date['day'];
        self::$announced_date = self::$announcedHolidays[self::$target_date['year']][self::$date_string];
    }
    
    public function test_holidayName() {
        $date = self::$date_string;
        $time = new AstroTime($date." 00:00:00", HolidayJp::TIMEZONE_NAME, false);
        $holiday = new HolidayJp($date);
        $this->assertEquals(self::$announced_date, $holiday->holidayName());
    }

    public function test_nearestYears() {
        $years = array_keys(self::$announcedHolidays);
        $holidays = [];
        foreach($years as $year) {
            $holidays[$year] = HolidayJp::holidayNamesFromYear($year, 'date_slash', true);
        }
        $this->assertEquals(self::$announcedHolidays, $holidays);
    }

    public function test_yearMonth() {
        $year = self::$target_date['year'];
        $month = self::$target_date['month'];
        $this->assertEquals(self::$month_holidays, 
            HolidayJp::holidayNamesFromYearMonth($year, $month, 'date_slash', true));
    }

    public function test_holidayNameFromDate() {
        $date = self::$date_string;
        $this->assertEquals(self::$announced_date,
            HolidayJp::holidayNameFromDate($date));
    }

    public function test_holidayNameFromTimestamp() {
        $this->assertEquals(self::$announced_date, 
            HolidayJp::holidayNameFromTimestamp(self::$target_date['timestamp']));
    }

    public function test_holidayNameFromJulian() {
        $this->assertEquals(self::$announced_date, 
            HolidayJp::holidayNameFromJulian(self::$target_date['julian']));
    }

    public function test_itteratePeriodsFromDate() {
        $start = new AstroTime("2019/04/29");
        $end   = new AstroTime("2019/08/31");
        $this->assertEquals(self::$announcedHolidaysForItterate,
            HolidayJp::itteratePeriodsFromDate($start, $end, 'date_slash', true));
    }
}