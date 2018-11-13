<?php
require 'config.php';
return [
  'paths' => [
    'migrations' => 'db/migrations'
  ],
  'migration_base_class' => 'FatturaPa\Core\Models\MigrationManager',
  'environments' => [
    'default_migration_table' => 'core_migrations',
    'default_database' => 'dev',
    'dev' => [
      'adapter' => DBDRIVER,
      'host' => DBHOST,
      'name' => DBNAME,
      'user' => DBUSER,
      'pass' => DBPASS,
      'charset' => 'utf8',
    ]
  ]
];