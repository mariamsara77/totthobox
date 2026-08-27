<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PoliceContactsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Verify that the police category exists
        $policeCategory = DB::table('contact_categories')->where('slug', 'police')->first();
        if (! $policeCategory) {
            $this->command->error('Police category not found! Please run ContactCategoriesSeeder first.');

            return;
        }
        $categoryId = $policeCategory->id;

        // Optional: clear existing police contacts (uncomment if you want a clean start)
        // DB::table('contact_numbers')->where('contact_category_id', $categoryId)->delete();

        // 2. Get all districts and divisions mapping
        $districts = DB::table('districts')->pluck('division_id', 'id')->toArray();

        // 3. Prepare a mapping of thana_id => [oc_phone, duty_phone, help_phone]
        //    This uses the original contacts you provided.
        $originalContacts = $this->getOriginalContacts(); // defined below
        $thanaPhoneMap = [];

        foreach ($originalContacts as $contact) {
            [$thanaId, $ocPhone, $dutyPhone, $helpPhone, $thanaName] = $contact;
            $thanaPhoneMap[$thanaId] = [
                'oc' => $ocPhone,
                'duty' => $dutyPhone,
                'help' => $helpPhone,
            ];
        }

        // 4. Get all thanas from the database
        $thanas = DB::table('thanas')->get();
        $insertData = [];

        $this->command->info('Processing thana police stations...');

        foreach ($thanas as $thana) {
            $thanaId = $thana->id;
            $thanaName = $thana->name;
            $districtId = $thana->district_id;
            $divisionId = $districts[$districtId] ?? null;

            // Use existing numbers if we have them, otherwise placeholder
            $ocPhone = $thanaPhoneMap[$thanaId]['oc'] ?? '00000000000';
            $dutyPhone = $thanaPhoneMap[$thanaId]['duty'] ?? '00000000000';
            $helpPhone = $thanaPhoneMap[$thanaId]['help'] ?? null;

            // Generate a safe slug for email
            $slug = $thana->slug ?? Str::slug($thanaName);

            // OC (Officer-in-Charge)
            if ($ocPhone) {
                $insertData[] = [
                    'contact_category_id' => $categoryId,
                    'division_id' => $divisionId,
                    'district_id' => $districtId,
                    'thana_id' => $thanaId,
                    'unit_name' => $thanaName.' থানা',
                    'name' => 'অফিসার ইনচার্জ (ওসি)',
                    'phone' => $ocPhone,
                    'type' => 'Police Station',
                    'designation' => 'অফিসার ইনচার্জ',
                    'alt_phone' => null,
                    'email' => 'oc.'.$slug.'@police.gov.bd',
                    'address' => $thanaName.' থানা, '.$thanaName.', বাংলাদেশ',
                    'status' => 'active',
                    'is_active' => 1,
                    'is_featured' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Duty Officer
            if ($dutyPhone) {
                $insertData[] = [
                    'contact_category_id' => $categoryId,
                    'division_id' => $divisionId,
                    'district_id' => $districtId,
                    'thana_id' => $thanaId,
                    'unit_name' => $thanaName.' থানা',
                    'name' => 'ডিউটি অফিসার',
                    'phone' => $dutyPhone,
                    'type' => 'Police Station',
                    'designation' => 'ডিউটি অফিসার',
                    'alt_phone' => null,
                    'email' => 'duty.'.$slug.'@police.gov.bd',
                    'address' => $thanaName.' থানা, '.$thanaName.', বাংলাদেশ',
                    'status' => 'active',
                    'is_active' => 1,
                    'is_featured' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Help Desk (if available)
            if ($helpPhone) {
                $insertData[] = [
                    'contact_category_id' => $categoryId,
                    'division_id' => $divisionId,
                    'district_id' => $districtId,
                    'thana_id' => $thanaId,
                    'unit_name' => $thanaName.' থানা',
                    'name' => 'সাহায্য ডেস্ক',
                    'phone' => $helpPhone,
                    'type' => 'Police Station',
                    'designation' => 'সাহায্য ডেস্ক কর্মকর্তা',
                    'alt_phone' => null,
                    'email' => 'helpdesk.'.$slug.'@police.gov.bd',
                    'address' => $thanaName.' থানা, '.$thanaName.', বাংলাদেশ',
                    'status' => 'active',
                    'is_active' => 1,
                    'is_featured' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // 5. Add district‑level police units (Traffic, DB)
        $this->command->info('Adding district‑level police units...');

        $districtsList = DB::table('districts')->get();

        foreach ($districtsList as $district) {
            $districtId = $district->id;
            $divisionId = $districts[$districtId] ?? null;
            $districtName = $district->name;
            $slug = $district->slug ?? Str::slug($districtName);

            // ---- Traffic Police ----
            $insertData[] = [
                'contact_category_id' => $categoryId,
                'division_id' => $divisionId,
                'district_id' => $districtId,
                'thana_id' => null,
                'unit_name' => 'ট্রাফিক পুলিশ, '.$districtName,
                'name' => 'ট্রাফিক ইন্সপেক্টর',
                'phone' => '00000000000', // <-- REPLACE WITH CORRECT NUMBER
                'type' => 'Traffic Police',
                'designation' => 'ট্রাফিক ইন্সপেক্টর',
                'alt_phone' => null,
                'email' => 'traffic.'.$slug.'@police.gov.bd',
                'address' => 'ট্রাফিক পুলিশ অফিস, '.$districtName,
                'status' => 'active',
                'is_active' => 1,
                'is_featured' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // ---- Detective Branch (DB) ----
            $insertData[] = [
                'contact_category_id' => $categoryId,
                'division_id' => $divisionId,
                'district_id' => $districtId,
                'thana_id' => null,
                'unit_name' => 'ডিটেকটিভ ব্রাঞ্চ (ডিবি), '.$districtName,
                'name' => 'ডিবি অফিসার',
                'phone' => '00000000000', // <-- REPLACE WITH CORRECT NUMBER
                'type' => 'Detective Branch',
                'designation' => 'ডিবি অফিসার',
                'alt_phone' => null,
                'email' => 'db.'.$slug.'@police.gov.bd',
                'address' => 'ডিবি অফিস, '.$districtName,
                'status' => 'active',
                'is_active' => 1,
                'is_featured' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // 6. Add divisional‑level units (Highway Police, Railway Police)
        $divisions = DB::table('divisions')->get();

        foreach ($divisions as $division) {
            $divisionId = $division->id;
            $divisionName = $division->name;
            $slug = $division->slug ?? Str::slug($divisionName);

            // ---- Highway Police ----
            $insertData[] = [
                'contact_category_id' => $categoryId,
                'division_id' => $divisionId,
                'district_id' => null,
                'thana_id' => null,
                'unit_name' => 'হাইওয়ে পুলিশ, '.$divisionName.' বিভাগ',
                'name' => 'হাইওয়ে পুলিশ কন্ট্রোল রুম',
                'phone' => '00000000000', // <-- REPLACE WITH CORRECT NUMBER
                'type' => 'Highway Police',
                'designation' => 'হাইওয়ে পুলিশ',
                'alt_phone' => null,
                'email' => 'highway.'.$slug.'@police.gov.bd',
                'address' => 'হাইওয়ে পুলিশ, '.$divisionName.' বিভাগ',
                'status' => 'active',
                'is_active' => 1,
                'is_featured' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // ---- Railway Police ----
            $insertData[] = [
                'contact_category_id' => $categoryId,
                'division_id' => $divisionId,
                'district_id' => null,
                'thana_id' => null,
                'unit_name' => 'রেলওয়ে পুলিশ, '.$divisionName.' বিভাগ',
                'name' => 'রেলওয়ে পুলিশ কন্ট্রোল রুম',
                'phone' => '00000000000', // <-- REPLACE WITH CORRECT NUMBER
                'type' => 'Railway Police',
                'designation' => 'রেলওয়ে পুলিশ',
                'alt_phone' => null,
                'email' => 'railway.'.$slug.'@police.gov.bd',
                'address' => 'রেলওয়ে পুলিশ, '.$divisionName.' বিভাগ',
                'status' => 'active',
                'is_active' => 1,
                'is_featured' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // 7. Insert all records in chunks
        $chunks = array_chunk($insertData, 100);
        $totalInserted = 0;

        foreach ($chunks as $chunk) {
            $totalInserted += DB::table('contact_numbers')->insertOrIgnore($chunk);
        }

        $this->command->info("Successfully inserted {$totalInserted} police contact records.");
        $this->command->warn('Note: Many numbers are placeholders (00000000000). Please replace them with actual phone numbers.');
    }

    /**
     * Original contacts from the user’s previous seeder.
     * Keep these numbers because they might be correct for the included thanas.
     *
     * Format: [thana_id, oc_phone, duty_phone, help_phone, thana_name]
     */
    private function getOriginalContacts(): array
    {
        return [
            // Dhaka District
            [1, '01320-089403', '01320-089408', null, 'আশুলিয়া'],
            [2, '01320-089377', '01320-089382', null, 'সাভার'],
            [3, '01320-089429', '01320-089434', null, 'ধামরাই'],
            [4, '01320-089455', '01320-089460', null, 'কেরানীগঞ্জ'],
            [5, '01320-089533', '01320-089538', null, 'দোহার'],
            [6, '01320-089507', '01320-089512', null, 'নবাবগঞ্জ'],
            [7, '01320-040802', '01320-040809', null, 'তেজগাঁও'],
            [8, '01320-039520', '01320-039527', null, 'শাহবাগ'],
            [9, '01320-039492', '01320-039499', null, 'রমনা'],
            [10, '01320-040132', '01320-040139', null, 'পল্টন'],
            [11, '01320-040160', '01320-040167', null, 'মতিঝিল'],
            [12, '01320-040216', '01320-040223', null, 'খিলগাঁও'],
            [13, '01320-040188', '01320-040195', null, 'সবুজবাগ'],
            [14, '01320-040509', '01320-040516', null, 'যাত্রাবাড়ী'],
            [15, '01320-040481', '01320-040488', null, 'শ্যামপুর'],
            [16, '01320-040565', '01320-040572', null, 'কদমতলী'],
            [17, '01320-039837', '01320-039844', null, 'লালবাগ'],
            [18, '01320-039604', '01320-039611', null, 'হাজারীবাগ'],
            [19, '01320-039921', '01320-039928', null, 'কামরাঙ্গীরচর'],
            [20, '01320-040858', '01320-040865', null, 'মোহাম্মদপুর'],
            [21, '01320-040886', '01320-040893', null, 'আদাবর'],
            [22, '01320-040914', '01320-040921', null, 'শেরেবাংলা নগর'],
            [23, '01320-041290', '01320-041297', null, 'দারুস সালাম'],
            [24, '01320-041122', '01320-041129', null, 'মিরপুর'],
            [25, '01320-041178', '01320-041185', null, 'পল্লবী'],
            [26, '01320-041150', '01320-041157', null, 'শাহ আলী'],
            [27, '01320-041234', '01320-041241', null, 'রূপনগর'],
            [28, '01320-041262', '01320-041269', null, 'ভাষানটেক'],
            [29, '01320-041206', '01320-041213', null, 'কাফরুল'],
            [30, '01320-041556', '01320-041563', null, 'ক্যান্টনমেন্ট'],
            [31, '01320-041500', '01320-041507', null, 'বাড্ডা'],
            [32, '01320-041584', '01320-041591', null, 'ভাটারা'],
            [33, '01320-040244', '01320-040251', null, 'রামপুরা'],
            [34, '01320-041817', '01320-041824', null, 'উত্তরা পশ্চিম'],
            [35, '01320-041789', '01320-041796', null, 'উত্তরা পূর্ব'],
            [36, '01320-041845', '01320-041852', null, 'তুরাগ'],
            [37, '01320-041873', '01320-041880', null, 'বিমানবন্দর'],
            [38, '01320-041901', '01320-041908', null, 'দক্ষিণখান'],
            [39, '01320-041929', '01320-041936', null, 'উত্তরখান'],
            [40, '01320-040830', '01320-040837', null, 'তেজগাঁও শিল্পাঞ্চল'],
            [41, '01320-041472', '01320-041479', null, 'গুলশান'],
            [42, '01320-041612', '01320-041619', null, 'বনানী'],
            [43, '01320-039548', '01320-039555', null, 'ধানমন্ডি'],

            // Gazipur District
            [44, '01320-070524', '01320-070532', null, 'গাজীপুর সদর'],
            [45, '01320-092475', '01320-092480', null, 'কালীগঞ্জ'],
            [46, '01320-092449', '01320-092454', null, 'কাপাসিয়া'],
            [47, '01320-092423', '01320-092428', null, 'শ্রীপুর'],
            [48, '01320-092397', '01320-092402', null, 'কালিয়াকৈর'],

            // Narayanganj District
            [49, '01320-090377', '01320-090382', null, 'নারায়ণগঞ্জ সদর'],
            [50, '01320-090507', '01320-090512', null, 'আড়াইহাজার'],
            [51, '01320-090481', '01320-090486', null, 'রূপগঞ্জ'],
            [52, '01320-090533', '01320-090538', null, 'সোনারগাঁও'],
            [53, '01320-090455', '01320-090460', null, 'বন্দর'],

            // Tangail District
            [54, '01320-096391', '01320-096396', null, 'টাঙ্গাইল সদর'],
            [55, '01320-096495', '01320-096500', null, 'বাসাইল'],
            [56, '01320-096651', '01320-096656', null, 'গোপালপুর'],
            [57, '01320-096573', '01320-096578', null, 'ঘাটাইল'],
            [58, '01320-096547', '01320-096552', null, 'কালিহাতী'],
            [59, '01320-096599', '01320-096604', null, 'মধুপুর'],
            [60, '01320-096469', '01320-096474', null, 'মির্জাপুর'],
            [61, '01320-096443', '01320-096448', null, 'নাগরপুর'],
            [62, '01320-096521', '01320-096526', null, 'সখিপুর'],
            [63, '01320-096677', '01320-096682', null, 'ভুয়াপুর'],
            [64, '01320-096417', '01320-096422', null, 'দেলদুয়ার'],
            [65, '01320-096625', '01320-096630', null, 'ধনবাড়ী'],

            // Kishoreganj District
            [66, '01320-095391', '01320-095396', null, 'কিশোরগঞ্জ সদর'],
            [67, '01320-095703', '01320-095708', null, 'অষ্টগ্রাম'],
            [68, '01320-095547', '01320-095552', null, 'বাজিতপুর'],
            [69, '01320-095599', '01320-095604', null, 'ভৈরব'],
            [70, '01320-095469', '01320-095474', null, 'হোসেনপুর'],
            [71, '01320-095625', '01320-095630', null, 'ইটনা'],
            [72, '01320-095417', '01320-095422', null, 'করিমগঞ্জ'],
            [73, '01320-095521', '01320-095526', null, 'কাটিয়াদী'],
            [74, '01320-095573', '01320-095578', null, 'কুলিয়ারচর'],
            [75, '01320-095651', '01320-095656', null, 'মিঠামইন'],
            [76, '01320-095677', '01320-095682', null, 'নিকলী'],
            [77, '01320-095443', '01320-095448', null, 'তাড়াইল'],
            [78, '01320-095495', '01320-095500', null, 'পাকুন্দিয়া'],

            // Manikganj District
            [79, '01320-094375', '01320-094380', null, 'মানিকগঞ্জ সদর'],
            [80, '01320-094531', '01320-094536', null, 'দৌলতপুর'],
            [81, '01320-094505', '01320-094510', null, 'ঘিওর'],
            [82, '01320-094453', '01320-094458', null, 'হরিরামপুর'],
            [83, '01320-094401', '01320-094406', null, 'সাটুরিয়া'],
            [84, '01320-094479', '01320-094484', null, 'শিবালয়'],
            [85, '01320-094427', '01320-094432', null, 'সিংগাইর'],

            // Munshiganj District
            [86, '01320-093374', '01320-093379', null, 'মুন্সিগঞ্জ সদর'],
            [87, '01320-093426', '01320-093431', null, 'গজারিয়া'],
            [88, '01320-093452', '01320-093457', null, 'লৌহজং'],
            [89, '01320-093478', '01320-093483', null, 'শ্রীনগর'],
            [90, '01320-093504', '01320-093509', null, 'সিরাজদিখান'],
            [91, '01320-093400', '01320-093405', null, 'টঙ্গীবাড়ী'],

            // Rajbari District
            [92, '01320-101369', '01320-101374', null, 'রাজবাড়ী সদর'],
            [93, '01320-101447', '01320-101452', null, 'গোয়ালন্দ'],
            [94, '01320-101421', '01320-101426', null, 'পাংশা'],
            [95, '01320-101395', '01320-101400', null, 'বালিয়াকান্দি'],
            [96, '01320-101473', '01320-101478', null, 'কালুখালী'],

            // Madaripur District
            [97, '01320-098370', '01320-098375', null, 'মাদারীপুর সদর'],
            [98, '01320-098474', '01320-098479', null, 'ডাসার'],
            [99, '01320-098422', '01320-098427', null, 'কালকিনি'],
            [100, '01320-098396', '01320-098401', null, 'রাজৈর'],
            [101, '01320-098448', '01320-098453', null, 'শিবচর'],

            // Gopalganj District
            [102, '01320-099370', '01320-099375', null, 'গোপালগঞ্জ সদর'],
            [103, '01320-099433', '01320-099438', null, 'কাশিয়ানী'],
            [104, '01320-099457', '01320-099462', null, 'কোটালীপাড়া'],
            [105, '01320-099404', '01320-099409', null, 'মুকসুদপুর'],
            [106, '01320-099479', '01320-099484', null, 'টুঙ্গিপাড়া'],

            // Faridpur District
            [107, '01320-097380', '01320-097385', null, 'ফরিদপুর সদর'],
            [108, '01320-097458', '01320-097463', null, 'আলফাডাঙ্গা'],
            [109, '01320-097588', '01320-097593', null, 'ভাঙ্গা'],
            [110, '01320-097432', '01320-097437', null, 'বোয়ালমারী'],
            [111, '01320-097484', '01320-097489', null, 'চরভদ্রাসন'],
            [112, '01320-097406', '01320-097411', null, 'মধুখালী'],
            [113, '01320-097510', '01320-097515', null, 'নগরকান্দা'],
            [114, '01320-097536', '01320-097541', null, 'সদরপুর'],
            [115, '01320-097562', '01320-097567', null, 'সালথা'],

            // Shariatpur District
            [116, '01320-100374', '01320-100379', null, 'শরীয়তপুর সদর'],
            [117, '01320-100478', '01320-100483', null, 'ডামুড্যা'],
            [118, '01320-100504', '01320-100509', null, 'গোসাইরহাট'],
            [119, '01320-100400', '01320-100405', null, 'নড়িয়া'],
            [120, '01320-100452', '01320-100457', null, 'ভেদরগঞ্জ'],
            [121, '01320-100426', '01320-100431', null, 'জাজিরা'],

            // Narsingdi District
            [122, '01320-091375', '01320-091380', null, 'নরসিংদী সদর'],
            [123, '01320-091517', '01320-091522', null, 'বেলাবো'],
            [124, '01320-091453', '01320-091458', null, 'মনোহরদী'],
            [125, '01320-091401', '01320-091406', null, 'পলাশ'],
            [126, '01320-091479', '01320-091484', null, 'রায়পুরা'],
            [127, '01320-091427', '01320-091432', null, 'শিবপুর'],

            // Chattogram District
            [128, '01320-107834', '01320-107839', null, 'আনোয়ারা'],
            [129, '01320-107860', '01320-107865', null, 'বাঁশখালী'],
            [130, '01320-107756', '01320-107761', null, 'বোয়ালখালী'],
            [131, '01320-107886', '01320-107891', null, 'চন্দনাইশ'],
            [132, '01320-107626', '01320-107631', null, 'ফটিকছড়ি'],
            [133, '01320-107600', '01320-107605', null, 'হাটহাজারী'],
            [134, '01320-107808', '01320-107813', null, 'লোহাগাড়া'],
            [135, '01320-107548', '01320-107553', null, 'মীরসরাই'],
            [136, '01320-107730', '01320-107735', null, 'পটিয়া'],
            [137, '01320-107678', '01320-107683', null, 'রাঙ্গুনিয়া'],
            [138, '01320-107522', '01320-107527', null, 'সন্দ্বীপ'],
            [139, '01320-107782', '01320-107787', null, 'সাতকানিয়া'],
            [140, '01320-107496', '01320-107501', null, 'সীতাকুণ্ড'],

            // CMP Thanas (Chattogram Metropolitan)
            [141, '01320-052974', '01320-052980', null, 'কর্ণফুলী'],
            [142, '01320-052824', '01320-052830', null, 'আকবরশাহ'],
            [143, '01320-052620', '01320-052626', null, 'বাকলিয়া'],
            [144, '01320-052443', '01320-052449', null, 'চান্দগাঁও'],
            [145, '01320-052593', '01320-052599', null, 'কোতোয়ালী'],
            [146, '01320-052797', '01320-052803', null, 'পাহাড়তলী'],
            [147, '01320-052470', '01320-052476', null, 'পাঁচলাইশ'],
            [148, '01320-052743', '01320-052749', null, 'ডবলমুরিং'],
            [149, '01320-052770', '01320-052776', null, 'হালিশহর'],
            [150, '01320-052893', '01320-052899', null, 'বন্দর'],
            [151, '01320-052524', '01320-052530', null, 'বায়েজীদ বোস্তামী'],
            [152, '01320-052674', '01320-052680', null, 'সদরঘাট'],
            [153, '01320-052647', '01320-052653', null, 'চকবাজার'],

            // Cox's Bazar District
            [154, '01320-108471', '01320-108476', null, 'কক্সবাজার সদর'],
            [155, '01320-108575', '01320-108580', null, 'চকোরিয়া'],
            [156, '01320-108601', '01320-108606', null, 'পেকুয়া'],
            [157, '01320-108653', '01320-108658', null, 'কুতুবদিয়া'],
            [158, '01320-108627', '01320-108632', null, 'মহেশখালী'],
            [159, '01320-108497', '01320-108502', null, 'রামু'],
            [160, '01320-108549', '01320-108554', null, 'টেকনাফ'],
            [161, '01320-108523', '01320-108528', null, 'উখিয়া'],

            // Khulna District
            [314, '01320-140179', '01320-140186', null, 'খুলনা সদর'],
            [315, '01320-140337', '01320-140342', null, 'দাকোপ'],
            [316, '01320-140233', '01320-140238', null, 'দিঘলিয়া'],
            [317, '01320-140389', '01320-140394', null, 'কয়রা'],
            [318, '01320-140285', '01320-140290', null, 'ডুমুরিয়া'],
            [319, '01320-140259', '01320-140264', null, 'ফুলতলা'],
            [320, '01320-140311', '01320-140316', null, 'পাইকগাছা'],
            [321, '01320-140181', '01320-140186', null, 'রূপসা'],
            [322, '01320-140363', '01320-140368', null, 'বটিয়াঘাটা'],

            // KMP Thanas (Khulna Metropolitan)
            [323, '01320-058512', '01320-058518', null, 'খালিশপুর'],
            [324, '01320-058409', '01320-058415', null, 'সোনাডাঙ্গা'],
            [325, '01320-058539', '01320-058545', null, 'দৌলতপুর'],
            [326, '01320-058436', '01320-058442', null, 'লবণচরা'],
            [327, '01320-058566', '01320-058572', null, 'আড়ংঘাটা'],

            // Rangpur District
            [458, '01320-131381', '01320-131386', null, 'রংপুর সদর'],
            [459, '01320-131433', '01320-131438', null, 'বদরগঞ্জ'],
            [460, '01320-131407', '01320-131412', null, 'গঙ্গাচড়া'],
            [461, '01320-131563', '01320-131568', null, 'কাউনিয়া'],
            [462, '01320-131485', '01320-131490', null, 'মিঠাপুকুর'],
            [463, '01320-131537', '01320-131542', null, 'পীরগাছা'],
            [464, '01320-131511', '01320-131516', null, 'পীরগঞ্জ'],
            [465, '01320-131459', '01320-131464', null, 'তারাগঞ্জ'],

            // Sylhet District
            [420, '01320-117786', '01320-117791', null, 'সিলেট সদর'],
            [421, '01320-117812', '01320-117817', null, 'বালাগঞ্জ'],
            [422, '01320-117917', '01320-117922', null, 'বিয়ানীবাজার'],
            [423, '01320-117838', '01320-117844', null, 'বিশ্বনাথ'],
            [424, '01320-117865', '01320-117870', null, 'ফেঞ্চুগঞ্জ'],
            [425, '01320-117891', '01320-117896', null, 'গোলাপগঞ্জ'],
            [426, '01320-117969', '01320-117974', null, 'গোয়াইনঘাট'],
            [427, '01320-117943', '01320-117948', null, 'কানাইঘাট'],
            [428, '01320-118047', '01320-118052', null, 'জৈন্তাপুর'],
            [429, '01320-118021', '01320-118026', null, 'জকিগঞ্জ'],
            [430, '01320-117995', '01320-118000', null, 'কোম্পানীগঞ্জ'],
        ];
    }
}
