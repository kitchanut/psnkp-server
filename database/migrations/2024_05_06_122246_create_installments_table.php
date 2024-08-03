<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstallmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('installments', function (Blueprint $table) {
            $table->id();
            $table->integer('working_id');
            $table->date('installment_date_1');
            $table->integer('installment_value_1');
            $table->integer('installment_pay_1')->nullable();
            $table->date('installment_date_2');
            $table->integer('installment_value_2');
            $table->integer('installment_pay_2')->nullable();
            $table->date('installment_date_3');
            $table->integer('installment_value_3');
            $table->integer('installment_pay_3')->nullable();
            $table->date('installment_date_4');
            $table->integer('installment_value_4');
            $table->integer('installment_pay_4')->nullable();
            $table->date('installment_date_5');
            $table->integer('installment_value_5');
            $table->integer('installment_pay_5')->nullable();
            $table->date('installment_date_6');
            $table->integer('installment_value_6');
            $table->integer('installment_pay_6')->nullable();
            $table->integer('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('installments');
    }
}
