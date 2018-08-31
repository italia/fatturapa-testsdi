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
          	$table->uuid('uuid');                  
			$table->text('invoice_uuid');
			$table->text('type');
			$table->text('state');
			$table->binary('blob');			
        });
		
		//DB::statement('ALTER TABLE notifications ALTER COLUMN uuid SET DEFAULT uuid_generate_v4();');
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
