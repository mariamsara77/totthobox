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
        // Escape input for XSS
        $text = e($text);

        // Step 1: full URLs with protocol or www.
        $urlPattern = '/
            (?<!href=")                   # Negative lookbehind to avoid double linking
            \b
            (
                (?:https?:\/\/|www\.)     # Protocol or www.
                [^\s<>"\'()]+             # Domain + path (no spaces, <, >, quotes, parentheses)
                [^\s<>"\'.,;:!?)]         # Last char not punctuation
            )
        /ix';

        $text = preg_replace_callback($urlPattern, function ($matches) {
            $url = $matches[1];

            // Add protocol if missing
            $href = preg_match('/^www\./i', $url) ? "http://$url" : $url;

            // Escape output
            $escapedUrl = e($url);
            $escapedHref = e($href);

            return "<a href=\"$escapedHref\" target=\"_blank\" rel=\"noopener noreferrer\" class=\"text-blue-500 underline hover:text-blue-700\">$escapedUrl</a>";
        }, $text);

        // Step 2: bare domains (without protocol or www.), avoid emails
        $domainPattern = '/
            (?<![@\/])                   # Negative lookbehind to avoid emails and urls
            \b
            (
                (?:[a-z0-9-]+\.)+        # Subdomains
                [a-z]{2,}                # TLD
            )
            \b
        /ix';

        $text = preg_replace_callback($domainPattern, function ($matches) {
            $domain = $matches[1];
            $href = "http://$domain";

            $escapedDomain = e($domain);
            $escapedHref = e($href);

            return "<a href=\"$escapedHref\" target=\"_blank\" rel=\"noopener noreferrer\" class=\"text-blue-500 underline hover:text-blue-700\">$escapedDomain</a>";
        }, $text);

        return $text;
    }
}
