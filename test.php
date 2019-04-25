<?php
require_once './vendor/autoload.php';
use r28\HolidayJp\HolidayJp;

$date = null;
$holiday_name = HolidayJp::holidayNameFromDate($date);
echo $holiday_name.PHP_EOL;

