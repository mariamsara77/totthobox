<?php

use App\Helpers\BanglaConverter;

function bn_num($eng_date)
{
    $eng = [
        '0',
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December',
        'Saturday',
        'Sunday',
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
    ];
    $bn = [
        '০',
        '১',
        '২',
        '৩',
        '৪',
        '৫',
        '৬',
        '৭',
        '৮',
        '৯',
        'জানুয়ারি',
        'ফেব্রুয়ারি',
        'মার্চ',
        'এপ্রিল',
        'মে',
        'জুন',
        'জুলাই',
        'আগস্ট',
        'সেপ্টেম্বর',
        'অক্টোবর',
        'নভেম্বর',
        'ডিসেম্বর',
        'শনিবার',
        'রবিবার',
        'সোমবার',
        'মঙ্গলবার',
        'বুধবার',
        'বৃহস্পতিবার',
        'শুক্রবার',
    ];

    return str_replace($eng, $bn, $eng_date);
}
function bn_date($eng_date)
{
    $eng = [
        '0',
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December',
        'Saturday',
        'Sunday',
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Jan',
        'Feb',
        'Mar',
        'Apr',
        'May',
        'Jun',
        'Jul',
        'Aug',
        'Sep',
        'Oct',
        'Nov',
        'Dec',
        'Sat',
        'Sun',
        'Mon',
        'Tue',
        'Wed',
        'Thu',
        'Fri',
        'AM',
        'PM',
    ];
    $bn = [
        '০',
        '১',
        '২',
        '৩',
        '৪',
        '৫',
        '৬',
        '৭',
        '৮',
        '৯',
        'জানুয়ারি',
        'ফেব্রুয়ারি',
        'মার্চ',
        'এপ্রিল',
        'মে',
        'জুন',
        'জুলাই',
        'আগস্ট',
        'সেপ্টেম্বর',
        'অক্টোবর',
        'নভেম্বর',
        'ডিসেম্বর',
        'শনিবার',
        'রবিবার',
        'সোমবার',
        'মঙ্গলবার',
        'বুধবার',
        'বৃহস্পতিবার',
        'শুক্রবার',
        'জানুয়ারি',
        'ফেব্রুয়ারি',
        'মার্চ',
        'এপ্রিল',
        'মে',
        'জুন',
        'জুলাই',
        'আগস্ট',
        'সেপ্টেম্বর',
        'অক্টোবর',
        'নভেম্বর',
        'ডিসেম্বর',
        'শনিবার',
        'রবিবার',
        'সোমবার',
        'মঙ্গলবার',
        'বুধবার',
        'বৃহস্পতিবার',
        'শুক্রবার',
        'সকাল',
        'বিকেল',
    ];

    return str_replace($eng, $bn, $eng_date);
}

function bn_month($month)
{
    $months = [
        'January' => 'জানুয়ারি',
        'February' => 'ফেব্রুয়ারি',
        'March' => 'মার্চ',
        'April' => 'এপ্রিল',
        'May' => 'মে',
        'June' => 'জুন',
        'July' => 'জুলাই',
        'August' => 'আগস্ট',
        'September' => 'সেপ্টেম্বর',
        'October' => 'অক্টোবর',
        'November' => 'নভেম্বর',
        'December' => 'ডিসেম্বর',
    ];

    return $months[$month] ?? $month;
}

function bn_day($day)
{
    $days = [
        'Saturday' => 'শনিবার',
        'Sunday' => 'রবিবার',
        'Monday' => 'সোমবার',
        'Tuesday' => 'মঙ্গলবার',
        'Wednesday' => 'বুধবার',
        'Thursday' => 'বৃহস্পতিবার',
        'Friday' => 'শুক্রবার',
    ];

    return $days[$day] ?? $day;
}

if (! function_exists('bn_diff_for_humans')) {
    function bn_diff_for_humans($datetime)
    {
        return BanglaConverter::diffForHumansBangla($datetime);
    }
}

if (! function_exists('linkify')) {
    function linkify(string $text): string
    {
        // ১. আগে থেকে থাকা HTML <a> ট্যাগগুলোকে <flux:link> এ কনভার্ট করা
        $text = preg_replace_callback('/<a\s+(?:[^>]*?\s+)?href="([^"]*)"[^>]*>(.*?)<\/a>/is', function ($matches) {
            $href = $matches[1];
            $content = $matches[2];

            // লিংকে http/https না থাকলে যুক্ত করা (যেমন: href="totthobox.com")
            if (! preg_match('~^(?:f|ht)tps?://~i', $href) && ! str_starts_with($href, '/')) {
                $href = 'https://'.ltrim($href, '/');
            }

            return sprintf('<flux:link href="%s" target="_blank">%s</flux:link>', $href, $content);
        }, $text);

        // ২. প্লেইন টেক্সট URL (যা কোনো ট্যাগের ভেতর নেই) সেগুলোকে <flux:link> এ কনভার্ট করা
        $urlPattern = '/(?<!href="|">|src=")\b(?:https?:\/\/|www\.)[^\s<>"\'()]+/ix';
        $text = preg_replace_callback($urlPattern, function ($matches) {
            $url = $matches[0];
            $href = preg_match('/^www\./i', $url) ? "https://$url" : $url;

            return sprintf('<flux:link href="%s" target="_blank">%s</flux:link>', $href, $url);
        }, $text);

        return $text;
    }
}
