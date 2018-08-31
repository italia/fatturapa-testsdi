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
            $table->uuid('uuid');            
			$table->text('nomefile');
			$table->text('posizione');
			$table->text('cedente');
			$table->text('anno');
			$table->text('status');
			$table->text('blob');
        });
		
		//DB::statement('ALTER TABLE invoices ALTER COLUMN uuid SET DEFAULT uuid_generate_v4();');
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
