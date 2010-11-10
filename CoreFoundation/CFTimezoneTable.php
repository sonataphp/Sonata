<?
//
//  CFTimezoneTable.php
//  Sonata/CoreFoundation
//
//  Created by Roman Efimov on 6/10/2010.
//

function CFTimezoneTable() {
    return array(
        '-12:00,0' => 'Pacific/Fiji',
        '-12:00,0' => 'Pacific/Fiji',
        '-11:00,0' => 'Pacific/Samoa',
        '-10:00,0' => 'Pacific/Honolulu',
        '-09:00,1' => 'America/Anchorage',
        '-08:00,1' => 'America/Los_Angeles',
        '-07:00,0' => 'America/Phoenix',
        '-07:00,1' => 'America/Denver',
        '-06:00,0' => 'America/Winnipeg',
        '-06:00,1' => 'America/Chicago',
        '-05:00,0' => 'America/Indianapolis',
        '-05:00,1' => 'America/New_York',
        '-04:00,1' => 'America/Santiago',
        '-04:00,0' => 'America/Caracas',
        '-03:30,1' => 'America/St_Johns',
        '-03:00,1' => 'America/Thule',
        '-03:00,0' => 'America/Buenos_Aires',
        '-02:00,1' => 'Atlantic/South_Georgia',
        '-01:00,1' => 'Atlantic/Azores',
        '-01:00,0' => 'Atlantic/Cape_Verde',
        '00:00,0' => 'Africa/Casablanca',
        '00:00,1' => 'Europe/Dublin',
        '+01:00,1' => 'Europe/Amsterdam',
        '+01:00,0' => 'Africa/Lagos',
        '+02:00,1' => 'Europe/Athens',
        '+02:00,0' => 'Africa/Harare',
        '+03:00,1' => 'Asia/Baghdad',
        '+03:00,0' => 'Asia/Kuwait',
        '+03:30,0' => 'Europe/Moscow',
        '+04:00,0' => 'Asia/Dubai',
        '+04:00,1' => 'Asia/Baku',
        '+04:30,0' => 'Asia/Kabul',
        '+05:00,1' => 'Asia/Yekaterinburg',
        '+05:00,0' => 'Asia/Karachi',
        '+05:30,0' => 'Asia/Calcutta',
        '+05:45,0' => 'Asia/Katmandu',
        '+06:00,0' => 'Asia/Dhaka',
        '+06:00,1' => 'Asia/Almaty',
        '+06:30,0' => 'Asia/Rangoo',
        '+07:00,1' => 'Asia/Krasnoyarsk',
        '+07:00,0' => 'Asia/Bangkok',
        '+08:00,0' => 'Asia/Hong_Kong',
        '+08:00,1' => 'Asia/Irkutsk',
        '+09:00,1' => 'Asia/Yakutsk',
        '+09:00,0' => 'Asia/Tokyo',
        '+09:30,0' => 'Australia/Adelaide',
        '+09:30,1' => 'Australia/Adelaide',
        '+10:00,0' => 'Australia/Brisbane',
        '+10:00,1' => 'Australia/Sydney',
        '+11:00,0' => 'Asia/Magadan',
        '+12:00,1' => 'Pacific/Auckland',
        '+12:00,0' => 'Pacific/Fiji',
        '+13:00,0' => 'Pacific/Tongatapu');
}

function CFTimezoneName($timezone) {
    $timezones = CFTimezoneTable();
    return $timezones[trim($timezone)];
}

?>