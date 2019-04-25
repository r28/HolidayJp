# HolidayJp
[![Build Status](https://travis-ci.org/r28/HolidayJp.svg?branch=master)](https://travis-ci.org/r28/HolidayJp)

Japanese Holiday judgement library
日本の祝日を判定するライブラリ。
日付を与えると

- 祝日の場合    : 祝日名
- 祝日でない場合: false

を返す。

# Requirements
HolidayJp requires the following to run:
- PHP > 7.1
- composer
- r28/AstroTime
- laktak/hjson

# Install
Use composer:
```bash
$ composer require r28/holiday-jp
```

# Usage
```php
<?php
require_once path_to_vendor.'/vendor/autoload.php';
use r28\HolidayJp\HolidayJp;
```

- 日付を指定してインスタンスを生成した後に祝日判定 [holidayName()]
    ```php
    $date = '2019-05-01';
    $holiday = new HolidayJp($date);
    $holiday_name = $holiday->holidayName();
    echo $holiday_name.PHP_EOL;
    # 天皇即位の日
    ```

- インスタンスを生成後に日付を指定して祝日判定
    ```php
    $date = '2019-05-03';
    $time = new AstroTime($date, 'Asia/Tokyo', false);
    $holiday = new HolidayJp();
    $holiday_name = $holiday->holidayName($time);
    echo $holiday_name.PHP_EOL;
    # 憲法記念日
    ```

- Staticに 日付文字列 指定で祝日判定 [holidayNameFromDate()]
    ```php
    $holiday_name = HolidayJp::holidayNameFromDate('2019-05-04');
    echo $holiday_name.PHP_EOL;
    # みどりの日
    ```

- Staticに UnixTimestamp 指定で祝日判定 [holidayNameFromTimestamp()]
    ```php
    $holiday_name = HolidayJp::holidayNameFromTimestamp(1556982000); # 2019/05/05
    echo $holiday_name.PHP_EOL;
    # こどもの日
    ```

- Staticに ユリウス日 指定で祝日判定 [holidayNameFromJulian()]
    ```php
    $holiday_name = HolidayJp::holidayNameFromJulian(2458609.125);  # 2019/05/06
    echo $holiday_name.PHP_EOL;
    # 振替休日
    ```

- Staticに 年 を指定で1年間の祝日を Array で取得 [holidayNamesFromYear()]
    ```php
    $holidays = HolidayJp::holidayNamesFromYear(2019, 'date_string', true);
    print_r($holidays);

    Array
    (
        [2019-01-01] => 元日
        [2019-01-14] => 成人の日
        [2019-02-11] => 建国記念の日
        ....
        [2019-11-03] => 文化の日
        [2019-11-04] => 振替休日
        [2019-11-23] => 勤労感謝の日
    )
    ```

    - 第1引数 : 年(integer)
    - 第2引数 : 返却される Array の Key の形式
        - date_string : Y-m-d (string)
        - date_slash  : Y/m/d
        - date_short  : Ymd
        - timestamp   : Unix Timestamp (integer)
        - julian      : ユリウス日 (float)
    - 第3引数: true = 指定期間中の祝日のみを返却する, false = 指定期間全て(祝日でない場合の value は false)

- Staticに 年月 を指定で1か月の祝日を Array で取得 [holidayNamesFromYearMonth()]
    ```php
    $holidays = HolidayJp::holidayNamesFromYearMonth(2019, 5, 'date_string', true);
    print_r($holidays);

    Array
    (
        [2019-05-01] => 天皇即位の日
        [2019-05-02] => 国民の休日
        [2019-05-03] => 憲法記念日
        [2019-05-04] => みどりの日
        [2019-05-05] => こどもの日
        [2019-05-06] => 振替休日
    )
    ```

    - 第1引数 : 年(integer)
    - 第2引数 : 月(integer)
    - 第3引数以降は holidayNamesFromYear() の第2引数以降と同様

- Staticに 開始日, 終了日 を日付文字列で指定して指定期間の祝日を Array で取得 [itteratePeriodsFromDate() ]
    ```php
    $holidays = HolidayJp::itteratePeriodsFromDate('2019-01-01', '2019-04-01', 'date_string', true);
    print_r($holidays);

    Array
    (
        [2019-01-01] => 元日
        [2019-01-14] => 成人の日
        [2019-02-11] => 建国記念の日
        [2019-03-21] => 春分の日
    )
    ```
    - 第1引数 : 開始日(string)
    - 第2引数 : 終了日(string)
    - 第3引数以降は holidayNamesFromYear() の第2引数以降と同様

# 対応祝日
- 2019年用の「天皇の即位の日及び即位礼正殿の儀の行われる日を休日とする法律」に対応済
- 2020年用の「平成三十二年東京オリンピック競技大会・東京パラリンピック競技大会特別措置法及び 平成三十一年ラグビーワールドカップ大会特別措置法の一部を改正する法律 (平成30年法律第55号)」に対応済
- 「国民の祝日に関する法律の一部を改正する法律 (平成30年法律第57号)」に対応 (体育の日→スポーツの日)

## 祝日設定
祝日の設定は、`src/HolidayJp/settings/*.hjson` に HJSONファイル の形式で設置してある。

- Equinox.hjson
  春分・秋分の日 の計算用定数
- Happy_Mondays.hjson
  ハッピーマンデー制度で定められている祝日
- Specified_Moved.hjson
  2020年のオリンピック関連特別措置法のように、「ある年だけ別の日に移動し、前後は変更ない」ような祝日
- Stationaly_Holidays.hjson
  上記以外の通常の祝日 

```
これの編集スクリプトなどはそのうち...
```
