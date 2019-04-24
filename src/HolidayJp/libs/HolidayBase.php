<?php
namespace r28\HolidayJp\libs;

use r28\AstroTime\AstroTime;
use HJSON\HJSONParser;
use HJSON\HJSONStringifier;

/**
 * 祝日判定ベース
 * 
 */
class HolidayBase
{
    /**
     * ファイル名ベース
     * @var string
     */
    protected static $name = null;

    /**
     * 指定日付(AstroTimeオブジェクト)
     * @var AstroTime
     */
    public $date;

    /**
     * 指定日付(UnixTime形式)
     * @var integer
     */
    public $unix_time;

    /**
     * 指定日付(ユリウス日形式)
     * @var float
     */
    public $jd;

    /**
     * 祝日判定結果
     * @var string|boolean
     */
    public $is_matched = false;

    /**
     * 取込用CSVディレクトリ名
     */
    public $import_dir = 'imports/';

    /**
     * 取込用CSVカラム
     */
    static $import_columns = [];


    /**
     * Constructor
     * 
     * @param   AstroTime|float|integer     $dt
     */
    public function __construct($dt) {
        if (is_int($dt)) {
            $this->unix_time = $dt;
            $this->date = new AstroTime(['timestamp'=>$dt]);

        } else if (is_float($dt)) {
            $this->jd = $dt;
            $this->date = AstroTime::julian2Local($jd, 'Asia/Tokyo');

        } else if (is_object($dt)) {
            $this->date = $dt;

        } else {
            throw new Exception("Date is not correct");
        }

        $this->is_matched = static::isMatched($this->date);
    }

    /**
     * 指定の日付が固定の祝日にマッチするか
     * 
     * @param   AstroTime       $date   指定日付オブジェクト
     * @return  string|boolean  祝日: 祝日名,  それ以外: false
     */
    //public static function isMatched(AstroTime $date) {
    //}

    /**
     * 設定ファイル読込
     * 
     * @return Object
     */
    public static function parseSetting() {
        $path = static::$name . '.hjson';
        $text = file_get_contents($path, true);
        if (empty($text)) {
            throw new Exception("HJson file open failure: {$path}");
            return false;
        }

        $parser = new HJSONParser();
        $data = $parser->parse($text);
        return $data;
    }

    /**
     * 設定CSV読み込み
     *  - HJSONに変換する元のCSVファイル
     *  - imports/{$name}.csv
     */
    public static function parseCsv() {
        $path = dirname(__FILE__).'/../imports/'.static::$name.'.csv';
        if (! file_exists($path)) return false;

        $file = new \SplFileObject($path, 'r');
        $file->setFlags(
            \SplFileObject::READ_CSV |
            \SplFileObject::SKIP_EMPTY |
            \SplFileObject::READ_AHEAD
        );

        $records = [];
        foreach($file as $i=>$row) {
            if ($i === 0) {
                static::$import_columns = $row;
                continue;
            }
            $line = [];
            foreach($row as $j=>$dat) {
                $line[static::$import_columns[$j]] = $dat;
            }
            $records[] = (object) $line;
        }

        return $records;
    }
}