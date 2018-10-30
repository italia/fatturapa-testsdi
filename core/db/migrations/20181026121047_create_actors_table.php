<?php

use FatturaPa\Core\Models\MigrationManager;
use Illuminate\Database\Connection as DB;

class CreateActorsTable extends MigrationManager
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        if (!$this->schema->hasTable('actors')) {
>           $this->schema->create('actors', function (Illuminate\Database\Schema\Blueprint $table) {
                $table->text('id');
                $table->text('code');
                $table->text('key');
                $table->text('certificate');                                   
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->schema->drop('actors');
    }
}
