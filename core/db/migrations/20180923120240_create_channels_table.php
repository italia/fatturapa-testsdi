<?php


use FatturaPa\Core\Models\MigrationManager;
use Illuminate\Database\Connection as DB;

class CreateChannelsTable extends MigrationManager
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        if (!$this->schema->hasTable('channels')) {
            $this->schema->create('channels', function (Illuminate\Database\Schema\Blueprint $table) {
                $table->text('cedente');
                $table->text('issuer');
                $table->primary('cedente');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->schema->drop('channels');
    }
}
