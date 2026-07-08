<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfileFieldsToStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('staff', function (Blueprint $table) {
            // Education & Experience (JSON)
            $table->json('institutions')->nullable();
            $table->json('experiences')->nullable();
            $table->json('publications')->nullable();
            $table->json('honours')->nullable();
            $table->json('memberships')->nullable();
            $table->text('extra_curricular')->nullable();

            // Next of Kin extra
            $table->string('kin_relationship')->nullable();

            // Document uploads
            $table->string('doc_photo')->nullable();
            $table->string('doc_birth_certificate')->nullable();
            $table->string('doc_primary_cert')->nullable();
            $table->string('doc_ssce')->nullable();
            $table->string('doc_diploma')->nullable();
            $table->string('doc_degree')->nullable();
            $table->string('doc_masters')->nullable();
            $table->string('doc_phd')->nullable();
            $table->string('doc_indigine')->nullable();
            $table->string('doc_workshop')->nullable();
            $table->string('doc_nysc')->nullable();
            $table->string('doc_appointment_letter')->nullable();
            $table->string('doc_confirmation')->nullable();
            $table->string('doc_professional_body')->nullable();
            $table->string('doc_other')->nullable();
            $table->string('doc_other_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn([
                'institutions', 'experiences', 'publications', 'honours', 'memberships',
                'extra_curricular', 'kin_relationship',
                'doc_photo', 'doc_birth_certificate', 'doc_primary_cert', 'doc_ssce',
                'doc_diploma', 'doc_degree', 'doc_masters', 'doc_phd', 'doc_indigine',
                'doc_workshop', 'doc_nysc', 'doc_appointment_letter', 'doc_confirmation',
                'doc_professional_body', 'doc_other', 'doc_other_name'
            ]);
        });
    }
}
