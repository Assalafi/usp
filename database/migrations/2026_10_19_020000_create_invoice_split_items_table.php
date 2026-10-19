<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('invoice_split_items')) {
            Schema::create('invoice_split_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('invoice_id');
                $table->string('line_item_id', 80);
                $table->string('beneficiary_name', 180);
                $table->string('beneficiary_account', 30)->nullable();
                $table->string('bank_code', 20)->nullable();
                $table->decimal('beneficiary_amount', 15, 2);
                $table->boolean('is_primary')->default(false);
                $table->timestamps();
                $table->unique(['invoice_id', 'line_item_id'], 'invoice_split_items_invoice_line_unique');
                $table->index(['beneficiary_name', 'created_at'], 'invoice_split_items_beneficiary_date_idx');
                $table->index(['invoice_id', 'is_primary'], 'invoice_split_items_invoice_primary_idx');
            });
        }

        // These indexes keep period/status/service reporting fast without
        // changing the existing invoice data or its payment behaviour.
        if (Schema::hasTable('invoices')) {
            $indexExists = static function (string $name): bool {
                return collect(DB::select('SHOW INDEX FROM invoices WHERE Key_name = ?', [$name]))
                    ->isNotEmpty();
            };
            if (!$indexExists('invoices_status_created_idx')) {
                Schema::table('invoices', function (Blueprint $table) {
                    $table->index(['status', 'created_at'], 'invoices_status_created_idx');
                });
            }
            if (!$indexExists('invoices_split_created_idx')) {
                Schema::table('invoices', function (Blueprint $table) {
                    $table->index(['split_enabled', 'created_at'], 'invoices_split_created_idx');
                });
            }
            // serviceTypeId is a TEXT column and already has a prefix index;
            // adding it to a composite index exceeds MySQL's key-size limit.
            if (!$indexExists('invoices_academic_filter_idx')) {
                Schema::table('invoices', function (Blueprint $table) {
                    $table->index(['faculty', 'department', 'program', 'level'], 'invoices_academic_filter_idx');
                });
            }

            // Populate the audit table for split invoices created before this
            // migration. The stored JSON already contains masked accounts.
            DB::table('invoices')
                ->where('split_enabled', 1)
                ->whereNotNull('split_details')
                ->orderBy('id')
                ->chunkById(500, function ($invoices) {
                    foreach ($invoices as $invoice) {
                        $items = json_decode((string) $invoice->split_details, true);
                        if (!is_array($items)) {
                            continue;
                        }
                        foreach ($items as $item) {
                            if (!is_array($item) || empty($item['lineItemsId'])) {
                                continue;
                            }
                            DB::table('invoice_split_items')->updateOrInsert(
                                ['invoice_id' => $invoice->id, 'line_item_id' => (string) $item['lineItemsId']],
                                [
                                    'beneficiary_name' => (string) ($item['beneficiaryName'] ?? 'Beneficiary'),
                                    'beneficiary_account' => $item['beneficiaryAccount'] ?? null,
                                    'bank_code' => $item['bankCode'] ?? null,
                                    'beneficiary_amount' => (float) ($item['beneficiaryAmount'] ?? 0),
                                    'is_primary' => ($item['lineItemsId'] ?? '') === 'beneficiary1',
                                    'created_at' => $invoice->created_at ?: now(),
                                    'updated_at' => now(),
                                ]
                            );
                        }
                    }
                });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('invoices')) {
            $indexes = collect(DB::select('SHOW INDEX FROM invoices'))->pluck('Key_name')->unique();
            Schema::table('invoices', function (Blueprint $table) use ($indexes) {
                foreach (['invoices_status_created_idx', 'invoices_split_created_idx', 'invoices_academic_filter_idx'] as $index) {
                    if ($indexes->contains($index)) {
                        $table->dropIndex($index);
                    }
                }
            });
        }
        Schema::dropIfExists('invoice_split_items');
    }
};
