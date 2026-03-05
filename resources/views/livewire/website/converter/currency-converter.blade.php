<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

new class extends Component {
    public $amount = 1;
    public $from_currency = 'USD';
    public $to_currency = 'BDT';
    public $converted_amount = 0;
    public $exchange_rate = 0;
    public $errorMessage = null;
    public $fromSearch = '';
    public $toSearch = '';
    public $fromDropdownOpen = false;
    public $toDropdownOpen = false;

    // এটি প্রাইভেসির জন্য প্রোটেক্টেড রাখা হয়েছে যাতে ফ্রন্টএন্ডে সরাসরি না যায়
    protected function getCurrencyData()
    {
        return [
            'AED' => ['label' => 'UAE Dirham', 'flag' => '🇦🇪', 'symbol' => 'د.إ', 'code' => 'AED'],
            'AFN' => ['label' => 'Afghan Afghani', 'flag' => '🇦🇫', 'symbol' => '؋', 'code' => 'AFN'],
            'ALL' => ['label' => 'Albanian Lek', 'flag' => '🇦🇱', 'symbol' => 'L', 'code' => 'ALL'],
            'AMD' => ['label' => 'Armenian Dram', 'flag' => '🇦🇲', 'symbol' => '֏', 'code' => 'AMD'],
            'ANG' => ['label' => 'Netherlands Antillean Guilder', 'flag' => '🇧🇶', 'symbol' => 'ƒ', 'code' => 'ANG'],
            'AOA' => ['label' => 'Angolan Kwanza', 'flag' => '🇦🇴', 'symbol' => 'Kz', 'code' => 'AOA'],
            'ARS' => ['label' => 'Argentine Peso', 'flag' => '🇦🇷', 'symbol' => '$', 'code' => 'ARS'],
            'AUD' => ['label' => 'Australian Dollar', 'flag' => '🇦🇺', 'symbol' => '$', 'code' => 'AUD'],
            'AWG' => ['label' => 'Aruban Florin', 'flag' => '🇦🇼', 'symbol' => 'ƒ', 'code' => 'AWG'],
            'AZN' => ['label' => 'Azerbaijani Manat', 'flag' => '🇦🇿', 'symbol' => '₼', 'code' => 'AZN'],
            'BAM' => ['label' => 'Bosnia-Herzegovina Convertible Mark', 'flag' => '🇧🇦', 'symbol' => 'KM', 'code' => 'BAM'],
            'BBD' => ['label' => 'Barbadian Dollar', 'flag' => '🇧🇧', 'symbol' => '$', 'code' => 'BBD'],
            'BDT' => ['label' => 'Bangladeshi Taka', 'flag' => '🇧🇩', 'symbol' => '৳', 'code' => 'BDT'],
            'BGN' => ['label' => 'Bulgarian Lev', 'flag' => '🇧🇬', 'symbol' => 'лв', 'code' => 'BGN'],
            'BHD' => ['label' => 'Bahraini Dinar', 'flag' => '🇧🇭', 'symbol' => '.د.ب', 'code' => 'BHD'],
            'BIF' => ['label' => 'Burundian Franc', 'flag' => '🇧🇮', 'symbol' => 'FBu', 'code' => 'BIF'],
            'BMD' => ['label' => 'Bermudian Dollar', 'flag' => '🇧🇲', 'symbol' => '$', 'code' => 'BMD'],
            'BND' => ['label' => 'Brunei Dollar', 'flag' => '🇧🇳', 'symbol' => '$', 'code' => 'BND'],
            'BOB' => ['label' => 'Bolivian Boliviano', 'flag' => '🇧🇴', 'symbol' => 'Bs.', 'code' => 'BOB'],
            'BRL' => ['label' => 'Brazilian Real', 'flag' => '🇧🇷', 'symbol' => 'R$', 'code' => 'BRL'],
            'BSD' => ['label' => 'Bahamian Dollar', 'flag' => '🇧🇸', 'symbol' => '$', 'code' => 'BSD'],
            'BTN' => ['label' => 'Bhutanese Ngultrum', 'flag' => '🇧🇹', 'symbol' => 'Nu.', 'code' => 'BTN'],
            'BWP' => ['label' => 'Botswanan Pula', 'flag' => '🇧🇼', 'symbol' => 'P', 'code' => 'BWP'],
            'BYN' => ['label' => 'Belarusian Ruble', 'flag' => '🇧🇾', 'symbol' => 'Br', 'code' => 'BYN'],
            'BZD' => ['label' => 'Belize Dollar', 'flag' => '🇧🇿', 'symbol' => 'BZ$', 'code' => 'BZD'],
            'CAD' => ['label' => 'Canadian Dollar', 'flag' => '🇨🇦', 'symbol' => '$', 'code' => 'CAD'],
            'CDF' => ['label' => 'Congolese Franc', 'flag' => '🇨🇩', 'symbol' => 'FC', 'code' => 'CDF'],
            'CHF' => ['label' => 'Swiss Franc', 'flag' => '🇨🇭', 'symbol' => 'CHF', 'code' => 'CHF'],
            'CLP' => ['label' => 'Chilean Peso', 'flag' => '🇨🇱', 'symbol' => '$', 'code' => 'CLP'],
            'CNY' => ['label' => 'Chinese Yuan', 'flag' => '🇨🇳', 'symbol' => '¥', 'code' => 'CNY'],
            'COP' => ['label' => 'Colombian Peso', 'flag' => '🇨🇴', 'symbol' => '$', 'code' => 'COP'],
            'CRC' => ['label' => 'Costa Rican Colón', 'flag' => '🇨🇷', 'symbol' => '₡', 'code' => 'CRC'],
            'CUP' => ['label' => 'Cuban Peso', 'flag' => '🇨🇺', 'symbol' => '₱', 'code' => 'CUP'],
            'CVE' => ['label' => 'Cape Verdean Escudo', 'flag' => '🇨🇻', 'symbol' => 'Esc', 'code' => 'CVE'],
            'CZK' => ['label' => 'Czech Koruna', 'flag' => '🇨🇿', 'symbol' => 'Kč', 'code' => 'CZK'],
            'DJF' => ['label' => 'Djiboutian Franc', 'flag' => '🇩🇯', 'symbol' => 'Fdj', 'code' => 'DJF'],
            'DKK' => ['label' => 'Danish Krone', 'flag' => '🇩🇰', 'symbol' => 'kr', 'code' => 'DKK'],
            'DOP' => ['label' => 'Dominican Peso', 'flag' => '🇩🇴', 'symbol' => 'RD$', 'code' => 'DOP'],
            'DZD' => ['label' => 'Algerian Dinar', 'flag' => '🇩🇿', 'symbol' => 'د.ج', 'code' => 'DZD'],
            'EGP' => ['label' => 'Egyptian Pound', 'flag' => '🇪🇬', 'symbol' => '£', 'code' => 'EGP'],
            'ERN' => ['label' => 'Eritrean Nakfa', 'flag' => '🇪🇷', 'symbol' => 'Nfk', 'code' => 'ERN'],
            'ETB' => ['label' => 'Ethiopian Birr', 'flag' => '🇪🇹', 'symbol' => 'Br', 'code' => 'ETB'],
            'EUR' => ['label' => 'Euro', 'flag' => '🇪🇺', 'symbol' => '€', 'code' => 'EUR'],
            'FJD' => ['label' => 'Fiji Dollar', 'flag' => '🇫🇯', 'symbol' => '$', 'code' => 'FJD'],
            'FKP' => ['label' => 'Falkland Islands Pound', 'flag' => '🇫🇰', 'symbol' => '£', 'code' => 'FKP'],
            'GBP' => ['label' => 'British Pound', 'flag' => '🇬🇧', 'symbol' => '£', 'code' => 'GBP'],
            'GEL' => ['label' => 'Georgian Lari', 'flag' => '🇬🇪', 'symbol' => '₾', 'code' => 'GEL'],
            'GHS' => ['label' => 'Ghanaian Cedi', 'flag' => '🇬🇭', 'symbol' => '₵', 'code' => 'GHS'],
            'GIP' => ['label' => 'Gibraltar Pound', 'flag' => '🇬🇮', 'symbol' => '£', 'code' => 'GIP'],
            'GMD' => ['label' => 'Gambian Dalasi', 'flag' => '🇬🇲', 'symbol' => 'D', 'code' => 'GMD'],
            'GNF' => ['label' => 'Guinean Franc', 'flag' => '🇬🇳', 'symbol' => 'FG', 'code' => 'GNF'],
            'GTQ' => ['label' => 'Guatemalan Quetzal', 'flag' => '🇬🇹', 'symbol' => 'Q', 'code' => 'GTQ'],
            'GYD' => ['label' => 'Guyanese Dollar', 'flag' => '🇬🇾', 'symbol' => '$', 'code' => 'GYD'],
            'HKD' => ['label' => 'Hong Kong Dollar', 'flag' => '🇭🇰', 'symbol' => '$', 'code' => 'HKD'],
            'HNL' => ['label' => 'Honduran Lempira', 'flag' => '🇭🇳', 'symbol' => 'L', 'code' => 'HNL'],
            'HRK' => ['label' => 'Croatian Kuna', 'flag' => '🇭🇷', 'symbol' => 'kn', 'code' => 'HRK'],
            'HTG' => ['label' => 'Haitian Gourde', 'flag' => '🇭🇹', 'symbol' => 'G', 'code' => 'HTG'],
            'HUF' => ['label' => 'Hungarian Forint', 'flag' => '🇭🇺', 'symbol' => 'Ft', 'code' => 'HUF'],
            'IDR' => ['label' => 'Indonesian Rupiah', 'flag' => '🇮🇩', 'symbol' => 'Rp', 'code' => 'IDR'],
            'ILS' => ['label' => 'Israeli New Sheqel', 'flag' => '🇮🇱', 'symbol' => '₪', 'code' => 'ILS'],
            'INR' => ['label' => 'Indian Rupee', 'flag' => '🇮🇳', 'symbol' => '₹', 'code' => 'INR'],
            'IQD' => ['label' => 'Iraqi Dinar', 'flag' => '🇮🇶', 'symbol' => 'ع.د', 'code' => 'IQD'],
            'IRR' => ['label' => 'Iranian Rial', 'flag' => '🇮🇷', 'symbol' => '﷼', 'code' => 'IRR'],
            'ISK' => ['label' => 'Icelandic Króna', 'flag' => '🇮🇸', 'symbol' => 'kr', 'code' => 'ISK'],
            'JMD' => ['label' => 'Jamaican Dollar', 'flag' => '🇯🇲', 'symbol' => 'J$', 'code' => 'JMD'],
            'JOD' => ['label' => 'Jordanian Dinar', 'flag' => '🇯🇴', 'symbol' => 'د.ا', 'code' => 'JOD'],
            'JPY' => ['label' => 'Japanese Yen', 'flag' => '🇯🇵', 'symbol' => '¥', 'code' => 'JPY'],
            'KES' => ['label' => 'Kenyan Shilling', 'flag' => '🇰🇪', 'symbol' => 'KSh', 'code' => 'KES'],
            'KGS' => ['label' => 'Kyrgyzstani Som', 'flag' => '🇰🇬', 'symbol' => 'с', 'code' => 'KGS'],
            'KHR' => ['label' => 'Cambodian Riel', 'flag' => '🇰🇭', 'symbol' => '៛', 'code' => 'KHR'],
            'KMF' => ['label' => 'Comorian Franc', 'flag' => '🇰🇲', 'symbol' => 'CF', 'code' => 'KMF'],
            'KPW' => ['label' => 'North Korean Won', 'flag' => '🇰🇵', 'symbol' => '₩', 'code' => 'KPW'],
            'KRW' => ['label' => 'South Korean Won', 'flag' => '🇰🇷', 'symbol' => '₩', 'code' => 'KRW'],
            'KWD' => ['label' => 'Kuwaiti Dinar', 'flag' => '🇰🇼', 'symbol' => 'د.ك', 'code' => 'KWD'],
            'KYD' => ['label' => 'Cayman Islands Dollar', 'flag' => '🇰🇾', 'symbol' => '$', 'code' => 'KYD'],
            'KZT' => ['label' => 'Kazakhstani Tenge', 'flag' => '🇰🇿', 'symbol' => '₸', 'code' => 'KZT'],
            'LAK' => ['label' => 'Lao Kip', 'flag' => '🇱🇦', 'symbol' => '₭', 'code' => 'LAK'],
            'LBP' => ['label' => 'Lebanese Pound', 'flag' => '🇱🇧', 'symbol' => 'ل.ل', 'code' => 'LBP'],
            'LKR' => ['label' => 'Sri Lankan Rupee', 'flag' => '🇱🇰', 'symbol' => '₨', 'code' => 'LKR'],
            'LRD' => ['label' => 'Liberian Dollar', 'flag' => '🇱🇷', 'symbol' => '$', 'code' => 'LRD'],
            'LSL' => ['label' => 'Lesotho Loti', 'flag' => '🇱🇸', 'symbol' => 'L', 'code' => 'LSL'],
            'LYD' => ['label' => 'Libyan Dinar', 'flag' => '🇱🇾', 'symbol' => 'ل.د', 'code' => 'LYD'],
            'MAD' => ['label' => 'Moroccan Dirham', 'flag' => '🇲🇦', 'symbol' => 'د.م.', 'code' => 'MAD'],
            'MDL' => ['label' => 'Moldovan Leu', 'flag' => '🇲🇩', 'symbol' => 'L', 'code' => 'MDL'],
            'MGA' => ['label' => 'Malagasy Ariary', 'flag' => '🇲🇬', 'symbol' => 'Ar', 'code' => 'MGA'],
            'MKD' => ['label' => 'Macedonian Denar', 'flag' => '🇲🇰', 'symbol' => 'ден', 'code' => 'MKD'],
            'MMK' => ['label' => 'Myanmar Kyat', 'flag' => '🇲🇲', 'symbol' => 'K', 'code' => 'MMK'],
            'MNT' => ['label' => 'Mongolian Tugrik', 'flag' => '🇲🇳', 'symbol' => '₮', 'code' => 'MNT'],
            'MOP' => ['label' => 'Macanese Pataca', 'flag' => '🇲🇴', 'symbol' => 'P', 'code' => 'MOP'],
            'MRU' => ['label' => 'Mauritanian Ouguiya', 'flag' => '🇲🇷', 'symbol' => 'UM', 'code' => 'MRU'],
            'MUR' => ['label' => 'Mauritian Rupee', 'flag' => '🇲🇺', 'symbol' => '₨', 'code' => 'MUR'],
            'MVR' => ['label' => 'Maldivian Rufiyaa', 'flag' => '🇲🇻', 'symbol' => 'ރ', 'code' => 'MVR'],
            'MWK' => ['label' => 'Malawian Kwacha', 'flag' => '🇲🇼', 'symbol' => 'MK', 'code' => 'MWK'],
            'MXN' => ['label' => 'Mexican Peso', 'flag' => '🇲🇽', 'symbol' => '$', 'code' => 'MXN'],
            'MYR' => ['label' => 'Malaysian Ringgit', 'flag' => '🇲🇾', 'symbol' => 'RM', 'code' => 'MYR'],
            'MZN' => ['label' => 'Mozambican Metical', 'flag' => '🇲🇿', 'symbol' => 'MT', 'code' => 'MZN'],
            'NAD' => ['label' => 'Namibian Dollar', 'flag' => '🇳🇦', 'symbol' => '$', 'code' => 'NAD'],
            'NGN' => ['label' => 'Nigerian Naira', 'flag' => '🇳🇬', 'symbol' => '₦', 'code' => 'NGN'],
            'NIO' => ['label' => 'Nicaraguan Córdoba', 'flag' => '🇳🇮', 'symbol' => 'C$', 'code' => 'NIO'],
            'NOK' => ['label' => 'Norwegian Krone', 'flag' => '🇳🇴', 'symbol' => 'kr', 'code' => 'NOK'],
            'NPR' => ['label' => 'Nepalese Rupee', 'flag' => '🇳🇵', 'symbol' => '₨', 'code' => 'NPR'],
            'NZD' => ['label' => 'New Zealand Dollar', 'flag' => '🇳🇿', 'symbol' => '$', 'code' => 'NZD'],
            'OMR' => ['label' => 'Omani Rial', 'flag' => '🇴🇲', 'symbol' => 'ر.ع.', 'code' => 'OMR'],
            'PAB' => ['label' => 'Panamanian Balboa', 'flag' => '🇵🇦', 'symbol' => 'B/.', 'code' => 'PAB'],
            'PEN' => ['label' => 'Peruvian Sol', 'flag' => '🇵🇪', 'symbol' => 'S/.', 'code' => 'PEN'],
            'PGK' => ['label' => 'Papua New Guinean Kina', 'flag' => '🇵🇬', 'symbol' => 'K', 'code' => 'PGK'],
            'PHP' => ['label' => 'Philippine Peso', 'flag' => '🇵🇭', 'symbol' => '₱', 'code' => 'PHP'],
            'PKR' => ['label' => 'Pakistani Rupee', 'flag' => '🇵🇰', 'symbol' => '₨', 'code' => 'PKR'],
            'PLN' => ['label' => 'Polish Złoty', 'flag' => '🇵🇱', 'symbol' => 'zł', 'code' => 'PLN'],
            'PYG' => ['label' => 'Paraguayan Guaraní', 'flag' => '🇵🇾', 'symbol' => '₲', 'code' => 'PYG'],
            'QAR' => ['label' => 'Qatari Riyal', 'flag' => '🇶🇦', 'symbol' => '﷼', 'code' => 'QAR'],
            'RON' => ['label' => 'Romanian Leu', 'flag' => '🇷🇴', 'symbol' => 'L', 'code' => 'RON'],
            'RSD' => ['label' => 'Serbian Dinar', 'flag' => '🇷🇸', 'symbol' => 'дин.', 'code' => 'RSD'],
            'RUB' => ['label' => 'Russian Ruble', 'flag' => '🇷🇺', 'symbol' => '₽', 'code' => 'RUB'],
            'RWF' => ['label' => 'Rwandan Franc', 'flag' => '🇷🇼', 'symbol' => 'RF', 'code' => 'RWF'],
            'SAR' => ['label' => 'Saudi Riyal', 'flag' => '🇸🇦', 'symbol' => '﷼', 'code' => 'SAR'],
            'SBD' => ['label' => 'Solomon Islands Dollar', 'flag' => '🇸🇧', 'symbol' => '$', 'code' => 'SBD'],
            'SCR' => ['label' => 'Seychellois Rupee', 'flag' => '🇸🇨', 'symbol' => '₨', 'code' => 'SCR'],
            'SDG' => ['label' => 'Sudanese Pound', 'flag' => '🇸🇩', 'symbol' => 'ج.س.', 'code' => 'SDG'],
            'SEK' => ['label' => 'Swedish Krona', 'flag' => '🇸🇪', 'symbol' => 'kr', 'code' => 'SEK'],
            'SGD' => ['label' => 'Singapore Dollar', 'flag' => '🇸🇬', 'symbol' => '$', 'code' => 'SGD'],
            'SLL' => ['label' => 'Sierra Leonean Leone', 'flag' => '🇸🇱', 'symbol' => 'Le', 'code' => 'SLL'],
            'SOS' => ['label' => 'Somali Shilling', 'flag' => '🇸🇴', 'symbol' => 'S', 'code' => 'SOS'],
            'SRD' => ['label' => 'Surinamese Dollar', 'flag' => '🇸🇷', 'symbol' => '$', 'code' => 'SRD'],
            'SSP' => ['label' => 'South Sudanese Pound', 'flag' => '🇸🇸', 'symbol' => '£', 'code' => 'SSP'],
            'STN' => ['label' => 'São Tomé and Príncipe Dobra', 'flag' => '🇸🇹', 'symbol' => 'Db', 'code' => 'STN'],
            'SYP' => ['label' => 'Syrian Pound', 'flag' => '🇸🇾', 'symbol' => '£', 'code' => 'SYP'],
            'SZL' => ['label' => 'Swazi Lilangeni', 'flag' => '🇸🇿', 'symbol' => 'E', 'code' => 'SZL'],
            'THB' => ['label' => 'Thai Baht', 'flag' => '🇹🇭', 'symbol' => '฿', 'code' => 'THB'],
            'TJS' => ['label' => 'Tajikistani Somoni', 'flag' => '🇹🇯', 'symbol' => 'ЅМ', 'code' => 'TJS'],
            'TMT' => ['label' => 'Turkmenistani Manat', 'flag' => '🇹🇲', 'symbol' => 'm', 'code' => 'TMT'],
            'TND' => ['label' => 'Tunisian Dinar', 'flag' => '🇹🇳', 'symbol' => 'د.ت', 'code' => 'TND'],
            'TOP' => ['label' => 'Tongan Paʻanga', 'flag' => '🇹🇴', 'symbol' => 'T$', 'code' => 'TOP'],
            'TRY' => ['label' => 'Turkish Lira', 'flag' => '🇹🇷', 'symbol' => '₺', 'code' => 'TRY'],
            'TTD' => ['label' => 'Trinidad and Tobago Dollar', 'flag' => '🇹🇹', 'symbol' => 'TT$', 'code' => 'TTD'],
            'TWD' => ['label' => 'New Taiwan Dollar', 'flag' => '🇹🇼', 'symbol' => 'NT$', 'code' => 'TWD'],
            'TZS' => ['label' => 'Tanzanian Shilling', 'flag' => '🇹🇿', 'symbol' => 'TSh', 'code' => 'TZS'],
            'UAH' => ['label' => 'Ukrainian Hryvnia', 'flag' => '🇺🇦', 'symbol' => '₴', 'code' => 'UAH'],
            'UGX' => ['label' => 'Ugandan Shilling', 'flag' => '🇺🇬', 'symbol' => 'USh', 'code' => 'UGX'],
            'USD' => ['label' => 'US Dollar', 'flag' => '🇺🇸', 'symbol' => '$', 'code' => 'USD'],
            'UYU' => ['label' => 'Uruguayan Peso', 'flag' => '🇺🇾', 'symbol' => '$U', 'code' => 'UYU'],
            'UZS' => ['label' => 'Uzbekistan Som', 'flag' => '🇺🇿', 'symbol' => 'сум', 'code' => 'UZS'],
            'VES' => ['label' => 'Venezuelan Bolívar', 'flag' => '🇻🇪', 'symbol' => 'Bs.', 'code' => 'VES'],
            'VND' => ['label' => 'Vietnamese Dong', 'flag' => '🇻🇳', 'symbol' => '₫', 'code' => 'VND'],
            'VUV' => ['label' => 'Vanuatu Vatu', 'flag' => '🇻🇺', 'symbol' => 'Vt', 'code' => 'VUV'],
            'WST' => ['label' => 'Samoan Tālā', 'flag' => '🇼🇸', 'symbol' => 'T', 'code' => 'WST'],
            'XAF' => ['label' => 'Central African CFA Franc', 'flag' => '🇨🇲', 'symbol' => 'FCFA', 'code' => 'XAF'],
            'XCD' => ['label' => 'East Caribbean Dollar', 'flag' => '🇦🇬', 'symbol' => '$', 'code' => 'XCD'],
            'XOF' => ['label' => 'West African CFA Franc', 'flag' => '🇧🇯', 'symbol' => 'CFA', 'code' => 'XOF'],
            'XPF' => ['label' => 'CFP Franc', 'flag' => '🇵🇫', 'symbol' => '₣', 'code' => 'XPF'],
            'YER' => ['label' => 'Yemeni Rial', 'flag' => '🇾🇪', 'symbol' => '﷼', 'code' => 'YER'],
            'ZAR' => ['label' => 'South African Rand', 'flag' => '🇿🇦', 'symbol' => 'R', 'code' => 'ZAR'],
            'ZMW' => ['label' => 'Zambian Kwacha', 'flag' => '🇿🇲', 'symbol' => 'ZK', 'code' => 'ZMW'],
            'ZWL' => ['label' => 'Zimbabwean Dollar', 'flag' => '🇿🇼', 'symbol' => 'Z$', 'code' => 'ZWL'],
        ];
    }

    public function mount()
    {
        $this->convertCurrency();
    }

    public function convertCurrency()
    {
        $this->errorMessage = null;
        $cacheKey = "rate_{$this->from_currency}_{$this->to_currency}";

        try {
            $rate = Cache::remember($cacheKey, 3600, function () {
                $response = Http::timeout(10)->get("https://api.exchangerate-api.com/v4/latest/{$this->from_currency}");
                return $response->successful() ? $response->json()['rates'][$this->to_currency] : null;
            });

            if (!$rate)
                throw new \Exception("Rate not found");

            $this->exchange_rate = $rate;
            $this->converted_amount = round((float) $this->amount * $rate, 2);
        } catch (\Exception $e) {
            $this->errorMessage = "নেটওয়ার্ক সমস্যা, আবার চেষ্টা করুন।";
            $this->exchange_rate = 0;
        }
    }

    public function updated($field)
    {
        if ($field === 'amount')
            $this->convertCurrency();
    }

    public function selectFromCurrency($code)
    {
        $this->from_currency = $code;
        $this->fromDropdownOpen = false;
        $this->fromSearch = '';
        $this->convertCurrency();
    }

    public function selectToCurrency($code)
    {
        $this->to_currency = $code;
        $this->toDropdownOpen = false;
        $this->toSearch = '';
        $this->convertCurrency();
    }

    public function swapCurrencyUnits()
    {
        [$this->from_currency, $this->to_currency] = [$this->to_currency, $this->from_currency];
        $this->convertCurrency();
    }

    public function with()
    {
        $allData = $this->getCurrencyData();

        return [
            // শুধু প্রয়োজনীয় ৪টি তথ্য পাঠানো হচ্ছে
            'fromLabel' => $allData[$this->from_currency]['label'] ?? '',
            'fromSymbol' => $allData[$this->from_currency]['symbol'] ?? '',
            'toLabel' => $allData[$this->to_currency]['label'] ?? '',
            'toSymbol' => $allData[$this->to_currency]['symbol'] ?? '',

            // ড্রপডাউন যখন খোলা হবে তখনই কেবল লিস্ট তৈরি হবে
            'filteredFrom' => $this->fromDropdownOpen ? $this->getFilteredList($this->fromSearch) : [],
            'filteredTo' => $this->toDropdownOpen ? $this->getFilteredList($this->toSearch) : [],
        ];
    }

    protected function getFilteredList($query)
    {
        $data = $this->getCurrencyData();
        if (empty($query))
            return $data;
        return array_filter($data, fn($c, $k) => stripos($c['label'], $query) !== false || stripos($k, $query) !== false, ARRAY_FILTER_USE_BOTH);
    }
};
?>

<div x-data="{ close() { $wire.set('fromDropdownOpen', false); $wire.set('toDropdownOpen', false); } }"
    x-on:keydown.escape="close()">

    <header class="text-center mb-8">
        <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white leading-tight">কারেন্সি কনভার্টার</h2>
        <p class="text-zinc-500 mt-2">সর্বনিম্ন পেলোড ও সুপার ফাস্ট রূপান্তর</p>
    </header>



    <div class="max-w-2xl mx-auto space-y-6">



        <flux:input.group>
            <div class="relative w-full">
                <flux:button class="w-full" wire:click="$set('fromDropdownOpen', true)">
                    <img src="https://flagcdn.com/w40/{{ strtolower(substr($from_currency, 0, 2)) }}.png"
                        class="w-5 h-5 mr-2 inline shadow-sm !m-0">
                    {{ $from_currency }} - {{ $fromLabel }}
                </flux:button>

                @if($fromDropdownOpen)
                    <div class="absolute z-50 mt-2 w-full bg-white dark:bg-zinc-700 shadow-2xl rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden"
                        @click.away="$wire.set('fromDropdownOpen', false)">
                        <div class="p-2 border-b border-zinc-100 dark:border-zinc-700">
                            <flux:input type="text" wire:model.live.debounce.200ms="fromSearch" placeholder="সার্চ..." />
                        </div>
                        <ul class="max-h-60 overflow-y-auto custom-scrollbar">
                            @foreach($filteredFrom as $code => $currency)
                                <flux:navlist.item wire:click="selectFromCurrency('{{ $code }}')">
                                    <div class="flex gap-2 justify-start items-center">
                                        <img src="https://flagcdn.com/w20/{{ strtolower(substr($code, 0, 2)) }}.png"
                                            class="mr-3 rounded-xs">
                                        <strong>{{ $code }}</strong> <span class="ml-2 text-zinc-500">-
                                            {{ $currency['label'] }}</span>
                                    </div>
                                </flux:navlist.item>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <flux:input id="amount" kbd="{{ $fromSymbol }}" type="number" wire:model.live.debounce.500ms="amount" />
        </flux:input.group>






        <div class="flex justify-center items-center my-4">
            <flux:button wire:click="swapCurrencyUnits" icon="arrows-up-down" variant="ghost" />
        </div>
        <flux:input.group>
            <div class="relative w-full">
                <flux:button class="w-full" wire:click="$set('toDropdownOpen', true)">
                    <img src="https://flagcdn.com/w40/{{ strtolower(substr($to_currency, 0, 2)) }}.png"
                        class="w-5 h-5 mr-2 inline shadow-sm">
                    {{ $to_currency }} - {{ $toLabel }}
                </flux:button>

                @if($toDropdownOpen)
                    <div class="absolute z-50 mt-2 w-full bg-white dark:bg-zinc-700 shadow-2xl rounded-xl overflow-hidden"
                        @click.away="$wire.set('toDropdownOpen', false)">
                        <div class="p-2 border-b border-zinc-100 dark:border-zinc-700">
                            <flux:input type="text" wire:model.live.debounce.200ms="toSearch" placeholder="সার্চ..." />
                        </div>

                        <div class="max-h-60 overflow-y-auto">
                            @foreach($filteredTo as $code => $currency)
                                <flux:navlist.item wire:click="selectToCurrency('{{ $code }}')">

                                    <div class="flex gap-2 justify-start items-center">
                                        <img src="https://flagcdn.com/w20/{{ strtolower(substr($code, 0, 2)) }}.png"
                                            class="mr-3 rounded-xs">
                                        <strong>{{ $code }}</strong> <span class="ml-2 text-zinc-500">-
                                            {{ $currency['label'] }}</span>
                                    </div>

                                </flux:navlist.item>
                            @endforeach
                        </div>

                    </div>
                @endif
            </div>
            <flux:input id="converted_amount" kbd="{{ $toSymbol }}" type="number" :value="$converted_amount" readonly />
        </flux:input.group>

        <div
            class="mt-8 p-8 border border-zinc-200 dark:border-zinc-800 rounded-3xl bg-zinc-50 dark:bg-zinc-900/50 text-center shadow-inner">
            <p class="text-zinc-500 text-sm mb-1 uppercase tracking-wider font-semibold">কনভার্টেড অ্যামাউন্ট</p>
            <div class="flex justify-center items-baseline gap-2">
                <span
                    class="text-5xl font-black text-zinc-900 dark:text-white tracking-tighter">{{ number_format($converted_amount, 2) }}</span>
                <span class="text-xl font-bold text-zinc-400 uppercase">{{ $to_currency }}</span>
            </div>
            <p
                class="mt-4 text-sm font-medium text-blue-600 bg-blue-50 dark:bg-blue-900/20 px-4 py-2 rounded-full inline-block">
                1 {{ $from_currency }} = {{ number_format($exchange_rate, 4) }} {{ $to_currency }}
            </p>
        </div>
    </div>
</div>