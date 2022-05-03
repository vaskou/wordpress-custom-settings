<?php
if ( ! class_exists( 'WordpressCustomSettings_Bootstrap_2_0_1' ) ) {

	class WordpressCustomSettings_Bootstrap_2_0_1 {

		const VERSION = '2.0.1';

		private static $_instance;

		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		private function __construct() {

			if ( ! class_exists( 'WordpressCustomSettings\\VersionHandler' ) ) {
				include 'VersionHandler.php';
			}

			$version = WordpressCustomSettings\VersionHandler::instance();

			if ( empty( $version->getVersion() ) ) {
				$version->setVersion( self::VERSION );
			}

			if ( version_compare( $version->getVersion(), self::VERSION, '<=' ) ) {
				$version->setVersion( self::VERSION );
				spl_autoload_register( array( $this, 'class_loader' ), true, true );
			}
		}

		public function class_loader( $class_name ) {

			if ( 0 !== strpos( $class_name, 'WordpressCustomSettings\\' ) ) {
				return;
			}

			$filename = substr( $class_name, 24 );

			include_once( "{$filename}.php" );
		}
	}

	WordpressCustomSettings_Bootstrap_2_0_1::instance();
}