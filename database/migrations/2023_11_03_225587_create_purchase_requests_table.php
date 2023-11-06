<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('derived_id')->nullable();
            $table->integer('need_approval_flag')->default(0);
            $table->string('code');
            $table->string('issconnect_mr_no')->nullable();
            $table->date('request_date');
            $table->date('request_delivery_date')->nullable();
            $table->date('request_receive_date')->nullable();
            $table->foreignId('client_id')->nullable()->constrained();
            $table->foreignId('site_id')->nullable()->constrained();
            $table->foreignId('service_type_id')->nullable()->constrained();
            $table->foreignId('warehouse_id')->nullable()->constrained();
            $table->foreignId('category_id')->nullable()->constrained();
            $table->integer('create_user')->nullable();
            $table->integer('manager_user')->nullable();
            $table->dateTime('manager_reaction_time')->nullable();
            $table->string('manager_comment')->nullable();
            $table->integer('cost_controller_user')->nullable();
            $table->dateTime('cost_controller_reaction_time')->nullable();
            $table->string('cost_controller_comment')->nullable();
            $table->integer('kam_user')->nullable();
            $table->dateTime('kam_reaction_time')->nullable();
            $table->string('kam_comment')->nullable();
            $table->foreignId('vendor_id')->nullable()->constrained();
            $table->integer('status')->default(0);
            $table->string('purchase_no')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('po_request_delivery_date')->nullable();
            $table->date('po_request_receive_date')->nullable();
            $table->integer('purchase_user')->nullable();
            $table->string('purchase_type')->default('item');
            $table->string('purchase_no_file')->nullable();
            $table->date('etd')->nullable();
            $table->date('eta')->nullable();
            $table->string('delivery_no')->nullable();
            $table->date('delivery_date')->nullable();
            $table->integer('delivery_user')->nullable();
            $table->string('vehicle_no')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('driver_ktp')->nullable();
            $table->string('driver_phone_no')->nullable();
            $table->string('delivery_by')->nullable();
            $table->string('ncr_no')->nullable();
            $table->date('ncr_date')->nullable();
            $table->date('inspection_date')->nullable();
            $table->string('ncr_remark')->nullable();
            $table->string('ncr_action_plan')->nullable();
            $table->string('ncr_status')->nullable();
            $table->integer('inspection_status')->nullable();
            $table->integer('inspection_user')->nullable();
            $table->string('receive_no')->nullable();
            $table->date('receive_date')->nullable();
            $table->integer('receive_user')->nullable();
            $table->string('erp_receive_no')->nullable();
            $table->date('erp_receive_date')->nullable();
            $table->integer('erp_receive_user')->nullable();
            $table->string('return_no')->nullable();
            $table->date('return_date')->nullable();
            $table->integer('return_user')->nullable();
            $table->string('vendor_invoice_no')->nullable();
            $table->integer('vendor_invoice_user')->nullable();
            $table->date('vendor_invoice_date')->nullable();
            $table->date('vendor_invoice_send_date')->nullable();
            $table->date('vendor_invoice_receive_date')->nullable();
            $table->date('vendor_invoice_reject_date')->nullable();
            $table->string('vendor_invoice_reject_reason')->nullable();
            $table->date('vendor_invoice_due_date')->nullable();
            $table->date('vendor_invoice_estimate_payment_date')->nullable();
            $table->integer('deliver_ontime')->default(0);
            $table->decimal('deliver_infull', 24, 2)->default(0);
            $table->integer('receive_ontime')->default(0);
            $table->decimal('receive_infull', 24, 2)->default(0);
            $table->integer('kpi_quality')->default(0);
            $table->integer('kpi_fullfillment')->default(0);
            $table->integer('kpi_hse')->default(0);
            $table->string('kpi_remark')->nullable();
            $table->decimal('total_request_qty', 24, 2)->default(0);
            $table->decimal('total_delivery_qty', 24, 2)->default(0);
            $table->decimal('total_pass_qc_qty', 24, 2)->default(0);
            $table->decimal('total_reject_qc_qty', 24, 2)->default(0);
            $table->decimal('total_received_qty', 24, 2)->default(0);
            $table->decimal('total_return_qty', 24, 2)->default(0);
            $table->decimal('total_invoice_qty', 24, 2)->default(0);
            $table->decimal('total_request_amount', 24, 2)->default(0);
            $table->decimal('total_delivery_amount', 24, 2)->default(0);
            $table->decimal('total_pass_qc_amount', 24, 2)->default(0);
            $table->decimal('total_reject_qc_amount', 24, 2)->default(0);
            $table->decimal('total_received_amount', 24, 2)->default(0);
            $table->decimal('total_return_amount', 24, 2)->default(0);
            $table->decimal('total_invoice_amount', 24, 2)->default(0);
            $table->string('remarks')->nullable();
            $table->integer('request_type')->nullable();
            $table->integer('process_type')->nullable();
            $table->string('vendor_invoice_file')->nullable();
            $table->string('vendor_deliverydoc_file')->nullable();
            $table->string('vendor_faktur_pajak_file')->nullable();
            $table->integer('vat_code')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
