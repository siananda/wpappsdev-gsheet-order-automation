<?php

namespace WPAppsDev\GSOA\Traits;

trait ChainableContainer {
	/**
	 * Contains chainable class instances
	 *
	 * @var array
	 */
	protected $container = [];

	/**
	 * Magic getter to get chainable container instance
	 *
	 * @param string $prop Object key.
	 *
	 * @return mixed
	 */
	public function __get( $prop ) {
		if ( array_key_exists( $prop, $this->container ) ) {
			return $this->container[ $prop ];
		}
	}
}
