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
            if (!Schema::hasColumn('staff', 'institutions')) {
                $table->json('institutions')->nullable();
            }
            if (!Schema::hasColumn('staff', 'experiences')) {
                $table->json('experiences')->nullable();
            }
            if (!Schema::hasColumn('staff', 'publications')) {
                $table->json('publications')->nullable();
            }
            if (!Schema::hasColumn('staff', 'honours')) {
                $table->json('honours')->nullable();
            }
            if (!Schema::hasColumn('staff', 'memberships')) {
                $table->json('memberships')->nullable();
            }
            if (!Schema::hasColumn('staff', 'extra_curricular')) {
                $table->text('extra_curricular')->nullable();
            }

            // Next of Kin extra
            if (!Schema::hasColumn('staff', 'kin_relationship')) {
                $table->string('kin_relationship')->nullable();
            }

            // Document uploads
            if (!Schema::hasColumn('staff', 'doc_photo')) {
                $table->string('doc_photo')->nullable();
            }
            if (!Schema::hasColumn('staff', 'doc_birth_certificate')) {
                $table->string('doc_birth_certificate')->nullable();
            }
            if (!Schema::hasColumn('staff', 'doc_primary_cert')) {
                $table->string('doc_primary_cert')->nullable();
            }
            if (!Schema::hasColumn('staff', 'doc_ssce')) {
                $table->string('doc_ssce')->nullable();
            }
            if (!Schema::hasColumn('staff', 'doc_diploma')) {
                $table->string('doc_diploma')->nullable();
            }
            if (!Schema::hasColumn('staff', 'doc_degree')) {
                $table->string('doc_degree')->nullable();
            }
            if (!Schema::hasColumn('staff', 'doc_masters')) {
                $table->string('doc_masters')->nullable();
            }
            if (!Schema::hasColumn('staff', 'doc_phd')) {
                $table->string('doc_phd')->nullable();
            }
            if (!Schema::hasColumn('staff', 'doc_indigine')) {
                $table->string('doc_indigine')->nullable();
            }
            if (!Schema::hasColumn('staff', 'doc_workshop')) {
                $table->string('doc_workshop')->nullable();
            }
            if (!Schema::hasColumn('staff', 'doc_nysc')) {
                $table->string('doc_nysc')->nullable();
            }
            if (!Schema::hasColumn('staff', 'doc_trade_test')) {
                $table->string('doc_trade_test')->nullable();
            }
            if (!Schema::hasColumn('staff', 'doc_appointment_letter')) {
                $table->string('doc_appointment_letter')->nullable();
            }
            if (!Schema::hasColumn('staff', 'doc_confirmation')) {
                $table->string('doc_confirmation')->nullable();
            }
            if (!Schema::hasColumn('staff', 'doc_professional_body')) {
                $table->string('doc_professional_body')->nullable();
            }
            if (!Schema::hasColumn('staff', 'doc_other')) {
                $table->string('doc_other')->nullable();
            }
            if (!Schema::hasColumn('staff', 'doc_other_name')) {
                $table->string('doc_other_name')->nullable();
            }
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
                'doc_workshop', 'doc_nysc', 'doc_trade_test', 'doc_appointment_letter', 'doc_confirmation',
                'doc_professional_body', 'doc_other', 'doc_other_name'
            ]);
        });
    }
}
