<?php


use FatturaPa\Core\Models\MigrationManager;
use Illuminate\Database\Connection as DB;

class CreateInvoicesTable extends MigrationManager
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        if (!$this->schema->hasTable('invoices')) {
            $this->schema->create('invoices', function (Illuminate\Database\Schema\Blueprint $table) {
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
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->schema->drop('invoices');
    }
}
