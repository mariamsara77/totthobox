<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('id_cards', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Core
            $table->string('card_number')->unique();
            $table->string('card_type')->default('employee'); // employee, student, membership, press, visitor, custom
            $table->string('status')->default('active'); // active, expired, revoked, suspended

            // Design
            $table->string('template_name')->default('aurora');
            $table->string('language')->default('both'); // bn, en, both
            $table->json('design_settings')->nullable(); // colors, fonts, layout variants, show/hide fields

            // English
            $table->string('name_en')->nullable();
            $table->string('designation_en')->nullable();
            $table->string('department_en')->nullable();
            $table->string('organization_en')->nullable();
            $table->text('address_en')->nullable();

            // Bangla
            $table->string('name_bn')->nullable();
            $table->string('designation_bn')->nullable();
            $table->string('department_bn')->nullable();
            $table->string('organization_bn')->nullable();
            $table->text('address_bn')->nullable();

            // Personal
            $table->string('blood_group')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nid_or_passport')->nullable();

            // Contact
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();

            // Validity
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();

            // Hardware / Smart
            $table->string('nfc_serial')->nullable();
            $table->string('barcode_data')->nullable();

            // Extreme Flexibility
            $table->json('custom_fields')->nullable(); // any dynamic fields

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index('card_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('id_cards');
    }
};
