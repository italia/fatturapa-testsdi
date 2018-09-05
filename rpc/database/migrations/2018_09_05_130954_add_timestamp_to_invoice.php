<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimestampToInvoice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
                        
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
          Schema::table('invoices', function (Blueprint $table) {
                        
			$table->dropColumn('ctime');
                        
        });
    }
}
