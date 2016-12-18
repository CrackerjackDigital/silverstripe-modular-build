<?php
namespace Modular\Extensions\Model;

use Modular\enabler;
use Modular\ModelExtension;

/**
 * Base class for extensions that get attached to the Builder model to actually build records etc in order they are attached.
 *
 * @package Modular\Extensions\Model
 */
class Builder extends ModelExtension  {
	use enabler;

	private static $enabled = true;

	public static function shouldRun() {
		return static::enabled();
	}
}