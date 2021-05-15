<?php

namespace WordpressCustomSettings;

class SettingField {

	protected $name;
	protected $title;
	protected $type;
	protected $section;
	protected $args;

	/**
	 * SettingField constructor.
	 *
	 * @param string $name
	 * @param string $title
	 * @param string $type
	 * @param string $section
	 * @param array $args
	 */
	public function __construct( $name, $title, $type, $section = 'default', $args = array() ) {
		$this->name    = $name;
		$this->title   = $title;
		$this->type    = $type;
		$this->section = $section;

		$args       = wp_parse_args( array( 'type' => $type ), $args );
		$this->args = $args;
	}

	/**
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function get_title(): string {
		return $this->title;
	}

	/**
	 * @return string
	 */
	public function get_type(): string {
		return $this->type;
	}

	/**
	 * @return string
	 */
	public function get_section(): string {
		return $this->section;
	}

	/**
	 * @return array
	 */
	public function get_args(): array {
		return $this->args;
	}

}