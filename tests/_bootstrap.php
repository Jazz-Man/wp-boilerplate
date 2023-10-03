<?php

use Env\Env;
use lucatume\WPBrowser\Utils\Db;

use function Env\env;

function createTestDatabasesIfNotExist(): void {

    $root_dir = dirname( __DIR__ );

    Env::$options |= Env::USE_SERVER_ARRAY;

    try {

        $dotenv = Dotenv\Dotenv::createUnsafeImmutable( $root_dir, '.env', false );

        $dotenv->load();

        $dotenv->required( ['TEST_DB_HOST', 'TEST_DB_NAME', 'TEST_DB_USER', 'TEST_DB_PASSWORD'] );

        $db = Db::db( 'mysql:host='.env( 'TEST_DB_HOST' ), env( 'TEST_DB_USER' ), env( 'TEST_DB_PASSWORD' ) );
        $db( 'CREATE DATABASE IF NOT EXISTS '.env( 'TEST_DB_NAME' ) );

    } catch ( Exception $e ) {
        codecept_debug( 'Could not connect to the database: '.$e->getMessage() );
    }
}

createTestDatabasesIfNotExist();

if ( function_exists( 'uopz_allow_exit' ) ) {
    uopz_allow_exit( true );
}
