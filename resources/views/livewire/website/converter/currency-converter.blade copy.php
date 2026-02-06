<?php

use Livewire\Volt\Component;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
// use Exception;  // এই লাইনটি মুছে ফেলুন

new class extends Component {
    public $amount = 1;
    public $from_currency = 'USD';
    public $to_currency = 'BDT';
    public $converted_amount = 0;
    public $last_updated;
    public $currencies = [];
    public $errorMessage = null;
    public $isLoading = false;
    public $exchange_rate = 0;
    public $fromSearch = '';
    public $toSearch = '';
    public $fromDropdownOpen = false;
    public $toDropdownOpen = false;

    // Comprehensive currency list with flags and symbols
    protected array $currencyData = [
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

    public function mount()
    {
        $this->currencies = $this->currencyData;
        $this->convertCurrency();
    }

    public function updated($field)
    {
        if (in_array($field, ['amount', 'from_currency', 'to_currency'])) {
            $this->convertCurrency();
        }

        // Close other dropdown when one is opened
        if ($field === 'fromDropdownOpen' && $this->fromDropdownOpen) {
            $this->toDropdownOpen = false;
        } elseif ($field === 'toDropdownOpen' && $this->toDropdownOpen) {
            $this->fromDropdownOpen = false;
        }
    }

    public function convertCurrency()
    {

        $this->errorMessage = null;
        $this->isLoading = true;

        // Use caching to reduce API calls
        $cacheKey = "exchange_rate_{$this->from_currency}_{$this->to_currency}";

        try {
            $rate = Cache::remember($cacheKey, 3600, function () { // Cache for 1 hour
                $client = new Client([
                    'timeout' => 10,
                    'verify' => false // Only for development, remove in production
                ]);

                $response = $client->get("https://api.exchangerate-api.com/v4/latest/{$this->from_currency}");
                $data = json_decode($response->getBody(), true);

                if (!isset($data['rates'][$this->to_currency])) {
                    throw new Exception("Rate for {$this->to_currency} not found");
                }

                return $data['rates'][$this->to_currency];
            });

            // Ensure amount is numeric, default to 0 if empty or invalid
            $amount = is_numeric($this->amount) ? (float) $this->amount : 0;

            $this->exchange_rate = $rate;
            $this->converted_amount = round($amount * $rate, 2);
            $this->last_updated = Carbon::today()->toDateTimeString();


        } catch (ConnectException $e) {
            $this->converted_amount = 0;
            $this->errorMessage = "🌐 ইন্টারনেট সংযোগ নেই বা এক্সচেঞ্জ সার্ভার কাজ করছে না।";
        } catch (RequestException $e) {
            $this->converted_amount = 0;
            $this->errorMessage = "এক্সচেঞ্জ রেট সার্ভারে সমস্যা হচ্ছে। দয়া করে কিছুক্ষণ পর আবার চেষ্টা করুন।";
        } catch (Exception $e) {
            $this->converted_amount = 0;
            $this->errorMessage = "একটি অপ্রত্যাশিত ত্রুটি ঘটেছে: " . $e->getMessage();
        } finally {
            $this->isLoading = false;
        }
    }

    public function swapCurrencyUnits()
    {
        [$this->from_currency, $this->to_currency] = [$this->to_currency, $this->from_currency];
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

    public function getFilteredFromCurrenciesProperty()
    {
        if (empty($this->fromSearch)) {
            return $this->currencies;
        }

        return collect($this->currencies)->filter(function ($currency) {
            return stripos($currency['label'], $this->fromSearch) !== false ||
                stripos($currency['code'], $this->fromSearch) !== false;
        })->toArray();
    }

    public function getFilteredToCurrenciesProperty()
    {
        if (empty($this->toSearch)) {
            return $this->currencies;
        }

        return collect($this->currencies)->filter(function ($currency) {
            return stripos($currency['label'], $this->toSearch) !== false ||
                stripos($currency['code'], $this->toSearch) !== false;
        })->toArray();
    }
};
?>


<div>
    <div class="max-w-5xl mx-auto py-12 px-4">
        <div class="mb-10 text-center md:text-left">
            <h2 class="text-2xl font-bold text-zinc-900 dark:text-white tracking-tight">কারেন্সি কনভার্টার</h2>
            <p class="text-zinc-500 dark:text-zinc-400 text-sm">সরাসরি এক্সচেঞ্জ রেট অনুযায়ী দ্রুত হিসাব করুন</p>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-2 shadow-sm">
            <div class="flex flex-col md:flex-row items-center gap-1">

                <div class="w-full md:flex-1">
                    <flux:input wire:model.live.debounce.500ms="amount" type="number" placeholder="পরিমাণ"
                        :prefix="$currencies[$from_currency]['symbol'] ?? ''"
                        class="!border-none !bg-transparent !shadow-none !h-14 text-lg font-medium focus:ring-0" />
                </div>

                <div class="hidden md:block h-8 border-r border-zinc-200 dark:border-zinc-800"></div>

                <div class="w-full md:w-64">
                    <flux:select wire:model.live="from_currency" variant="listbox" searchable
                        class="!border-none !bg-transparent !shadow-none !h-14">
                        @foreach($currencies as $code => $currency)
                            <flux:select.option value="{{ $code }}">
                                <div class="flex items-center gap-3">
                                    <img src="https://flagcdn.com/w40/{{ strtolower(substr($code, 0, 2)) }}.png"
                                        class="w-5 h-3.5 object-cover rounded-sm shadow-sm" alt="flag">
                                    <span class="font-medium">{{ $code }}</span>
                                </div>
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <div class="p-1">
                    <flux:button wire:click="swapCurrencyUnits" variant="ghost" size="sm"
                        class="!rounded-full hover:bg-zinc-100 dark:hover:bg-zinc-800">
                        <flux:icon.arrows-right-left class="rotate-90 md:rotate-0 text-zinc-400 size-5" />
                    </flux:button>
                </div>

                <div class="w-full md:w-64">
                    <flux:select wire:model.live="to_currency" variant="listbox" searchable
                        class="!border-none !bg-transparent !shadow-none !h-14">
                        @foreach($currencies as $code => $currency)
                            <flux:select.option value="{{ $code }}">
                                <div class="flex items-center gap-3">
                                    <img src="https://flagcdn.com/w40/{{ strtolower(substr($code, 0, 2)) }}.png"
                                        class="w-5 h-3.5 object-cover rounded-sm shadow-sm" alt="flag">
                                    <span class="font-medium">{{ $code }}</span>
                                </div>
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <div class="w-full md:w-auto p-1">
                    <div
                        class="h-12 md:h-14 min-w-[140px] flex items-center justify-center px-6 bg-blue-600 dark:bg-blue-500 rounded-2xl text-white font-bold text-lg shadow-lg shadow-blue-500/20 relative overflow-hidden">
                        <div wire:loading.flex class="absolute inset-0 bg-blue-700 items-center justify-center">
                            {{--
                            <flux:spinner size="sm" color="white" /> --}}
                        </div>
                        {{ number_format($converted_amount, 2) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex flex-col md:flex-row justify-between items-center px-4 gap-4">
            <div class="flex items-center gap-2 text-sm text-zinc-500">
                <flux:icon.information-circle class="size-4" />
                <span>1 {{ $from_currency }} = {{ number_format($converted_amount / ($amount ?: 1), 4) }}
                    {{ $to_currency }}</span>
            </div>

            @if ($last_updated)
                <div class="text-[11px] text-zinc-400 uppercase tracking-widest flex items-center gap-2">
                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                    সর্বশেষ আপডেট: {{ \Carbon\Carbon::parse($last_updated)->diffForHumans() }}
                </div>
            @endif
        </div>

        {{-- @if ($errorMessage)
        <flux:banner variant="danger" class="mt-6">
            {{ $errorMessage }}
        </flux:banner>
        @endif --}}
    </div>



</div>