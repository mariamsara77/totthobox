<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FireServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Verify that the fire service category exists
        $fireCategory = DB::table('contact_categories')->where('id', 3)->orWhere('slug', 'fire-service')->first();
        if (!$fireCategory) {
            $this->command->error('Fire Service category not found! Please run ContactCategoriesSeeder first.');
            return;
        }
        $categoryId = $fireCategory->id;

        // Optional: clear existing fire service contacts
        // DB::table('contact_numbers')->where('contact_category_id', $categoryId)->delete();

        // 2. Get all districts and divisions mapping
        $districts = DB::table('districts')->pluck('division_id', 'id')->toArray();

        // 3. Get all thanas from the database
        $thanas = DB::table('thanas')->get();
        $insertData = [];

        $this->command->info("Processing Fire Service stations...");

        // 4. Prepare actual fire service mobile numbers
        $fireServiceNumbers = $this->getFireServiceMobileNumbers();

        foreach ($thanas as $thana) {
            $thanaId   = $thana->id;
            $thanaName = $thana->name;
            $districtId = $thana->district_id;
            $divisionId = $districts[$districtId] ?? null;

            // Get fire service mobile number for this thana
            $mobileNumber = $fireServiceNumbers[$thanaId] ?? null;
            $landlineNumber = $fireServiceNumbers['landline_' . $thanaId] ?? null;
            
            // If no specific number found, use national emergency number
            if (!$mobileNumber) {
                $mobileNumber = '16163';
            }

            // Generate a safe slug
            $slug = $thana->slug ?? Str::slug($thanaName);

            // Fire Station Main Contact
            $insertData[] = [
                'contact_category_id' => $categoryId,
                'division_id'         => $divisionId,
                'district_id'         => $districtId,
                'thana_id'            => $thanaId,
                'unit_name'           => $thanaName . ' ফায়ার স্টেশন',
                'name'                => 'ফায়ার স্টেশন ইনচার্জ',
                'phone'               => $mobileNumber,  // মোবাইল নম্বর এখানে
                'type'                => 'Fire Station',
                'designation'         => 'স্টেশন অফিসার',
                'alt_phone'           => $landlineNumber ?? ($mobileNumber != '16163' ? '16163' : null), // ল্যান্ডলাইন এখানে
                'email'               => 'fire.' . $slug . '@fireservice.gov.bd',
                'address'             => $thanaName . ' ফায়ার স্টেশন, ' . $thanaName . ', বাংলাদেশ',
                'status'              => 'active',
                'is_active'           => 1,
                'is_featured'         => 1,
                'created_at'          => now(),
                'updated_at'          => now(),
            ];

            // Emergency Control Room (for larger stations)
            if ($this->hasControlRoom($thanaId)) {
                $controlMobile = $fireServiceNumbers['control_' . $thanaId] ?? $mobileNumber;
                $controlLandline = $fireServiceNumbers['control_landline_' . $thanaId] ?? $landlineNumber;
                
                $insertData[] = [
                    'contact_category_id' => $categoryId,
                    'division_id'         => $divisionId,
                    'district_id'         => $districtId,
                    'thana_id'            => $thanaId,
                    'unit_name'           => $thanaName . ' ফায়ার স্টেশন',
                    'name'                => 'কন্ট্রোল রুম',
                    'phone'               => $controlMobile,  // মোবাইল নম্বর
                    'type'                => 'Control Room',
                    'designation'         => 'কন্ট্রোল রুম অফিসার',
                    'alt_phone'           => $controlLandline, // ল্যান্ডলাইন
                    'email'               => 'control.' . $slug . '@fireservice.gov.bd',
                    'address'             => $thanaName . ' ফায়ার স্টেশন, ' . $thanaName . ', বাংলাদেশ',
                    'status'              => 'active',
                    'is_active'           => 1,
                    'is_featured'         => 0,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ];
            }
        }

        // 5. Add district-level fire service offices
        $this->command->info("Adding district-level fire service offices...");
        
        $districtsList = DB::table('districts')->get();

        foreach ($districtsList as $district) {
            $districtId = $district->id;
            $divisionId = $districts[$districtId] ?? null;
            $districtName = $district->name;
            $slug = $district->slug ?? Str::slug($districtName);
            
            $districtMobile = $fireServiceNumbers['district_' . $districtId] ?? '16163';
            $districtLandline = $fireServiceNumbers['district_landline_' . $districtId] ?? null;

            // District Fire Service Office
            $insertData[] = [
                'contact_category_id' => $categoryId,
                'division_id'         => $divisionId,
                'district_id'         => $districtId,
                'thana_id'            => null,
                'unit_name'           => 'জেলা ফায়ার সার্ভিস অফিস, ' . $districtName,
                'name'                => 'জেলা ফায়ার সার্ভিস অফিসার',
                'phone'               => $districtMobile,  // মোবাইল নম্বর
                'type'                => 'District Office',
                'designation'         => 'জেলা ফায়ার সার্ভিস অফিসার',
                'alt_phone'           => $districtLandline, // ল্যান্ডলাইন
                'email'               => 'district.' . $slug . '@fireservice.gov.bd',
                'address'             => 'ফায়ার সার্ভিস অফিস, ' . $districtName,
                'status'              => 'active',
                'is_active'           => 1,
                'is_featured'         => 0,
                'created_at'          => now(),
                'updated_at'          => now(),
            ];
        }

        // 6. Add divisional-level fire service offices
        $this->command->info("Adding divisional-level fire service offices...");
        
        $divisions = DB::table('divisions')->get();

        foreach ($divisions as $division) {
            $divisionId = $division->id;
            $divisionName = $division->name;
            $slug = $division->slug ?? Str::slug($divisionName);
            
            $divisionMobile = $fireServiceNumbers['division_' . $divisionId] ?? '16163';
            $divisionLandline = $fireServiceNumbers['division_landline_' . $divisionId] ?? null;

            // Divisional Fire Service Office
            $insertData[] = [
                'contact_category_id' => $categoryId,
                'division_id'         => $divisionId,
                'district_id'         => null,
                'thana_id'            => null,
                'unit_name'           => 'বিভাগীয় ফায়ার সার্ভিস অফিস, ' . $divisionName,
                'name'                => 'বিভাগীয় ফায়ার সার্ভিস অফিসার',
                'phone'               => $divisionMobile,  // মোবাইল নম্বর
                'type'                => 'Divisional Office',
                'designation'         => 'বিভাগীয় ফায়ার সার্ভিস অফিসার',
                'alt_phone'           => $divisionLandline, // ল্যান্ডলাইন
                'email'               => 'division.' . $slug . '@fireservice.gov.bd',
                'address'             => 'ফায়ার সার্ভিস বিভাগীয় অফিস, ' . $divisionName,
                'status'              => 'active',
                'is_active'           => 1,
                'is_featured'         => 0,
                'created_at'          => now(),
                'updated_at'          => now(),
            ];
        }

        // 7. Add national headquarters and helpline
        $this->command->info("Adding national headquarters...");
        
        // National DG Office
        $insertData[] = [
            'contact_category_id' => $categoryId,
            'division_id'         => null,
            'district_id'         => null,
            'thana_id'            => null,
            'unit_name'           => 'ফায়ার সার্ভিস ও সিভিল ডিফেন্স সদর দপ্তর',
            'name'                => 'মহাপরিচালক',
            'phone'               => '01777777777',  // মোবাইল নম্বর
            'type'                => 'Headquarters',
            'designation'         => 'মহাপরিচালক',
            'alt_phone'           => '02-9355555, 02-9354444', // ল্যান্ডলাইন
            'email'               => 'dg@fireservice.gov.bd',
            'address'             => 'ফায়ার সার্ভিস সদর দপ্তর, 61-63 মতিঝিল, ঢাকা',
            'status'              => 'active',
            'is_active'           => 1,
            'is_featured'         => 1,
            'created_at'          => now(),
            'updated_at'          => now(),
        ];

        // National Emergency Helpline
        $insertData[] = [
            'contact_category_id' => $categoryId,
            'division_id'         => null,
            'district_id'         => null,
            'thana_id'            => null,
            'unit_name'           => 'জাতীয় জরুরি সেবা',
            'name'                => 'ফায়ার সার্ভিস ন্যাশনাল হেল্পলাইন',
            'phone'               => '16163',  // শর্ট কোড
            'type'                => 'National Helpline',
            'designation'         => 'ন্যাশনাল ইমার্জেন্সি হেল্পলাইন',
            'alt_phone'           => '02-9555555', // ল্যান্ডলাইন
            'email'               => 'help@fireservice.gov.bd',
            'address'             => 'ফায়ার সার্ভিস ও সিভিল ডিফেন্স, ঢাকা',
            'status'              => 'active',
            'is_active'           => 1,
            'is_featured'         => 1,
            'created_at'          => now(),
            'updated_at'          => now(),
        ];

        // 8. Insert all records in chunks
        $chunks = array_chunk($insertData, 100);
        $totalInserted = 0;

        foreach ($chunks as $chunk) {
            $totalInserted += DB::table('contact_numbers')->insertOrIgnore($chunk);
        }

        $this->command->info("Successfully inserted {$totalInserted} fire service contact records.");
        $this->command->warn("Note: Phone column contains mobile numbers, Alt_phone contains landline numbers where available.");
        $this->command->info("Default number 16163 (national emergency) is set for stations without specific numbers.");
    }

    /**
     * Check if a thana should have a dedicated control room
     */
    private function hasControlRoom($thanaId): bool
    {
        // Major fire stations that have dedicated control rooms
        $controlRoomThanas = [
            // Dhaka city major stations
            8, 9, 10, 11, 17, 20, 24, 34, 35, 41, 43,
            // Divisional cities
            128, 141, 144, 148, 314, 323, 324, 420, 458,
        ];
        
        return in_array($thanaId, $controlRoomThanas);
    }

    /**
     * Get actual fire service mobile numbers
     * 
     * Format: 
     * - [thana_id] => 'mobile_number'
     * - ['landline_thana_id'] => 'landline_number'
     * - ['control_thana_id'] => 'control_room_mobile'
     * - ['district_X'] => 'district_mobile'
     * - ['division_X'] => 'division_mobile'
     * 
     * IMPORTANT: These are sample/demo numbers. 
     * You MUST replace these with actual official numbers from:
     * Bangladesh Fire Service & Civil Defence
     * Website: fireservice.gov.bd
     */
    private function getFireServiceMobileNumbers(): array
    {
        return [
            // ========== DHAKA METROPOLITAN ==========
            // Shahbag, Ramna, Paltan area
            8 => '01769-123456',      // Shahbag স্টেশনের মোবাইল
            9 => '01769-123457',      // Ramna স্টেশনের মোবাইল
            10 => '01769-123458',     // Paltan স্টেশনের মোবাইল
            11 => '01769-123459',     // Motijheel স্টেশনের মোবাইল
            17 => '01769-123460',     // Lalbag স্টেশনের মোবাইল
            
            // Landline numbers for these stations
            'landline_8' => '02-9355555',
            'landline_9' => '02-9354444',
            'landline_10' => '02-9555555',
            'landline_11' => '02-9556666',
            'landline_17' => '02-9561234',
            
            // Control room numbers
            'control_8' => '01769-123461',
            'control_9' => '01769-123462',
            'control_10' => '01769-123463',
            'control_11' => '01769-123464',
            'control_17' => '01769-123465',
            
            'control_landline_8' => '02-9356666',
            'control_landline_9' => '02-9357777',
            'control_landline_10' => '02-9557777',
            'control_landline_11' => '02-9558888',
            
            // Mohammadpur, Mirpur, Uttara
            20 => '01769-123466',     // Mohammadpur
            24 => '01769-123467',     // Mirpur
            34 => '01769-123468',     // Uttara West
            35 => '01769-123469',     // Uttara East
            41 => '01769-123470',     // Gulshan
            43 => '01769-123471',     // Dhanmondi
            
            'landline_20' => '02-8125555',
            'landline_24' => '02-9015555',
            'landline_34' => '02-8915555',
            'landline_35' => '02-8925555',
            'landline_41' => '02-9885555',
            'landline_43' => '02-9675555',
            
            // ========== CHITTAGONG METROPOLITAN ==========
            141 => '01819-123456',    // Karnaphuli
            144 => '01819-123457',    // Kotwali
            148 => '01819-123458',    // Double Mooring
            
            'landline_141' => '031-611555',
            'landline_144' => '031-620555',
            'landline_148' => '031-650555',
            
            'control_141' => '01819-123459',
            'control_144' => '01819-123460',
            'control_148' => '01819-123461',
            
            // ========== KHULNA METROPOLITAN ==========
            323 => '01919-123456',    // Khalishpur
            324 => '01919-123457',    // Sonadanga
            
            'landline_323' => '041-721555',
            'landline_324' => '041-722555',
            
            // ========== SYLHET ==========
            420 => '01717-123456',    // Sylhet Sadar
            'landline_420' => '0821-715555',
            
            // ========== RANGPUR ==========
            458 => '01718-123456',    // Rangpur Sadar
            'landline_458' => '0521-61155',
            
            // ========== DISTRICT LEVEL OFFICES ==========
            'district_1' => '01769-123472',      // Dhaka
            'district_landline_1' => '02-9355555',
            
            'district_44' => '01769-123473',     // Gazipur
            'district_landline_44' => '02-9295555',
            
            'district_49' => '01769-123474',     // Narayanganj
            'district_landline_49' => '02-7645555',
            
            'district_54' => '01769-123475',     // Tangail
            'district_landline_54' => '0921-52555',
            
            'district_128' => '01819-123462',    // Chattogram
            'district_landline_128' => '031-620555',
            
            'district_154' => '01819-123463',    // Cox's Bazar
            'district_landline_154' => '0341-52555',
            
            'district_314' => '01919-123458',    // Khulna
            'district_landline_314' => '041-720555',
            
            'district_420' => '01717-123457',    // Sylhet
            'district_landline_420' => '0821-714555',
            
            'district_458' => '01718-123457',    // Rangpur
            'district_landline_458' => '0521-61055',
            
            'district_378' => '01720-123456',    // Rajshahi
            'district_landline_378' => '051-61155',
            
            'district_302' => '01721-123456',    // Barishal
            'district_landline_302' => '0421-61155',
            
            'district_394' => '01722-123456',    // Mymensingh
            'district_landline_394' => '071-61155',
            
            // ========== DIVISION LEVEL OFFICES ==========
            'division_1' => '01769-123476',      // Dhaka Division
            'division_landline_1' => '02-9355555',
            
            'division_2' => '01819-123464',      // Chattogram Division
            'division_landline_2' => '031-611555',
            
            'division_3' => '01919-123459',      // Khulna Division
            'division_landline_3' => '041-720555',
            
            'division_4' => '01720-123457',      // Rajshahi Division
            'division_landline_4' => '0721-61155',
            
            'division_5' => '01717-123458',      // Sylhet Division
            'division_landline_5' => '0821-714555',
            
            'division_6' => '01718-123458',      // Rangpur Division
            'division_landline_6' => '0521-61055',
            
            'division_7' => '01721-123457',      // Barishal Division
            'division_landline_7' => '0431-61155',
            
            'division_8' => '01722-123457',      // Mymensingh Division
            'division_landline_8' => '091-61155',
        ];
    }
}