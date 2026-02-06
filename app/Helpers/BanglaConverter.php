<?php

namespace App\Helpers;

class BanglaConverter
{
    /**
     * ইংরেজি সংখ্যা থেকে বাংলা সংখ্যা
     */
    public static function toBanglaNumber($number)
    {
        $english = ['0','1','2','3','4','5','6','7','8','9'];
        $bangla  = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
        return str_replace($english, $bangla, $number);
    }

    /**
     * বাংলা সংখ্যা থেকে ইংরেজি সংখ্যা
     */
    public static function toEnglishNumber($number)
    {
        $english = ['0','1','2','3','4','5','6','7','8','9'];
        $bangla  = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
        return str_replace($bangla, $english, $number);
    }

    /**
     * মাসের নাম কনভার্ট
     */
    public static function month($month)
    {
        $months = [
            'January' => 'জানুয়ারি', 'February' => 'ফেব্রুয়ারি',
            'March' => 'মার্চ', 'April' => 'এপ্রিল', 'May' => 'মে',
            'June' => 'জুন', 'July' => 'জুলাই', 'August' => 'আগস্ট',
            'September' => 'সেপ্টেম্বর', 'October' => 'অক্টোবর',
            'November' => 'নভেম্বর', 'December' => 'ডিসেম্বর',
        ];
        return $months[$month] ?? $month;
    }

    /**
     * দিনের নাম কনভার্ট
     */
    public static function day($day)
    {
        $days = [
            'Saturday' => 'শনিবার', 'Sunday' => 'রবিবার', 'Monday' => 'সোমবার',
            'Tuesday' => 'মঙ্গলবার', 'Wednesday' => 'বুধবার',
            'Thursday' => 'বৃহস্পতিবার', 'Friday' => 'শুক্রবার',
        ];
        return $days[$day] ?? $day;
    }

    /**
     * AM/PM কনভার্ট (ঘণ্টা সময়ের জন্য)
     */
    public static function period($period)
    {
        $map = [
            'AM' => 'পূর্বাহ্ন',
            'PM' => 'অপরাহ্ন',
            'am' => 'পূর্বাহ্ন',
            'pm' => 'অপরাহ্ন',
        ];
        return $map[$period] ?? $period;
    }

    /**
     * সময় ফরম্যাট কনভার্ট (যেমন "05:30 PM" -> "০৫:৩০ অপরাহ্ন")
     */
    public static function time($timeString)
    {
        if (!$timeString) return '';
        $timeString = self::toBanglaNumber($timeString);
        $timeString = str_replace(['AM','PM','am','pm'], ['পূর্বাহ্ন','অপরাহ্ন','পূর্বাহ্ন','অপরাহ্ন'], $timeString);
        return $timeString;
    }

    /**
     * পুরো DateTime কনভার্ট (যেমন "Saturday, 09 November 2025, 05:30 PM")
     */
    public static function fullDateTime($datetime)
    {
        if (!$datetime) return '';
        $eng = [
            'January','February','March','April','May','June','July','August','September','October','November','December',
            'Saturday','Sunday','Monday','Tuesday','Wednesday','Thursday','Friday',
            'AM','PM','am','pm'
        ];
        $bn = [
            'জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর',
            'শনিবার','রবিবার','সোমবার','মঙ্গলবার','বুধবার','বৃহস্পতিবার','শুক্রবার',
            'পূর্বাহ্ন','অপরাহ্ন','পূর্বাহ্ন','অপরাহ্ন'
        ];

        $converted = str_replace($eng, $bn, $datetime);
        return self::toBanglaNumber($converted);
    }

    /**
     * শুধু সংখ্যা, মাস, দিন, সময়সহ সবকিছু অটো কনভার্ট
     */
    public static function auto($string)
    {
        return self::fullDateTime($string);
    }


    public static function diffForHumansBangla($carbonInstance)
{
    if (!$carbonInstance) return '';

    // Laravel এর diffForHumans ইংরেজিতে দিবে
    $text = $carbonInstance->diffForHumans();

    // ইংরেজি শব্দগুলো বাংলায় ম্যাপ করা
    $eng = [
        'seconds ago', 'second ago',
        'minutes ago', 'minute ago',
        'hours ago', 'hour ago',
        'days ago', 'day ago',
        'weeks ago', 'week ago',
        'months ago', 'month ago',
        'years ago', 'year ago',
        'from now', 'ago', 'before', 'after'
    ];

    $bn = [
        'সেকেন্ড আগে', 'সেকেন্ড আগে',
        'মিনিট আগে', 'মিনিট আগে',
        'ঘণ্টা আগে', 'ঘণ্টা আগে',
        'দিন আগে', 'দিন আগে',
        'সপ্তাহ আগে', 'সপ্তাহ আগে',
        'মাস আগে', 'মাস আগে',
        'বছর আগে', 'বছর আগে',
        'পরে', 'আগে', 'আগে', 'পরে'
    ];

    // নাম্বার ও টেক্সট দুটোই ট্রান্সলেট
    $converted = str_replace($eng, $bn, $text);
    return self::toBanglaNumber($converted);
}

}
