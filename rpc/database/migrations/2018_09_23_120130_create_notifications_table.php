<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('invoice_id');
            $table->foreign('invoice_id')->references('id')->on('invoices')->change();
            $table->text('type');
            $table->text('status');
            $table->text('blob');
            $table->string('actor')->nullable();
            $table->text('nomefile')->nullable();
            $table->dateTimeTz('ctime')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('notifications');
    }
}
