<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => ':attribute অবশ্যই গ্রহণ করতে হবে।',
    'accepted_if'          => 'যখন :other এর মান :value হয়, তখন :attribute অবশ্যই গ্রহণ করতে হবে।',
    'active_url'           => ':attribute একটি সঠিক ইউআরএল (URL) নয়।',
    'after'                => ':attribute অবশ্যই :date এর পরের একটি তারিখ হতে হবে।',
    'after_or_equal'       => ':attribute অবশ্যই :date এর সমান অথবা পরের একটি তারিখ হতে হবে।',
    'alpha'                => ':attribute শুধুমাত্র বর্ণমালা সম্বলিত হতে হবে।',
    'alpha_dash'           => ':attribute শুধুমাত্র বর্ণমালা, সংখ্যা, ড্যাশ এবং আন্ডারস্কোর সম্বলিত হতে হবে।',
    'alpha_num'            => ':attribute শুধুমাত্র বর্ণমালা এবং সংখ্যা সম্বলিত হতে হবে।',
    'array'                => ':attribute অবশ্যই একটি অ্যারে (Array) হতে হবে।',
    'before'               => ':attribute অবশ্যই :date এর আগের একটি তারিখ হতে হবে।',
    'before_or_equal'      => ':attribute অবশ্যই :date এর সমান অথবা আগের একটি তারিখ হতে হবে।',
    'between'              => [
        'numeric' => ':attribute অবশ্যই :min এবং :max এর মধ্যে হতে হবে।',
        'file'    => ':attribute অবশ্যই :min এবং :max কিলোবাইটের মধ্যে হতে হবে।',
        'string'  => ':attribute অবশ্যই :min এবং :max অক্ষরের মধ্যে হতে হবে।',
        'array'   => ':attribute অবশ্যই :min এবং :max টি আইটেম থাকতে হবে।',
    ],
    'boolean'              => ':attribute ঘরটি অবশ্যই সত্য (true) অথবা মিথ্যা (false) হতে হবে।',
    'confirmed'            => ':attribute নিশ্চিতকরণ মিলছে না।',
    'current_password'     => 'পাসওয়ার্ডটি সঠিক নয়।',
    'date'                 => ':attribute একটি সঠিক তারিখ নয়।',
    'date_equals'          => ':attribute অবশ্যই :date এর সমান একটি তারিখ হতে হবে।',
    'date_format'          => ':attribute ফর্ম্যাটটি :format এর সাথে মিলছে না।',
    'declined'             => ':attribute অবশ্যই প্রত্যাখ্যান করতে হবে।',
    'declined_if'          => 'যখন :other এর মান :value হয়, তখন :attribute অবশ্যই প্রত্যাখ্যান করতে হবে।',
    'different'            => ':attribute এবং :other অবশ্যই আলাদা হতে হবে।',
    'digits'               => ':attribute অবশ্যই :digits অংকের হতে হবে।',
    'digits_between'       => ':attribute অবশ্যই :min এবং :max অংকের মধ্যে হতে হবে।',
    'dimensions'           => ':attribute এর ছবির মাত্রা সঠিক নয়।',
    'distinct'             => ':attribute এর মান ডুপ্লিকেট বা একই রয়েছে।',
    'email'                => ':attribute অবশ্যই একটি সঠিক ইমেইল ঠিকানা হতে হবে।',
    'ends_with'            => ':attribute অবশ্যই নিচের যে কোন একটি দিয়ে শেষ হতে হবে: :values',
    'enum'                 => 'নির্বাচিত :attribute সঠিক নয়।',
    'exists'               => 'নির্বাচিত :attribute সঠিক নয়।',
    'file'                 => ':attribute অবশ্যই একটি ফাইল হতে হবে।',
    'filled'               => ':attribute ঘরটি পূরণ করা আবশ্যক।',
    'gt'                   => [
        'numeric' => ':attribute অবশ্যই :value এর থেকে বড় হতে হবে।',
        'file'    => ':attribute অবশ্যই :value কিলোবাইটের থেকে বড় হতে হবে।',
        'string'  => ':attribute অবশ্যই :value অক্ষরের থেকে বড় হতে হবে।',
        'array'   => ':attribute অবশ্যই :value এর বেশি আইটেম থাকতে হবে।',
    ],
    'gte'                  => [
        'numeric' => ':attribute অবশ্যই :value এর সমান অথবা বড় হতে হবে।',
        'file'    => ':attribute অবশ্যই :value কিলোবাইটের সমান অথবা বড় হতে হবে।',
        'string'  => ':attribute অবশ্যই :value অক্ষরের সমান অথবা বড় হতে হবে।',
        'array'   => ':attribute অবশ্যই :value বা তার বেশি আইটেম থাকতে হবে।',
    ],
    'image'                => ':attribute অবশ্যই একটি ছবি হতে হবে।',
    'in'                   => 'নির্বাচিত :attribute সঠিক নয়।',
    'in_array'             => ':attribute ঘরটি :other এ বিদ্যমান নেই।',
    'integer'              => ':attribute অবশ্যই একটি পূর্ণসংখ্যা হতে হবে।',
    'ip'                   => ':attribute অবশ্যই একটি সঠিক আইপি (IP) ঠিকানা হতে হবে।',
    'ipv4'                 => ':attribute অবশ্যই একটি সঠিক আইপিভি৪ (IPv4) ঠিকানা হতে হবে।',
    'ipv6'                 => ':attribute অবশ্যই একটি সঠিক আইপিভি৬ (IPv6) ঠিকানা হতে হবে।',
    'json'                 => ':attribute অবশ্যই একটি সঠিক জেএসওএন (JSON) স্ট্রিং হতে হবে।',
    'lt'                   => [
        'numeric' => ':attribute অবশ্যই :value এর থেকে ছোট হতে হবে।',
        'file'    => ':attribute অবশ্যই :value কিলোবাইটের থেকে ছোট হতে হবে।',
        'string'  => ':attribute অবশ্যই :value অক্ষরের থেকে ছোট হতে হবে।',
        'array'   => ':attribute অবশ্যই :value এর কম আইটেম থাকতে হবে।',
    ],
    'lte'                  => [
        'numeric' => ':attribute অবশ্যই :value এর সমান অথবা ছোট হতে হবে।',
        'file'    => ':attribute অবশ্যই :value কিলোবাইটের সমান অথবা ছোট হতে হবে।',
        'string'  => ':attribute অবশ্যই :value অক্ষরের সমান অথবা ছোট হতে হবে।',
        'array'   => ':attribute অবশ্যই :value এর বেশি আইটেম থাকা যাবে না।',
    ],
    'max'                  => [
        'numeric' => ':attribute :max এর বেশি হতে পারবে না।',
        'file'    => ':attribute :max কিলোবাইটের বেশি হতে পারবে না।',
        'string'  => ':attribute :max অক্ষরের বেশি হতে পারবে না।',
        'array'   => ':attribute এ :max এর বেশি আইটেম থাকা যাবে না।',
    ],
    'mimes'                => ':attribute অবশ্যই :values ধরণের ফাইল হতে হবে।',
    'mimetypes'            => ':attribute অবশ্যই :values ধরণের ফাইল হতে হবে।',
    'min'                  => [
        'numeric' => ':attribute কমপক্ষে :min হতে হবে।',
        'file'    => ':attribute কমপক্ষে :min কিলোবাইট হতে হবে।',
        'string'  => ':attribute কমপক্ষে :min অক্ষর হতে হবে।',
        'array'   => ':attribute এ কমপক্ষে :min টি আইটেম থাকতে হবে।',
    ],
    'multiple_of'          => ':attribute অবশ্যই :value এর গুণিতক হতে হবে।',
    'not_in'               => 'নির্বাচিত :attribute সঠিক নয়।',
    'not_regex'            => ':attribute এর ফরম্যাট সঠিক নয়।',
    'numeric'              => ':attribute অবশ্যই একটি সংখ্যা হতে হবে।',
    'password'             => 'পাসওয়ার্ডটি ভুল।',
    'present'              => ':attribute ঘরটি উপস্থিত থাকতে হবে।',
    'regex'                => ':attribute ফরম্যাটটি সঠিক নয়।',
    'required'             => ':attribute ঘরটি অবশ্যই পূরণ করতে হবে।',
    'required_if'          => 'যখন :other এর মান :value হয়, তখন :attribute ঘরটি পূরণ করা আবশ্যক।',
    'required_unless'      => 'যতক্ষণ না :other এর মান :values এর মধ্যে থাকে, ততক্ষণ :attribute পূরণ করা আবশ্যক।',
    'required_with'        => 'যখন :values উপস্থিত থাকে, তখন :attribute পূরণ করা আবশ্যক।',
    'required_with_all'    => 'যখন :values উপস্থিত থাকে, তখন :attribute পূরণ করা আবশ্যক।',
    'required_without'     => 'যখন :values উপস্থিত না থাকে, তখন :attribute পূরণ করা আবশ্যক।',
    'required_without_all' => 'যখন :values উপস্থিত না থাকে, তখন :attribute পূরণ করা আবশ্যক।',
    'same'                 => ':attribute এবং :other অবশ্যই একই হতে হবে।',
    'size'                 => [
        'numeric' => ':attribute অবশ্যই :size হতে হবে।',
        'file'    => ':attribute অবশ্যই :size কিলোবাইট হতে হবে।',
        'string'  => ':attribute অবশ্যই :size অক্ষরের হতে হবে।',
        'array'   => ':attribute এ অবশ্যই :size টি আইটেম থাকতে হবে।',
    ],
    'starts_with'          => ':attribute অবশ্যই নিচের যে কোন একটি দিয়ে শুরু হতে হবে: :values',
    'string'               => ':attribute অবশ্যই একটি স্ট্রিং হতে হবে।',
    'timezone'             => ':attribute একটি সঠিক টাইমজোন হতে হবে।',
    'unique'               => ':attribute ইতিমধ্যে ব্যবহার করা হয়েছে।',
    'uploaded'             => ':attribute আপলোড করতে ব্যর্থ হয়েছে।',
    'url'                  => ':attribute একটি সঠিক ইউআরএল হতে হবে।',
    'uuid'                 => ':attribute একটি সঠিক ইউইউআইডি (UUID) হতে হবে।',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'name'                  => 'নাম',
        'username'              => 'ইউজারনেম',
        'email'                 => 'ইমেইল',
        'first_name'            => 'নামের প্রথম অংশ',
        'last_name'             => 'নামের শেষ অংশ',
        'password'              => 'পাসওয়ার্ড',
        'password_confirmation' => 'পাসওয়ার্ড নিশ্চিতকরণ',
        'city'                  => 'শহর',
        'country'               => 'দেশ',
        'address'               => 'ঠিকানা',
        'phone'                 => 'ফোন নম্বর',
        'mobile'                => 'মোবাইল নম্বর',
        'age'                   => 'বয়স',
        'sex'                   => 'লিঙ্গ',
        'gender'                => 'লিঙ্গ',
        'day'                   => 'দিন',
        'month'                 => 'মাস',
        'year'                  => 'বছর',
        'hour'                  => 'ঘন্টা',
        'minute'                => 'মিনিট',
        'second'                => 'সেকেন্ড',
        'title'                 => 'শিরোনাম',
        'content'               => 'বিষয়বস্তু',
        'description'           => 'বর্ণনা',
        'excerpt'               => 'সারাংশ',
        'date'                  => 'তারিখ',
        'time'                  => 'সময়',
        'available'             => 'উপলব্ধ',
        'size'                  => 'সাইজ',
    ],

];