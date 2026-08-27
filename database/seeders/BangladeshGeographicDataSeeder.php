<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Division;
use App\Models\District;
use App\Models\Thana;

class BangladeshGeographicDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks (MySQL only)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear previous data
        DB::table('thanas')->truncate();
        DB::table('districts')->truncate();
        DB::table('divisions')->truncate();

        // Enable foreign key checks back
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ১. Divisions
        $divisions = [
            ['name' => 'ঢাকা', 'slug' => 'dhaka', 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'চট্টগ্রাম', 'slug' => 'chittagong', 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রাজশাহী', 'slug' => 'rajshahi', 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'খুলনা', 'slug' => 'khulna', 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বরিশাল', 'slug' => 'barishal', 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সিলেট', 'slug' => 'sylhet', 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রংপুর', 'slug' => 'rangpur', 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ময়মনসিংহ', 'slug' => 'mymensingh', 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('divisions')->insert($divisions);

        $divisionIds = Division::pluck('id', 'slug')->toArray();

        // ২. Districts
        $districts = [
            // ঢাকা বিভাগ
            ['name' => 'ঢাকা', 'slug' => 'dhaka', 'division_id' => $divisionIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'গাজীপুর', 'slug' => 'gazipur', 'division_id' => $divisionIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নারায়ণগঞ্জ', 'slug' => 'narayanganj', 'division_id' => $divisionIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'টাঙ্গাইল', 'slug' => 'tangail', 'division_id' => $divisionIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কিশোরগঞ্জ', 'slug' => 'kishoreganj', 'division_id' => $divisionIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মানিকগঞ্জ', 'slug' => 'manikganj', 'division_id' => $divisionIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মুন্সিগঞ্জ', 'slug' => 'munshiganj', 'division_id' => $divisionIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রাজবাড়ী', 'slug' => 'rajbari', 'division_id' => $divisionIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মাদারীপুর', 'slug' => 'madaripur', 'division_id' => $divisionIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'গোপালগঞ্জ', 'slug' => 'gopalganj', 'division_id' => $divisionIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ফরিদপুর', 'slug' => 'faridpur', 'division_id' => $divisionIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'শরীয়তপুর', 'slug' => 'shariatpur', 'division_id' => $divisionIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নরসিংদী', 'slug' => 'narsingdi', 'division_id' => $divisionIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            // চট্টগ্রাম বিভাগ
            ['name' => 'চট্টগ্রাম', 'slug' => 'chittagong', 'division_id' => $divisionIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কক্সবাজার', 'slug' => 'coxsbazar', 'division_id' => $divisionIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বান্দরবান', 'slug' => 'bandarban', 'division_id' => $divisionIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রাঙ্গামাটি', 'slug' => 'rangamati', 'division_id' => $divisionIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'খাগড়াছড়ি', 'slug' => 'khagrachhari', 'division_id' => $divisionIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ফেনী', 'slug' => 'feni', 'division_id' => $divisionIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নোয়াখালী', 'slug' => 'noakhali', 'division_id' => $divisionIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'লক্ষ্মীপুর', 'slug' => 'lakshmipur', 'division_id' => $divisionIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'চাঁদপুর', 'slug' => 'chandpur', 'division_id' => $divisionIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ব্রাহ্মণবাড়িয়া', 'slug' => 'brahmanbaria', 'division_id' => $divisionIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কুমিল্লা', 'slug' => 'comilla', 'division_id' => $divisionIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            // রাজশাহী বিভাগ
            ['name' => 'রাজশাহী', 'slug' => 'rajshahi', 'division_id' => $divisionIds['rajshahi'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নাটোর', 'slug' => 'natore', 'division_id' => $divisionIds['rajshahi'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নওগাঁ', 'slug' => 'naogaon', 'division_id' => $divisionIds['rajshahi'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'চাঁপাইনবাবগঞ্জ', 'slug' => 'chapainawabganj', 'division_id' => $divisionIds['rajshahi'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'পাবনা', 'slug' => 'pabna', 'division_id' => $divisionIds['rajshahi'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সিরাজগঞ্জ', 'slug' => 'sirajganj', 'division_id' => $divisionIds['rajshahi'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বগুড়া', 'slug' => 'bogra', 'division_id' => $divisionIds['rajshahi'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'জয়পুরহাট', 'slug' => 'joypurhat', 'division_id' => $divisionIds['rajshahi'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            // খুলনা বিভাগ
            ['name' => 'খুলনা', 'slug' => 'khulna', 'division_id' => $divisionIds['khulna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বাগেরহাট', 'slug' => 'bagherhat', 'division_id' => $divisionIds['khulna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সাতক্ষীরা', 'slug' => 'satkhira', 'division_id' => $divisionIds['khulna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'যশোর', 'slug' => 'jessore', 'division_id' => $divisionIds['khulna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ঝিনাইদহ', 'slug' => 'jhenaidah', 'division_id' => $divisionIds['khulna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মাগুরা', 'slug' => 'magura', 'division_id' => $divisionIds['khulna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কুষ্টিয়া', 'slug' => 'kushtia', 'division_id' => $divisionIds['khulna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মেহেরপুর', 'slug' => 'meherpur', 'division_id' => $divisionIds['khulna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'চুয়াডাঙ্গা', 'slug' => 'chuadanga', 'division_id' => $divisionIds['khulna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নড়াইল', 'slug' => 'narail', 'division_id' => $divisionIds['khulna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            // বরিশাল বিভাগ
            ['name' => 'বরিশাল', 'slug' => 'barishal', 'division_id' => $divisionIds['barishal'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'পটুয়াখালী', 'slug' => 'patuakhali', 'division_id' => $divisionIds['barishal'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'পিরোজপুর', 'slug' => 'pirojpur', 'division_id' => $divisionIds['barishal'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ভোলা', 'slug' => 'bhola', 'division_id' => $divisionIds['barishal'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ঝালকাঠি', 'slug' => 'jhalokati', 'division_id' => $divisionIds['barishal'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বরগুনা', 'slug' => 'barguna', 'division_id' => $divisionIds['barishal'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            // সিলেট বিভাগ
            ['name' => 'সিলেট', 'slug' => 'sylhet', 'division_id' => $divisionIds['sylhet'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মৌলভীবাজার', 'slug' => 'moulvibazar', 'division_id' => $divisionIds['sylhet'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'হবিগঞ্জ', 'slug' => 'habiganj', 'division_id' => $divisionIds['sylhet'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সুনামগঞ্জ', 'slug' => 'sunamganj', 'division_id' => $divisionIds['sylhet'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            // রংপুর বিভাগ
            ['name' => 'রংপুর', 'slug' => 'rangpur', 'division_id' => $divisionIds['rangpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'দিনাজপুর', 'slug' => 'dinajpur', 'division_id' => $divisionIds['rangpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'গাইবান্ধা', 'slug' => 'gaibandha', 'division_id' => $divisionIds['rangpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কুড়িগ্রাম', 'slug' => 'kurigram', 'division_id' => $divisionIds['rangpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'লালমনিরহাট', 'slug' => 'lalmonirhat', 'division_id' => $divisionIds['rangpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নীলফামারী', 'slug' => 'nilphamari', 'division_id' => $divisionIds['rangpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'পঞ্চগড়', 'slug' => 'panchagarh', 'division_id' => $divisionIds['rangpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ঠাকুরগাঁও', 'slug' => 'thakurgaon', 'division_id' => $divisionIds['rangpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            // ময়মনসিংহ বিভাগ
            ['name' => 'ময়মনসিংহ', 'slug' => 'mymensingh', 'division_id' => $divisionIds['mymensingh'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নেত্রকোনা', 'slug' => 'netrokona', 'division_id' => $divisionIds['mymensingh'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'শেরপুর', 'slug' => 'sherpur', 'division_id' => $divisionIds['mymensingh'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'জামালপুর', 'slug' => 'jamalpur', 'division_id' => $divisionIds['mymensingh'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('districts')->insert($districts);

        $districtIds = DB::table('districts')->pluck('id', 'slug')->toArray();

        // ৩. Thanas
        $rawThanas = [
            // ঢাকা জেলা
            ['name' => 'আশুলিয়া', 'slug' => 'ashulia', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সাভার', 'slug' => 'savar', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ধামরাই', 'slug' => 'dhamrai', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কেরানীগঞ্জ', 'slug' => 'keraniganj', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'দোহার', 'slug' => 'dohar', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নবাবগঞ্জ', 'slug' => 'nababganj', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'তেজগাঁও', 'slug' => 'tejgaon', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'শাহবাগ', 'slug' => 'shahbag', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রমনা', 'slug' => 'ramna', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'পল্টন', 'slug' => 'polton', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মতিঝিল', 'slug' => 'motijheel', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'খিলগাঁও', 'slug' => 'khilgaon', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সবুজবাগ', 'slug' => 'sabujbagh', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'যাত্রাবাড়ী', 'slug' => 'jatrabari', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'শ্যামপুর', 'slug' => 'shyampur', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কদমতলী', 'slug' => 'kadamtali', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'লালবাগ', 'slug' => 'lalbagh', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'হাজারীবাগ', 'slug' => 'hazaribagh', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কামরাঙ্গীরচর', 'slug' => 'kamrangirchar', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মোহাম্মদপুর', 'slug' => 'mohammadpur', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'আদাবর', 'slug' => 'adabor', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'শেরেবাংলা নগর', 'slug' => 'sher-e-bangla-nagar', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'দারুস সালাম', 'slug' => 'darus-salam', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মিরপুর', 'slug' => 'mirpur', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'পল্লবী', 'slug' => 'pallabi', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'শাহ আলী', 'slug' => 'shah-ali', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রূপনগর', 'slug' => 'rupnagar', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ভাষানটেক', 'slug' => 'bhashantek', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কাফরুল', 'slug' => 'kafrul', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ক্যান্টনমেন্ট', 'slug' => 'cantonment', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বাড্ডা', 'slug' => 'badda', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ভাটারা', 'slug' => 'bhatara', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রামপুরা', 'slug' => 'rampura', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'উত্তরা পশ্চিম', 'slug' => 'uttara-paschim', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'উত্তরা পূর্ব', 'slug' => 'uttara-purba', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'তুরাগ', 'slug' => 'turag', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বিমানবন্দর', 'slug' => 'bimanbandar', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'দক্ষিণখান', 'slug' => 'dakshinkhan', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'উত্তরখান', 'slug' => 'uttarkhan', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'তেজগাঁও শিল্পাঞ্চল', 'slug' => 'tejgaon-shilpanchal', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'গুলশান', 'slug' => 'gulshan', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বনানী', 'slug' => 'banani', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ধানমন্ডি', 'slug' => 'dhanmondi', 'district_id' => $districtIds['dhaka'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // গাজীপুর জেলা
            ['name' => 'গাজীপুর সদর', 'slug' => 'gazipur-sadar', 'district_id' => $districtIds['gazipur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কালীগঞ্জ', 'slug' => 'kaliganj-gazipur', 'district_id' => $districtIds['gazipur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কাপাসিয়া', 'slug' => 'kapasia', 'district_id' => $districtIds['gazipur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'শ্রীপুর', 'slug' => 'sreepur-gazipur', 'district_id' => $districtIds['gazipur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কালিয়াকৈর', 'slug' => 'kaliakair', 'district_id' => $districtIds['gazipur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // নারায়ণগঞ্জ জেলা
            ['name' => 'নারায়ণগঞ্জ সদর', 'slug' => 'narayanganj-sadar', 'district_id' => $districtIds['narayanganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'আড়াইহাজার', 'slug' => 'araihazir', 'district_id' => $districtIds['narayanganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রূপগঞ্জ', 'slug' => 'rupganj', 'district_id' => $districtIds['narayanganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সোনারগাঁও', 'slug' => 'sonargaon', 'district_id' => $districtIds['narayanganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বন্দর', 'slug' => 'bandar-narayanganj', 'district_id' => $districtIds['narayanganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // টাঙ্গাইল জেলা
            ['name' => 'টাঙ্গাইল সদর', 'slug' => 'tangail-sadar', 'district_id' => $districtIds['tangail'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বাসাইল', 'slug' => 'basail', 'district_id' => $districtIds['tangail'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'গোপালপুর', 'slug' => 'gopalpur-tangail', 'district_id' => $districtIds['tangail'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ঘাটাইল', 'slug' => 'ghatail', 'district_id' => $districtIds['tangail'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কালিহাতী', 'slug' => 'kalihati', 'district_id' => $districtIds['tangail'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মধুপুর', 'slug' => 'madhupur-tangail', 'district_id' => $districtIds['tangail'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মির্জাপুর', 'slug' => 'mirzapur-tangail', 'district_id' => $districtIds['tangail'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নাগরপুর', 'slug' => 'nagarpara', 'district_id' => $districtIds['tangail'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সখিপুর', 'slug' => 'sakhipur', 'district_id' => $districtIds['tangail'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ভুয়াপুর', 'slug' => 'bhuapur', 'district_id' => $districtIds['tangail'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'দেলদুয়ার', 'slug' => 'delduar', 'district_id' => $districtIds['tangail'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ধনবাড়ী', 'slug' => 'dhanbari', 'district_id' => $districtIds['tangail'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // কিশোরগঞ্জ জেলা
            ['name' => 'কিশোরগঞ্জ সদর', 'slug' => 'kishoreganj-sadar', 'district_id' => $districtIds['kishoreganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'অষ্টগ্রাম', 'slug' => 'ashtagram', 'district_id' => $districtIds['kishoreganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বাজিতপুর', 'slug' => 'bajitpur', 'district_id' => $districtIds['kishoreganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ভৈরব', 'slug' => 'bhairab', 'district_id' => $districtIds['kishoreganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'হোসেনপুর', 'slug' => 'hossainpur', 'district_id' => $districtIds['kishoreganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ইটনা', 'slug' => 'itna', 'district_id' => $districtIds['kishoreganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'করিমগঞ্জ', 'slug' => 'karimganj', 'district_id' => $districtIds['kishoreganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কাটিয়াদী', 'slug' => 'katiadi', 'district_id' => $districtIds['kishoreganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কুলিয়ারচর', 'slug' => 'kuliar-char', 'district_id' => $districtIds['kishoreganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মিঠামইন', 'slug' => 'mithamain', 'district_id' => $districtIds['kishoreganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নিকলী', 'slug' => 'nikli', 'district_id' => $districtIds['kishoreganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'তাড়াইল', 'slug' => 'tarail', 'district_id' => $districtIds['kishoreganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'পাকুন্দিয়া', 'slug' => 'pakundia', 'district_id' => $districtIds['kishoreganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // মানিকগঞ্জ জেলা
            ['name' => 'মানিকগঞ্জ সদর', 'slug' => 'manikganj-sadar', 'district_id' => $districtIds['manikganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'দৌলতপুর', 'slug' => 'daulatpur-manikganj', 'district_id' => $districtIds['manikganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ঘিওর', 'slug' => 'ghior', 'district_id' => $districtIds['manikganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'হরিরামপুর', 'slug' => 'harirampur', 'district_id' => $districtIds['manikganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সাটুরিয়া', 'slug' => 'saturia', 'district_id' => $districtIds['manikganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'শিবালয়', 'slug' => 'shibalaya', 'district_id' => $districtIds['manikganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সিংগাইর', 'slug' => 'singair', 'district_id' => $districtIds['manikganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // মুন্সিগঞ্জ জেলা
            ['name' => 'মুন্সিগঞ্জ সদর', 'slug' => 'munshiganj-sadar', 'district_id' => $districtIds['munshiganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'গজারিয়া', 'slug' => 'gajaria', 'district_id' => $districtIds['munshiganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'লৌহজং', 'slug' => 'louhajang', 'district_id' => $districtIds['munshiganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'শ্রীনগর', 'slug' => 'sreenagar', 'district_id' => $districtIds['munshiganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সিরাজদিখান', 'slug' => 'sirajdikhan', 'district_id' => $districtIds['munshiganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'টঙ্গীবাড়ী', 'slug' => 'tongibari', 'district_id' => $districtIds['munshiganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // রাজবাড়ী জেলা
            ['name' => 'রাজবাড়ী সদর', 'slug' => 'rajbari-sadar', 'district_id' => $districtIds['rajbari'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'গোয়ালন্দ', 'slug' => 'goalanda', 'district_id' => $districtIds['rajbari'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'পাংশা', 'slug' => 'pangsha', 'district_id' => $districtIds['rajbari'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বালিয়াকান্দি', 'slug' => 'baliakandi', 'district_id' => $districtIds['rajbari'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কালুখালী', 'slug' => 'kalukhali', 'district_id' => $districtIds['rajbari'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // মাদারীপুর জেলা
            ['name' => 'মাদারীপুর সদর', 'slug' => 'madaripur-sadar', 'district_id' => $districtIds['madaripur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ডাসার', 'slug' => 'dasar', 'district_id' => $districtIds['madaripur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কালকিনি', 'slug' => 'kalkini', 'district_id' => $districtIds['madaripur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রাজৈর', 'slug' => 'rajoir', 'district_id' => $districtIds['madaripur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'শিবচর', 'slug' => 'shibchar', 'district_id' => $districtIds['madaripur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // গোপালগঞ্জ জেলা
            ['name' => 'গোপালগঞ্জ সদর', 'slug' => 'gopalganj-sadar', 'district_id' => $districtIds['gopalganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কাশিয়ানী', 'slug' => 'kashiani', 'district_id' => $districtIds['gopalganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কোটালীপাড়া', 'slug' => 'kotalipara', 'district_id' => $districtIds['gopalganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মুকসুদপুর', 'slug' => 'muksudpur', 'district_id' => $districtIds['gopalganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'টুঙ্গিপাড়া', 'slug' => 'tungipara', 'district_id' => $districtIds['gopalganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // ফরিদপুর জেলা
            ['name' => 'ফরিদপুর সদর', 'slug' => 'faridpur-sadar', 'district_id' => $districtIds['faridpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'আলফাডাঙ্গা', 'slug' => 'alfadanga', 'district_id' => $districtIds['faridpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ভাঙ্গা', 'slug' => 'bhanga', 'district_id' => $districtIds['faridpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বোয়ালমারী', 'slug' => 'boalmari', 'district_id' => $districtIds['faridpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'চরভদ্রাসন', 'slug' => 'charbhadrasan', 'district_id' => $districtIds['faridpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মধুখালী', 'slug' => 'madhukhali', 'district_id' => $districtIds['faridpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নগরকান্দা', 'slug' => 'nagarkanda', 'district_id' => $districtIds['faridpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সদরপুর', 'slug' => 'sadarpur', 'district_id' => $districtIds['faridpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সালথা', 'slug' => 'saltha', 'district_id' => $districtIds['faridpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // শরীয়তপুর জেলা
            ['name' => 'শরীয়তপুর সদর', 'slug' => 'shariatpur-sadar', 'district_id' => $districtIds['shariatpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ডামুড্যা', 'slug' => 'damudya', 'district_id' => $districtIds['shariatpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'গোসাইরহাট', 'slug' => 'gosairhat', 'district_id' => $districtIds['shariatpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নড়িয়া', 'slug' => 'naria', 'district_id' => $districtIds['shariatpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ভেদরগঞ্জ', 'slug' => 'bhedarganj', 'district_id' => $districtIds['shariatpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'জাজিরা', 'slug' => 'jazira', 'district_id' => $districtIds['shariatpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // নরসিংদী জেলা
            ['name' => 'নরসিংদী সদর', 'slug' => 'narsingdi-sadar', 'district_id' => $districtIds['narsingdi'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বেলাবো', 'slug' => 'belabo', 'district_id' => $districtIds['narsingdi'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মনোহরদী', 'slug' => 'manohardi', 'district_id' => $districtIds['narsingdi'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'পলাশ', 'slug' => 'palash', 'district_id' => $districtIds['narsingdi'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রায়পুরা', 'slug' => 'raipura', 'district_id' => $districtIds['narsingdi'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'শিবপুর', 'slug' => 'shibpur', 'district_id' => $districtIds['narsingdi'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // চট্টগ্রাম জেলা
            ['name' => 'আনোয়ারা', 'slug' => 'anwara', 'district_id' => $districtIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বাঁশখালী', 'slug' => 'bashkhali', 'district_id' => $districtIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বোয়ালখালী', 'slug' => 'boalkhali', 'district_id' => $districtIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'চন্দনাইশ', 'slug' => 'chandanaish', 'district_id' => $districtIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ফটিকছড়ি', 'slug' => 'fatikchhari', 'district_id' => $districtIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'হাটহাজারী', 'slug' => 'hathazari', 'district_id' => $districtIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'লোহাগাড়া', 'slug' => 'lohagara', 'district_id' => $districtIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মীরসরাই', 'slug' => 'mirsharai', 'district_id' => $districtIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'পটিয়া', 'slug' => 'patia', 'district_id' => $districtIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রাঙ্গুনিয়া', 'slug' => 'rangunia', 'district_id' => $districtIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সন্দ্বীপ', 'slug' => 'sandwip', 'district_id' => $districtIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সাতকানিয়া', 'slug' => 'satkania', 'district_id' => $districtIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সীতাকুণ্ড', 'slug' => 'sitakunda', 'district_id' => $districtIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কর্ণফুলী', 'slug' => 'karnaphuli', 'district_id' => $districtIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'আকবরশাহ', 'slug' => 'akbarshah', 'district_id' => $districtIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বাকলিয়া', 'slug' => 'bakalia', 'district_id' => $districtIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'চান্দগাঁও', 'slug' => 'chandgaon', 'district_id' => $districtIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কোতোয়ালী', 'slug' => 'kotwali', 'district_id' => $districtIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'পাহাড়তলী', 'slug' => 'pahartali', 'district_id' => $districtIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'পাঁচলাইশ', 'slug' => 'panchlaish', 'district_id' => $districtIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ডবলমুরিং', 'slug' => 'doublemooring', 'district_id' => $districtIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'হালিশহর', 'slug' => 'halishahar', 'district_id' => $districtIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বন্দর', 'slug' => 'bandar', 'district_id' => $districtIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বায়েজীদ বোস্তামী', 'slug' => 'bayezid-bostami', 'district_id' => $districtIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'এনায়েত বাজার', 'slug' => 'enayet-bazar', 'district_id' => $districtIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'চকবাজার', 'slug' => 'chakbazar', 'district_id' => $districtIds['chittagong'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // কক্সবাজার জেলা
            ['name' => 'কক্সবাজার সদর', 'slug' => 'coxsbazar-sadar', 'district_id' => $districtIds['coxsbazar'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'চকোরিয়া', 'slug' => 'chokoria', 'district_id' => $districtIds['coxsbazar'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'পেকুয়া', 'slug' => 'pekua', 'district_id' => $districtIds['coxsbazar'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কুতুবদিয়া', 'slug' => 'kutubdia', 'district_id' => $districtIds['coxsbazar'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মহেশখালী', 'slug' => 'moheshkhali', 'district_id' => $districtIds['coxsbazar'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রামু', 'slug' => 'ramu', 'district_id' => $districtIds['coxsbazar'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'টেকনাফ', 'slug' => 'teknaf', 'district_id' => $districtIds['coxsbazar'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'উখিয়া', 'slug' => 'ukhiya', 'district_id' => $districtIds['coxsbazar'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // বান্দরবান জেলা
            ['name' => 'বান্দরবান সদর', 'slug' => 'bandarban-sadar', 'district_id' => $districtIds['bandarban'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'আঁচি', 'slug' => 'achi', 'district_id' => $districtIds['bandarban'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'লামা', 'slug' => 'lama', 'district_id' => $districtIds['bandarban'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নাইক্ষ্যংছড়ি', 'slug' => 'naikhongchhari', 'district_id' => $districtIds['bandarban'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'আলীকদম', 'slug' => 'alikadam', 'district_id' => $districtIds['bandarban'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রুমা', 'slug' => 'ruma', 'district_id' => $districtIds['bandarban'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'থানচি', 'slug' => 'thanchi', 'district_id' => $districtIds['bandarban'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // রাঙ্গামাটি জেলা
            ['name' => 'রাঙ্গামাটি সদর', 'slug' => 'rangamati-sadar', 'district_id' => $districtIds['rangamati'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বাঘাইছড়ি', 'slug' => 'baghaichhari', 'district_id' => $districtIds['rangamati'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বরকল', 'slug' => 'barkal', 'district_id' => $districtIds['rangamati'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কাপ্তাই', 'slug' => 'kaptai', 'district_id' => $districtIds['rangamati'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কাউখালী', 'slug' => 'kaukhali', 'district_id' => $districtIds['rangamati'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'লংগদু', 'slug' => 'longadu', 'district_id' => $districtIds['rangamati'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নানিয়ারচর', 'slug' => 'naniarchar', 'district_id' => $districtIds['rangamati'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রাজস্থলী', 'slug' => 'rajasthali', 'district_id' => $districtIds['rangamati'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সাজেক', 'slug' => 'sajek', 'district_id' => $districtIds['rangamati'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'জুরাছড়ি', 'slug' => 'jurachhari', 'district_id' => $districtIds['rangamati'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // খাগড়াছড়ি জেলা
            ['name' => 'খাগড়াছড়ি সদর', 'slug' => 'khagrachhari-sadar', 'district_id' => $districtIds['khagrachhari'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'দীঘিনালা', 'slug' => 'dighinala', 'district_id' => $districtIds['khagrachhari'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'পানছড়ি', 'slug' => 'panchhari', 'district_id' => $districtIds['khagrachhari'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মহালছড়ি', 'slug' => 'mohalchhari', 'district_id' => $districtIds['khagrachhari'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মানিকছড়ি', 'slug' => 'manikchhari', 'district_id' => $districtIds['khagrachhari'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রামগড়', 'slug' => 'ramgarh', 'district_id' => $districtIds['khagrachhari'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মাটিরাঙ্গা', 'slug' => 'matiranga', 'district_id' => $districtIds['khagrachhari'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'লক্ষ্মীছড়ি', 'slug' => 'lakshmichhari', 'district_id' => $districtIds['khagrachhari'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'গুইমারা', 'slug' => 'guimara', 'district_id' => $districtIds['khagrachhari'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // ফেনী জেলা
            ['name' => 'ফেনী সদর', 'slug' => 'feni-sadar', 'district_id' => $districtIds['feni'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'দাগনভূঞা', 'slug' => 'daganbhuiyan', 'district_id' => $districtIds['feni'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ছাগলনাইয়া', 'slug' => 'chagalnaiya', 'district_id' => $districtIds['feni'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'পরশুরাম', 'slug' => 'parshuram', 'district_id' => $districtIds['feni'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ফুলগাজী', 'slug' => 'fulgazi', 'district_id' => $districtIds['feni'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সোনাগাজী', 'slug' => 'sonagazi', 'district_id' => $districtIds['feni'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // নোয়াখালী জেলা
            ['name' => 'নোয়াখালী সদর', 'slug' => 'noakhali-sadar', 'district_id' => $districtIds['noakhali'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বেগমগঞ্জ', 'slug' => 'begumganj', 'district_id' => $districtIds['noakhali'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'চাটখিল', 'slug' => 'chatkhil', 'district_id' => $districtIds['noakhali'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কোম্পানীগঞ্জ', 'slug' => 'companiganj-noakhali', 'district_id' => $districtIds['noakhali'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'হাতিয়া', 'slug' => 'hatia', 'district_id' => $districtIds['noakhali'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সেনবাগ', 'slug' => 'senbagh', 'district_id' => $districtIds['noakhali'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সুবর্ণচর', 'slug' => 'subarnachar', 'district_id' => $districtIds['noakhali'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কবিরহাট', 'slug' => 'kabirhat', 'district_id' => $districtIds['noakhali'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সোনাইমুড়ী', 'slug' => 'sonaimuri', 'district_id' => $districtIds['noakhali'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // লক্ষ্মীপুর জেলা
            ['name' => 'লক্ষ্মীপুর সদর', 'slug' => 'lakshmipur-sadar', 'district_id' => $districtIds['lakshmipur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কমলনগর', 'slug' => 'kamalnagar', 'district_id' => $districtIds['lakshmipur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রায়পুর', 'slug' => 'raipur-lakshmipur', 'district_id' => $districtIds['lakshmipur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রামগঞ্জ', 'slug' => 'ramganj', 'district_id' => $districtIds['lakshmipur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রামগতি', 'slug' => 'ramgati', 'district_id' => $districtIds['lakshmipur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // চাঁদপুর জেলা
            ['name' => 'চাঁদপুর সদর', 'slug' => 'chandpur-sadar', 'district_id' => $districtIds['chandpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ফরিদগঞ্জ', 'slug' => 'faridganj', 'district_id' => $districtIds['chandpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'হাজীগঞ্জ', 'slug' => 'hajiganj', 'district_id' => $districtIds['chandpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'হাইমচর', 'slug' => 'haimchar', 'district_id' => $districtIds['chandpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কচুয়া', 'slug' => 'kachua', 'district_id' => $districtIds['chandpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মতলব উত্তর', 'slug' => 'matlab-uttar', 'district_id' => $districtIds['chandpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মতলব দক্ষিণ', 'slug' => 'matlab-dakshin', 'district_id' => $districtIds['chandpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'শাহরাস্তি', 'slug' => 'shahrasti', 'district_id' => $districtIds['chandpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // ব্রাহ্মণবাড়িয়া জেলা
            ['name' => 'ব্রাহ্মণবাড়িয়া সদর', 'slug' => 'brahmanbaria-sadar', 'district_id' => $districtIds['brahmanbaria'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'আশুগঞ্জ', 'slug' => 'ashuganj', 'district_id' => $districtIds['brahmanbaria'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নাসিরনগর', 'slug' => 'nasirnagar', 'district_id' => $districtIds['brahmanbaria'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নবীনগর', 'slug' => 'nabinagar', 'district_id' => $districtIds['brahmanbaria'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সরাইল', 'slug' => 'sarail', 'district_id' => $districtIds['brahmanbaria'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কসবা', 'slug' => 'kasba', 'district_id' => $districtIds['brahmanbaria'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'আখাউড়া', 'slug' => 'akhaura', 'district_id' => $districtIds['brahmanbaria'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বিজয়নগর', 'slug' => 'bijoynagar', 'district_id' => $districtIds['brahmanbaria'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বাঞ্ছারামপুর', 'slug' => 'bancharampur', 'district_id' => $districtIds['brahmanbaria'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // কুমিল্লা জেলা
            ['name' => 'কুমিল্লা সদর দক্ষিণ', 'slug' => 'comilla-sadar-dakshin', 'district_id' => $districtIds['comilla'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বরুড়া', 'slug' => 'barura', 'district_id' => $districtIds['comilla'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ব্রাহ্মণপাড়া', 'slug' => 'brahmanpara', 'district_id' => $districtIds['comilla'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বুড়িচং', 'slug' => 'burichang', 'district_id' => $districtIds['comilla'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'চান্দিনা', 'slug' => 'chandina', 'district_id' => $districtIds['comilla'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'দাউদকান্দি', 'slug' => 'daudkandi', 'district_id' => $districtIds['comilla'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'দেবিদ্বার', 'slug' => 'debiddar', 'district_id' => $districtIds['comilla'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'হোমনা', 'slug' => 'homna', 'district_id' => $districtIds['comilla'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'লাকসাম', 'slug' => 'laksam', 'district_id' => $districtIds['comilla'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'লালমাই', 'slug' => 'lalmai', 'district_id' => $districtIds['comilla'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মনোহরগঞ্জ', 'slug' => 'manoharganj', 'district_id' => $districtIds['comilla'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মুরাদনগর', 'slug' => 'muradnagar', 'district_id' => $districtIds['comilla'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নাঙ্গলকোট', 'slug' => 'nangalkot', 'district_id' => $districtIds['comilla'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মেঘনা', 'slug' => 'meghna', 'district_id' => $districtIds['comilla'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'তিতাস', 'slug' => 'titas', 'district_id' => $districtIds['comilla'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'চৌদ্দগ্রাম', 'slug' => 'chauddagram', 'district_id' => $districtIds['comilla'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কুমিল্লা আদর্শ সদর', 'slug' => 'comilla-adarsha-sadar', 'district_id' => $districtIds['comilla'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // রাজশাহী জেলা
            ['name' => 'বাগমারা', 'slug' => 'bagmara', 'district_id' => $districtIds['rajshahi'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বাঘা', 'slug' => 'bagha', 'district_id' => $districtIds['rajshahi'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'চারঘাট', 'slug' => 'charghat', 'district_id' => $districtIds['rajshahi'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'দুর্গাপুর', 'slug' => 'durgapur-rajshahi', 'district_id' => $districtIds['rajshahi'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'গোদাগাড়ী', 'slug' => 'godagari', 'district_id' => $districtIds['rajshahi'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মোহনপুর', 'slug' => 'mohanpur-rajshahi', 'district_id' => $districtIds['rajshahi'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'পবা', 'slug' => 'paba', 'district_id' => $districtIds['rajshahi'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'পুঠিয়া', 'slug' => 'puthia', 'district_id' => $districtIds['rajshahi'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'তানোর', 'slug' => 'tanore', 'district_id' => $districtIds['rajshahi'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রাজশাহী সদর', 'slug' => 'rajshahi-sadar', 'district_id' => $districtIds['rajshahi'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বোয়ালিয়া', 'slug' => 'boalia', 'district_id' => $districtIds['rajshahi'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'শাহমখদুম', 'slug' => 'shahmakhdum', 'district_id' => $districtIds['rajshahi'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মতিহার', 'slug' => 'matihar', 'district_id' => $districtIds['rajshahi'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রাজপাড়া', 'slug' => 'rajpara', 'district_id' => $districtIds['rajshahi'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // নাটোর জেলা
            ['name' => 'নাটোর সদর', 'slug' => 'natore-sadar', 'district_id' => $districtIds['natore'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বাগাতিপাড়া', 'slug' => 'bagatipara', 'district_id' => $districtIds['natore'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বড়াইগ্রাম', 'slug' => 'baraigram', 'district_id' => $districtIds['natore'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'গুরুদাসপুর', 'slug' => 'gurudaspur', 'district_id' => $districtIds['natore'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'লালপুর', 'slug' => 'lalpur', 'district_id' => $districtIds['natore'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সিংড়া', 'slug' => 'singra', 'district_id' => $districtIds['natore'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নলডাঙ্গা', 'slug' => 'naldanga', 'district_id' => $districtIds['natore'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // নওগাঁ জেলা
            ['name' => 'নওগাঁ সদর', 'slug' => 'naogaon-sadar', 'district_id' => $districtIds['naogaon'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'আত্রাই', 'slug' => 'atrai', 'district_id' => $districtIds['naogaon'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বদলগাছী', 'slug' => 'badalgachhi', 'district_id' => $districtIds['naogaon'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মান্দা', 'slug' => 'manda', 'district_id' => $districtIds['naogaon'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নিয়ামতপুর', 'slug' => 'niamotpur', 'district_id' => $districtIds['naogaon'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'পত্নীতলা', 'slug' => 'patnitala', 'district_id' => $districtIds['naogaon'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'পোরশা', 'slug' => 'porsha', 'district_id' => $districtIds['naogaon'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সাপাহার', 'slug' => 'sapahar', 'district_id' => $districtIds['naogaon'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ধামইরহাট', 'slug' => 'dhamoirhat', 'district_id' => $districtIds['naogaon'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মহাদেবপুর', 'slug' => 'mahadevpur', 'district_id' => $districtIds['naogaon'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রানীনগর', 'slug' => 'raninagar', 'district_id' => $districtIds['naogaon'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // চাঁপাইনবাবগঞ্জ জেলা
            ['name' => 'চাঁপাইনবাবগঞ্জ সদর', 'slug' => 'chapainawabganj-sadar', 'district_id' => $districtIds['chapainawabganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'গোমস্তাপুর', 'slug' => 'gomostapur', 'district_id' => $districtIds['chapainawabganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নাচোল', 'slug' => 'nachole', 'district_id' => $districtIds['chapainawabganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'শিবগঞ্জ', 'slug' => 'shibganj-chapainawabganj', 'district_id' => $districtIds['chapainawabganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ভোলাহাট', 'slug' => 'bholahat', 'district_id' => $districtIds['chapainawabganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // পাবনা জেলা
            ['name' => 'পাবনা সদর', 'slug' => 'pabna-sadar', 'district_id' => $districtIds['pabna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'আটঘরিয়া', 'slug' => 'atghoria', 'district_id' => $districtIds['pabna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ঈশ্বরদী', 'slug' => 'ishwardi', 'district_id' => $districtIds['pabna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ফরিদপুর', 'slug' => 'faridpur-pabna', 'district_id' => $districtIds['pabna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বেড়া', 'slug' => 'bera', 'district_id' => $districtIds['pabna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ভাংগুড়া', 'slug' => 'bhangura', 'district_id' => $districtIds['pabna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'চাটমোহর', 'slug' => 'chatmohar', 'district_id' => $districtIds['pabna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সুজানগর', 'slug' => 'sujanagar', 'district_id' => $districtIds['pabna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'আমিনপুর', 'slug' => 'aminpur', 'district_id' => $districtIds['pabna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // সিরাজগঞ্জ জেলা
            ['name' => 'সিরাজগঞ্জ সদর', 'slug' => 'sirajganj-sadar', 'district_id' => $districtIds['sirajganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বেলকুচি', 'slug' => 'belkuchi', 'district_id' => $districtIds['sirajganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'চৌহালী', 'slug' => 'chauhali', 'district_id' => $districtIds['sirajganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কাজীপুর', 'slug' => 'kazipur', 'district_id' => $districtIds['sirajganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রায়গঞ্জ', 'slug' => 'raiganj', 'district_id' => $districtIds['sirajganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'শাহজাদপুর', 'slug' => 'shahjadpur', 'district_id' => $districtIds['sirajganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'উল্লাপাড়া', 'slug' => 'ullapara', 'district_id' => $districtIds['sirajganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'তাড়াশ', 'slug' => 'tarash', 'district_id' => $districtIds['sirajganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কামারখন্দ', 'slug' => 'kamarkhanda', 'district_id' => $districtIds['sirajganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // বগুড়া জেলা
            ['name' => 'বগুড়া সদর', 'slug' => 'bogra-sadar', 'district_id' => $districtIds['bogra'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'আদমদীঘি', 'slug' => 'adamdighi', 'district_id' => $districtIds['bogra'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ধুনট', 'slug' => 'dhunat', 'district_id' => $districtIds['bogra'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'দুপচাঁচিয়া', 'slug' => 'dupchanchia', 'district_id' => $districtIds['bogra'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'গাবতলী', 'slug' => 'gabtali', 'district_id' => $districtIds['bogra'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কাহালু', 'slug' => 'kahaloo', 'district_id' => $districtIds['bogra'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নন্দীগ্রাম', 'slug' => 'nandigram', 'district_id' => $districtIds['bogra'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সারিয়াকান্দি', 'slug' => 'sariakandi', 'district_id' => $districtIds['bogra'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'শেরপুর', 'slug' => 'sherpur-bogra', 'district_id' => $districtIds['bogra'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'শিবগঞ্জ', 'slug' => 'shibganj-bogra', 'district_id' => $districtIds['bogra'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সোনাতলা', 'slug' => 'sonatala', 'district_id' => $districtIds['bogra'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'শাহজাহানপুর', 'slug' => 'shahjahanpur', 'district_id' => $districtIds['bogra'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // জয়পুরহাট জেলা
            ['name' => 'জয়পুরহাট সদর', 'slug' => 'joypurhat-sadar', 'district_id' => $districtIds['joypurhat'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'আক্কেলপুর', 'slug' => 'akkelpur', 'district_id' => $districtIds['joypurhat'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কালাই', 'slug' => 'kalai', 'district_id' => $districtIds['joypurhat'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ক্ষেতলাল', 'slug' => 'khetlal', 'district_id' => $districtIds['joypurhat'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'পাঁচবিবি', 'slug' => 'panchbibi', 'district_id' => $districtIds['joypurhat'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // খুলনা জেলা
            ['name' => 'খুলনা সদর', 'slug' => 'khulna-sadar', 'district_id' => $districtIds['khulna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'দাকোপ', 'slug' => 'dakop', 'district_id' => $districtIds['khulna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'দিঘলিয়া', 'slug' => 'dighalia', 'district_id' => $districtIds['khulna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কয়রা', 'slug' => 'koyra', 'district_id' => $districtIds['khulna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ডুমুরিয়া', 'slug' => 'dumuria', 'district_id' => $districtIds['khulna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ফুলতলা', 'slug' => 'phultala', 'district_id' => $districtIds['khulna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'পাইকগাছা', 'slug' => 'paikgachha', 'district_id' => $districtIds['khulna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রূপসা', 'slug' => 'rupsa', 'district_id' => $districtIds['khulna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বটিয়াঘাটা', 'slug' => 'batiaghata', 'district_id' => $districtIds['khulna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'খালিশপুর', 'slug' => 'khalishpur', 'district_id' => $districtIds['khulna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সোনাডাঙ্গা', 'slug' => 'sonadanga', 'district_id' => $districtIds['khulna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'দৌলতপুর', 'slug' => 'daulatpur', 'district_id' => $districtIds['khulna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'লবণচরা', 'slug' => 'labanchara', 'district_id' => $districtIds['khulna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'আড়ংঘাটা', 'slug' => 'aranghata', 'district_id' => $districtIds['khulna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // বাগেরহাট জেলা
            ['name' => 'বাগেরহাট সদর', 'slug' => 'bagherhat-sadar', 'district_id' => $districtIds['bagherhat'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'চিতলমারী', 'slug' => 'chitalmari', 'district_id' => $districtIds['bagherhat'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ফকিরহাট', 'slug' => 'fakirhat', 'district_id' => $districtIds['bagherhat'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কচুয়া', 'slug' => 'kachua-bagerhat', 'district_id' => $districtIds['bagherhat'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মোল্লাহাট', 'slug' => 'mollahat', 'district_id' => $districtIds['bagherhat'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মোংলা', 'slug' => 'mongla', 'district_id' => $districtIds['bagherhat'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মোরেলগঞ্জ', 'slug' => 'morelganj', 'district_id' => $districtIds['bagherhat'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রামপাল', 'slug' => 'rampal', 'district_id' => $districtIds['bagherhat'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'শরণখোলা', 'slug' => 'sharankhola', 'district_id' => $districtIds['bagherhat'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // সাতক্ষীরা জেলা
            ['name' => 'সাতক্ষীরা সদর', 'slug' => 'satkhira-sadar', 'district_id' => $districtIds['satkhira'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'আশাশুনি', 'slug' => 'ashashuni', 'district_id' => $districtIds['satkhira'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'দেবহাটা', 'slug' => 'debhat', 'district_id' => $districtIds['satkhira'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কালিগঞ্জ', 'slug' => 'kaliganj-satkhira', 'district_id' => $districtIds['satkhira'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কলারোয়া', 'slug' => 'kalaroa', 'district_id' => $districtIds['satkhira'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'শ্যামনগর', 'slug' => 'shyamnagar', 'district_id' => $districtIds['satkhira'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'তালা', 'slug' => 'tala', 'district_id' => $districtIds['satkhira'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // যশোর জেলা
            ['name' => 'যশোর সদর', 'slug' => 'jessore-sadar', 'district_id' => $districtIds['jessore'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'অভয়নগর', 'slug' => 'abhaynagar', 'district_id' => $districtIds['jessore'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বাঘারপাড়া', 'slug' => 'bagharpara', 'district_id' => $districtIds['jessore'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'চৌগাছা', 'slug' => 'chaugachha', 'district_id' => $districtIds['jessore'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ঝিকরগাছা', 'slug' => 'jhikargachha', 'district_id' => $districtIds['jessore'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কেশবপুর', 'slug' => 'keshabpur', 'district_id' => $districtIds['jessore'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মনিরামপুর', 'slug' => 'manirampur', 'district_id' => $districtIds['jessore'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'শার্শা', 'slug' => 'sharsha', 'district_id' => $districtIds['jessore'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // ঝিনাইদহ জেলা
            ['name' => 'ঝিনাইদহ সদর', 'slug' => 'jhenaidah-sadar', 'district_id' => $districtIds['jhenaidah'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'হরিনাকুন্ডু', 'slug' => 'harinakundu', 'district_id' => $districtIds['jhenaidah'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কালীগঞ্জ', 'slug' => 'kaliganj-jhenaidah', 'district_id' => $districtIds['jhenaidah'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কোটচাঁদপুর', 'slug' => 'kotchandpur', 'district_id' => $districtIds['jhenaidah'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মহেশপুর', 'slug' => 'maheshpur', 'district_id' => $districtIds['jhenaidah'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'শৈলকুপা', 'slug' => 'shailkupa', 'district_id' => $districtIds['jhenaidah'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // মাগুরা জেলা
            ['name' => 'মাগুরা সদর', 'slug' => 'magura-sadar', 'district_id' => $districtIds['magura'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মহম্মদপুর', 'slug' => 'mohammadpur-magura', 'district_id' => $districtIds['magura'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'শালিখা', 'slug' => 'shalikha', 'district_id' => $districtIds['magura'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'শ্রীপুর', 'slug' => 'sreepur-magura', 'district_id' => $districtIds['magura'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // কুষ্টিয়া জেলা
            ['name' => 'কুষ্টিয়া সদর', 'slug' => 'kushtia-sadar', 'district_id' => $districtIds['kushtia'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ভেড়ামারা', 'slug' => 'bheramara', 'district_id' => $districtIds['kushtia'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'দৌলতপুর', 'slug' => 'daulatpur-kushtia', 'district_id' => $districtIds['kushtia'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কুমারখালী', 'slug' => 'kumarkhali', 'district_id' => $districtIds['kushtia'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'খোকসা', 'slug' => 'khoksa', 'district_id' => $districtIds['kushtia'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মিরপুর', 'slug' => 'mirpur-kushtia', 'district_id' => $districtIds['kushtia'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // মেহেরপুর জেলা
            ['name' => 'মেহেরপুর সদর', 'slug' => 'meherpur-sadar', 'district_id' => $districtIds['meherpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মুজিবনগর', 'slug' => 'mujibnagar', 'district_id' => $districtIds['meherpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'গাংনী', 'slug' => 'gangni', 'district_id' => $districtIds['meherpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // চুয়াডাঙ্গা জেলা
            ['name' => 'চুয়াডাঙ্গা সদর', 'slug' => 'chuadanga-sadar', 'district_id' => $districtIds['chuadanga'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'আলমডাঙ্গা', 'slug' => 'alamdanga', 'district_id' => $districtIds['chuadanga'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'দামুড়হুদা', 'slug' => 'damurhuda', 'district_id' => $districtIds['chuadanga'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'জীবননগর', 'slug' => 'jibannagar', 'district_id' => $districtIds['chuadanga'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // নড়াইল জেলা
            ['name' => 'নড়াইল সদর', 'slug' => 'narail-sadar', 'district_id' => $districtIds['narail'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কালিয়া', 'slug' => 'kalia', 'district_id' => $districtIds['narail'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'লোহাগড়া', 'slug' => 'lohagara-narail', 'district_id' => $districtIds['narail'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // বরিশাল জেলা
            ['name' => 'বরিশাল সদর', 'slug' => 'sadar-barishal', 'district_id' => $districtIds['barishal'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'আগৈলঝাড়া', 'slug' => 'agoiljhara', 'district_id' => $districtIds['barishal'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বাবুগঞ্জ', 'slug' => 'babuganj', 'district_id' => $districtIds['barishal'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বাকেরগঞ্জ', 'slug' => 'bakerganj', 'district_id' => $districtIds['barishal'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বানারীপাড়া', 'slug' => 'banaripara', 'district_id' => $districtIds['barishal'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'গৌরনদী', 'slug' => 'gaurnadi', 'district_id' => $districtIds['barishal'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মেহেন্দিগঞ্জ', 'slug' => 'mehendiganj', 'district_id' => $districtIds['barishal'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মুলাদী', 'slug' => 'muladi', 'district_id' => $districtIds['barishal'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'হিজলা', 'slug' => 'hijla', 'district_id' => $districtIds['barishal'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'উজিরপুর', 'slug' => 'uazirpur', 'district_id' => $districtIds['barishal'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // পটুয়াখালী জেলা
            ['name' => 'পটুয়াখালী সদর', 'slug' => 'patuakhali-sadar', 'district_id' => $districtIds['patuakhali'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বাউফল', 'slug' => 'bauphal', 'district_id' => $districtIds['patuakhali'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'দশমিনা', 'slug' => 'dashmina', 'district_id' => $districtIds['patuakhali'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'গলাচিপা', 'slug' => 'galachipa', 'district_id' => $districtIds['patuakhali'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কলাপাড়া', 'slug' => 'kalapara', 'district_id' => $districtIds['patuakhali'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মির্জাগঞ্জ', 'slug' => 'mirzaganj', 'district_id' => $districtIds['patuakhali'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'দুমকি', 'slug' => 'dumki', 'district_id' => $districtIds['patuakhali'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রাঙ্গাবালী', 'slug' => 'rangabali', 'district_id' => $districtIds['patuakhali'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // পিরোজপুর জেলা
            ['name' => 'পিরোজপুর সদর', 'slug' => 'pirojpur-sadar', 'district_id' => $districtIds['pirojpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ভাণ্ডারিয়া', 'slug' => 'bhandaria', 'district_id' => $districtIds['pirojpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কাউখালী', 'slug' => 'kaukhali-pirojpur', 'district_id' => $districtIds['pirojpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মঠবাড়িয়া', 'slug' => 'mathbaria', 'district_id' => $districtIds['pirojpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নাজিরপুর', 'slug' => 'nazirpur', 'district_id' => $districtIds['pirojpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নেছারাবাদ (স্বরূপকাঠী)', 'slug' => 'nesarabad-swarupkathi', 'district_id' => $districtIds['pirojpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ইন্দুরকানী', 'slug' => 'indurkani', 'district_id' => $districtIds['pirojpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // ভোলা জেলা
            ['name' => 'ভোলা সদর', 'slug' => 'bhola-sadar', 'district_id' => $districtIds['bhola'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বোরহানউদ্দিন', 'slug' => 'borhanuddin', 'district_id' => $districtIds['bhola'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'চরফ্যাশন', 'slug' => 'charfession', 'district_id' => $districtIds['bhola'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'দৌলতখান', 'slug' => 'daulatkhan', 'district_id' => $districtIds['bhola'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'লালমোহন', 'slug' => 'lalmohan', 'district_id' => $districtIds['bhola'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'তজুমদ্দিন', 'slug' => 'tazumuddin', 'district_id' => $districtIds['bhola'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মনপুরা', 'slug' => 'manpura', 'district_id' => $districtIds['bhola'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // ঝালকাঠি জেলা
            ['name' => 'ঝালকাঠি সদর', 'slug' => 'jhalokati-sadar', 'district_id' => $districtIds['jhalokati'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কাঁঠালিয়া', 'slug' => 'kathalia', 'district_id' => $districtIds['jhalokati'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নলছিটি', 'slug' => 'nalchity', 'district_id' => $districtIds['jhalokati'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রাজাপুর', 'slug' => 'rajapur', 'district_id' => $districtIds['jhalokati'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // বরগুনা জেলা
            ['name' => 'বরগুনা সদর', 'slug' => 'barguna-sadar', 'district_id' => $districtIds['barguna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'আমতলী', 'slug' => 'amtali', 'district_id' => $districtIds['barguna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'পাথরঘাটা', 'slug' => 'patharghata', 'district_id' => $districtIds['barguna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বেতাগী', 'slug' => 'betagi', 'district_id' => $districtIds['barguna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বামনা', 'slug' => 'bamna', 'district_id' => $districtIds['barguna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'তালতলী', 'slug' => 'taltali', 'district_id' => $districtIds['barguna'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // সিলেট জেলা
            ['name' => 'সিলেট সদর', 'slug' => 'sylhet-sadar', 'district_id' => $districtIds['sylhet'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বালাগঞ্জ', 'slug' => 'balaganj', 'district_id' => $districtIds['sylhet'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বিয়ানীবাজার', 'slug' => 'bianibazar', 'district_id' => $districtIds['sylhet'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বিশ্বনাথ', 'slug' => 'bishwanath', 'district_id' => $districtIds['sylhet'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ফেঞ্চুগঞ্জ', 'slug' => 'fenchuganj', 'district_id' => $districtIds['sylhet'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'গোলাপগঞ্জ', 'slug' => 'golapganj', 'district_id' => $districtIds['sylhet'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'গোয়াইনঘাট', 'slug' => 'goainghat', 'district_id' => $districtIds['sylhet'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কানাইঘাট', 'slug' => 'kanaighat', 'district_id' => $districtIds['sylhet'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'জৈন্তাপুর', 'slug' => 'jainthapur', 'district_id' => $districtIds['sylhet'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'দক্ষিণ সুরমা', 'slug' => 'dakshin-surma', 'district_id' => $districtIds['sylhet'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'উসমানীনগর', 'slug' => 'osmaninagar', 'district_id' => $districtIds['sylhet'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // মৌলভীবাজার জেলা
            ['name' => 'মৌলভীবাজার সদর', 'slug' => 'moulvibazar-sadar', 'district_id' => $districtIds['moulvibazar'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বড়লেখা', 'slug' => 'barlekha', 'district_id' => $districtIds['moulvibazar'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কমলগঞ্জ', 'slug' => 'kamalganj', 'district_id' => $districtIds['moulvibazar'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কুলাউড়া', 'slug' => 'kulaura', 'district_id' => $districtIds['moulvibazar'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রাজনগর', 'slug' => 'rajnagar', 'district_id' => $districtIds['moulvibazar'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'শ্রীমঙ্গল', 'slug' => 'sreemangal', 'district_id' => $districtIds['moulvibazar'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'জুড়ী', 'slug' => 'juri', 'district_id' => $districtIds['moulvibazar'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // হবিগঞ্জ জেলা
            ['name' => 'হবিগঞ্জ সদর', 'slug' => 'habiganj-sadar', 'district_id' => $districtIds['habiganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'আজমিরীগঞ্জ', 'slug' => 'ajmiriganj', 'district_id' => $districtIds['habiganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বুনিয়াচং', 'slug' => 'baniachang', 'district_id' => $districtIds['habiganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বাহুবল', 'slug' => 'bahubal', 'district_id' => $districtIds['habiganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'চুনারুঘাট', 'slug' => 'chunarughat', 'district_id' => $districtIds['habiganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'লাখাই', 'slug' => 'lakhai', 'district_id' => $districtIds['habiganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মাধবপুর', 'slug' => 'madhabpur', 'district_id' => $districtIds['habiganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নবীগঞ্জ', 'slug' => 'nabiganj', 'district_id' => $districtIds['habiganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'শায়েস্তাগঞ্জ', 'slug' => 'shayestaganj', 'district_id' => $districtIds['habiganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // সুনামগঞ্জ জেলা
            ['name' => 'সুনামগঞ্জ সদর', 'slug' => 'sunamganj-sadar', 'district_id' => $districtIds['sunamganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ছাতক', 'slug' => 'chhatak', 'district_id' => $districtIds['sunamganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'দিরাই', 'slug' => 'dirai', 'district_id' => $districtIds['sunamganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ধর্মপাশা', 'slug' => 'dharmapasha', 'district_id' => $districtIds['sunamganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'দোয়ারাবাজার', 'slug' => 'dowarabazar', 'district_id' => $districtIds['sunamganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'জগন্নাথপুর', 'slug' => 'jagannathpur', 'district_id' => $districtIds['sunamganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'জামালগঞ্জ', 'slug' => 'jamalganj-sunamganj', 'district_id' => $districtIds['sunamganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'শাল্লা', 'slug' => 'shalla', 'district_id' => $districtIds['sunamganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'তাহিরপুর', 'slug' => 'tahirpur', 'district_id' => $districtIds['sunamganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'দক্ষিণ সুনামগঞ্জ', 'slug' => 'dakshin-sunamganj', 'district_id' => $districtIds['sunamganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মধ্যনগর', 'slug' => 'madhyanagar', 'district_id' => $districtIds['sunamganj'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // রংপুর জেলা
            ['name' => 'রংপুর সদর', 'slug' => 'sadar-rangpur', 'district_id' => $districtIds['rangpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বদরগঞ্জ', 'slug' => 'badarganj', 'district_id' => $districtIds['rangpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'গঙ্গাচড়া', 'slug' => 'gangachara', 'district_id' => $districtIds['rangpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কাউনিয়া', 'slug' => 'kaunia', 'district_id' => $districtIds['rangpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মিঠাপুকুর', 'slug' => 'mithapukur', 'district_id' => $districtIds['rangpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'পীরগাছা', 'slug' => 'pirgachha', 'district_id' => $districtIds['rangpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'পীরগঞ্জ', 'slug' => 'pirganj-rangpur', 'district_id' => $districtIds['rangpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'তারাগঞ্জ', 'slug' => 'taraganj', 'district_id' => $districtIds['rangpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // দিনাজপুর জেলা
            ['name' => 'দিনাজপুর সদর', 'slug' => 'dinajpur-sadar', 'district_id' => $districtIds['dinajpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বিরামপুর', 'slug' => 'birampur', 'district_id' => $districtIds['dinajpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বীরগঞ্জ', 'slug' => 'birganj', 'district_id' => $districtIds['dinajpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বোচাগঞ্জ', 'slug' => 'bochaganj', 'district_id' => $districtIds['dinajpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'চিরিরবন্দর', 'slug' => 'chirirbandar', 'district_id' => $districtIds['dinajpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ফুলবাড়ী', 'slug' => 'phulbari-dinajpur', 'district_id' => $districtIds['dinajpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ঘোড়াঘাট', 'slug' => 'ghoraghat', 'district_id' => $districtIds['dinajpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'হাকিমপুর', 'slug' => 'hakimpur', 'district_id' => $districtIds['dinajpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কাহারোল', 'slug' => 'kaharol', 'district_id' => $districtIds['dinajpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'খানসামা', 'slug' => 'khansama', 'district_id' => $districtIds['dinajpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নবাবগঞ্জ', 'slug' => 'nababganj-dinajpur', 'district_id' => $districtIds['dinajpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'পার্বতীপুর', 'slug' => 'parbatipur', 'district_id' => $districtIds['dinajpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সেতাবগঞ্জ', 'slug' => 'setabganj', 'district_id' => $districtIds['dinajpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // গাইবান্ধা জেলা
            ['name' => 'গাইবান্ধা সদর', 'slug' => 'gaibandha-sadar', 'district_id' => $districtIds['gaibandha'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ফুলছড়ি', 'slug' => 'phulchhari', 'district_id' => $districtIds['gaibandha'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'গোবিন্দগঞ্জ', 'slug' => 'gobindaganj', 'district_id' => $districtIds['gaibandha'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'পলাশবাড়ী', 'slug' => 'palashbari', 'district_id' => $districtIds['gaibandha'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সাদুল্লাপুর', 'slug' => 'sadullapur', 'district_id' => $districtIds['gaibandha'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সাঘাটা', 'slug' => 'saghata', 'district_id' => $districtIds['gaibandha'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সুন্দরগঞ্জ', 'slug' => 'sundarganj', 'district_id' => $districtIds['gaibandha'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // কুড়িগ্রাম জেলা
            ['name' => 'কুড়িগ্রাম সদর', 'slug' => 'kurigram-sadar', 'district_id' => $districtIds['kurigram'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ভুরুঙ্গামারী', 'slug' => 'bhurungamari', 'district_id' => $districtIds['kurigram'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'চিলমারী', 'slug' => 'chilmari', 'district_id' => $districtIds['kurigram'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'উলিপুর', 'slug' => 'ulipur', 'district_id' => $districtIds['kurigram'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রাজারহাট', 'slug' => 'rajarhat', 'district_id' => $districtIds['kurigram'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রৌমারী', 'slug' => 'rowmari', 'district_id' => $districtIds['kurigram'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নাগেশ্বরী', 'slug' => 'nageshwari', 'district_id' => $districtIds['kurigram'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ফুলবাড়ী', 'slug' => 'phulbari-kurigram', 'district_id' => $districtIds['kurigram'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'চর রাজিবপুর', 'slug' => 'char-rajibpur', 'district_id' => $districtIds['kurigram'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // লালমনিরহাট জেলা
            ['name' => 'লালমনিরহাট সদর', 'slug' => 'lalmonirhat-sadar', 'district_id' => $districtIds['lalmonirhat'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'আদিতমারী', 'slug' => 'aditmari', 'district_id' => $districtIds['lalmonirhat'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'হাতীবান্ধা', 'slug' => 'hatibandha', 'district_id' => $districtIds['lalmonirhat'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কালীগঞ্জ', 'slug' => 'kaliganj-lalmonirhat', 'district_id' => $districtIds['lalmonirhat'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'পাটগ্রাম', 'slug' => 'patgram', 'district_id' => $districtIds['lalmonirhat'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // নীলফামারী জেলা
            ['name' => 'নীলফামারী সদর', 'slug' => 'nilphamari-sadar', 'district_id' => $districtIds['nilphamari'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ডিমলা', 'slug' => 'dimla', 'district_id' => $districtIds['nilphamari'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ডোমার', 'slug' => 'domar', 'district_id' => $districtIds['nilphamari'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'জলঢাকা', 'slug' => 'jaldhaka', 'district_id' => $districtIds['nilphamari'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কিশোরগঞ্জ', 'slug' => 'kishoreganj-nilphamari', 'district_id' => $districtIds['nilphamari'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সৈয়দপুর', 'slug' => 'saidpur', 'district_id' => $districtIds['nilphamari'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // পঞ্চগড় জেলা
            ['name' => 'পঞ্চগড় সদর', 'slug' => 'panchagarh-sadar', 'district_id' => $districtIds['panchagarh'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'আটোয়ারী', 'slug' => 'atwari', 'district_id' => $districtIds['panchagarh'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বোদা', 'slug' => 'boda', 'district_id' => $districtIds['panchagarh'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'দেবীগঞ্জ', 'slug' => 'debiganj', 'district_id' => $districtIds['panchagarh'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'তেঁতুলিয়া', 'slug' => 'tetulia', 'district_id' => $districtIds['panchagarh'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // ঠাকুরগাঁও জেলা
            ['name' => 'ঠাকুরগাঁও সদর', 'slug' => 'thakurgaon-sadar', 'district_id' => $districtIds['thakurgaon'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বালিয়াডাঙ্গী', 'slug' => 'baliadangi', 'district_id' => $districtIds['thakurgaon'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'হরিপুর', 'slug' => 'haripur', 'district_id' => $districtIds['thakurgaon'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'রানীশংকৈল', 'slug' => 'ranisankail', 'district_id' => $districtIds['thakurgaon'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'পীরগঞ্জ', 'slug' => 'pirganj-thakurgaon', 'district_id' => $districtIds['thakurgaon'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // ময়মনসিংহ জেলা
            ['name' => 'ময়মনসিংহ সদর', 'slug' => 'sadar-mymensingh', 'district_id' => $districtIds['mymensingh'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ভালুকা', 'slug' => 'valuka', 'district_id' => $districtIds['mymensingh'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'গফরগাঁও', 'slug' => 'gafargaon', 'district_id' => $districtIds['mymensingh'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'গৌরীপুর', 'slug' => 'gauripur', 'district_id' => $districtIds['mymensingh'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'হালুয়াঘাট', 'slug' => 'haluaghat', 'district_id' => $districtIds['mymensingh'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ঈশ্বরগঞ্জ', 'slug' => 'ishwarganj', 'district_id' => $districtIds['mymensingh'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মুক্তাগাছা', 'slug' => 'muktagachha', 'district_id' => $districtIds['mymensingh'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নান্দাইল', 'slug' => 'nandail', 'district_id' => $districtIds['mymensingh'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ফুলবাড়িয়া', 'slug' => 'phulbaria', 'district_id' => $districtIds['mymensingh'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ফুলপুর', 'slug' => 'fulpur', 'district_id' => $districtIds['mymensingh'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ত্রিশাল', 'slug' => 'trishal', 'district_id' => $districtIds['mymensingh'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ধোবাউড়া', 'slug' => 'dhobaura', 'district_id' => $districtIds['mymensingh'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // নেত্রকোনা জেলা
            ['name' => 'নেত্রকোনা সদর', 'slug' => 'netrokona-sadar', 'district_id' => $districtIds['netrokona'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'আটপাড়া', 'slug' => 'atpara', 'district_id' => $districtIds['netrokona'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বারহাট্টা', 'slug' => 'barhatta', 'district_id' => $districtIds['netrokona'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'দুর্গাপুর', 'slug' => 'durgapur-netrokona', 'district_id' => $districtIds['netrokona'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কলমাকান্দা', 'slug' => 'kalmakanda', 'district_id' => $districtIds['netrokona'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'কেন্দুয়া', 'slug' => 'kendua', 'district_id' => $districtIds['netrokona'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মদন', 'slug' => 'madan', 'district_id' => $districtIds['netrokona'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মোহনগঞ্জ', 'slug' => 'mohangonj', 'district_id' => $districtIds['netrokona'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'পূর্বধলা', 'slug' => 'purba-dhala', 'district_id' => $districtIds['netrokona'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'খালিয়াজুরী', 'slug' => 'khaliajuri', 'district_id' => $districtIds['netrokona'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // শেরপুর জেলা
            ['name' => 'শেরপুর সদর', 'slug' => 'sherpur-sadar', 'district_id' => $districtIds['sherpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নকলা', 'slug' => 'nakla', 'district_id' => $districtIds['sherpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'নালিতাবাড়ী', 'slug' => 'nalitabari', 'district_id' => $districtIds['sherpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'শ্রীবরদী', 'slug' => 'sreebardi', 'district_id' => $districtIds['sherpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ঝিনাইগাতী', 'slug' => 'jhinaigati', 'district_id' => $districtIds['sherpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // জামালপুর জেলা
            ['name' => 'জামালপুর সদর', 'slug' => 'jamalpur-sadar', 'district_id' => $districtIds['jamalpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'বকশীগঞ্জ', 'slug' => 'bakshiganj', 'district_id' => $districtIds['jamalpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'দেওয়ানগঞ্জ', 'slug' => 'dewanganj', 'district_id' => $districtIds['jamalpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ইসলামপুর', 'slug' => 'islampur', 'district_id' => $districtIds['jamalpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মাদারগঞ্জ', 'slug' => 'madarganj', 'district_id' => $districtIds['jamalpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'মেলান্দহ', 'slug' => 'melandah', 'district_id' => $districtIds['jamalpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'সরিষাবাড়ী', 'slug' => 'sarishabari', 'district_id' => $districtIds['jamalpur'], 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
        ];

        $thanasToInsert = [];
        $existingSlugsByDistrict = []; // To keep track of slugs already processed for a given district

        foreach ($rawThanas as $thana) {
            $originalSlug = $thana['slug'];
            $currentSlug = $originalSlug;
            $counter = 1;

            // Ensure district_id is set for the lookup
            $districtId = $thana['district_id'];

            // Initialize array for this district if not already
            if (!isset($existingSlugsByDistrict[$districtId])) {
                $existingSlugsByDistrict[$districtId] = [];
            }

            // Check for uniqueness within the same district
            while (in_array($currentSlug, $existingSlugsByDistrict[$districtId])) {
                $currentSlug = $originalSlug . '-' . $counter++;
            }

            $thana['slug'] = $currentSlug;
            $thanasToInsert[] = $thana;

            // Add the newly assigned unique slug to our tracking array
            $existingSlugsByDistrict[$districtId][] = $currentSlug;
        }

        // Insert all processed thanas
        DB::table('thanas')->insert($thanasToInsert);

        // This $thanaIds variable is not used after insertion in this seeder,
        // but can be kept if needed for future logic within the same seeder.
        // $thanaIds = [];
        // foreach (DB::table('thanas')->get() as $thana) {
        //     $thanaIds[$thana->slug] = $thana->id;
        // }
    }
}
