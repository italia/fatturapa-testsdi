<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('remote_id')->nullable();
            $table->text('nomefile');
            $table->text('posizione');
            $table->text('cedente');
            $table->text('anno');
            $table->text('status');
            $table->text('blob');
            $table->dateTimeTz('ctime')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('actor')->nullable();
            $table->string('issuer')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('invoices');
    }
}
