<?php
namespace r28\HolidayJp\libs;
use r28\AstroTime\AstroTime;

if (! defined('BASE_DIR')) {
    define('BASE_DIR', dirname(__FILE__).'/../');
}

/**
 * 指定期間の祝日をCSVファイルに出力
 * 
 * @property string     $export_dir     出力先ディレクトリ
 * @property string     $export_file    出力先CSVファイル名
 * @property array      $colnames       出力カラム
 */
class ExportCsv
{
    /**
     * Export directory path
     * @var string
     */
    public $export_dir = BASE_DIR.'/../exports';

    /**
     * Export file name
     * @var string
     */
    public $export_file = 'holidays.csv';

    public $colnames = [ 'datetime', 'timestamp', 'name' ];


    /**
     * Constructor
     * 
     * @param   string  $export_dir     CSV export directory path
     */
    public function __construct($export_dir=null, $export_file=null) {
        if (! is_null($export_dir)) {
            $this->setExportDir($export_dir);
        }
        if (! is_null($export_file)) {
            $this->setExportFileName($export_file);
        }
    }

    /**
     * Set csv export directory
     * 
     * @param   string  $export_dir     CSV export directory path
     * @return  ExportCsv
     */
    public function setExportDir($export_dir) {
        if (static::checkDir($export_dir)) {
            $this->export_dir = $export_dir;
        }
        return $this;
    }

    /**
     * Set csv export filename
     * 
     * @param   string  $name       CSV export file name
     * @return  ExportCsv
     */
    public function setExportFileName($name) {
        $this->export_file = $name;
        return $this;
    }

    /**
     * Export Holidays to CSV
     * 
     * @param   array   $holidays   Holidays array
     */
    public function exportHolidays($holidays) {
        $fp = static::openFile($this->export_dir, $this->export_file);

        $str = implode(",", $this->colnames).PHP_EOL;
        foreach ($holidays as $name=>$holiday) {
            $str .= implode(",", static::bindColumn($holiday)).PHP_EOL;
        }
        fwrite($fp, trim($str));
        fclose($fp);
    }


    /**
     * Check and Make directory
     * 
     * @param   string  $dir    Target directory
     * @return  boolean
     */
    private static function checkDir($dir, $mode=0755) {
        if (! is_dir($dir)) {
            if (! mkdir($dir, $mode, true)) {
                throw new Exception("Cannot make directory: {$dir}");
                return false;
            }
        }
        return true;
    }

    /**
     * Open export csv file
     * 
     * @param   string  $dir    Export directory
     * @param   string  $name   Export file Name
     * @return  mixed   Resource id | boolean(false)
     */
    private static function openFile($dir, $name) {
        $dir = (substr($dir, -1) !== DIRECTORY_SEPARATOR) ? "{$dir}/" : $dir;
        $path = "{$dir}{$name}";
        $fp = @fopen($path, 'w');
        if (! $fp) {
            throw new Exception("Cannot open export file: {$path}");
            return false;
        }
        return $fp;
    }

    private static function bindColumn($holiday) {
        return [
            'datetime' => '"'.$holiday['time']->local->format('Y-m-d').'"',
            'timestamp' => $holiday['time']->timestamp,
            'name' => $holiday['name'],
        ];
    }
}