<?php
namespace Modular\Extensions\Model;

use Modular\enabler;
use Modular\Fields\EnumField;
use Modular\ModelExtension;

/**
 * Model extension which tracks if a record has been updated since the last build, e.g. by a User interaction or other
 * update. This way we can check on build if BuildModelUnchanged = false then don't mess with the record
 * and conversely if BuildModelUnchanged then we can mess with it.
 */
class Built extends EnumField  {
	// in this case enabler turns the LastUpdate timestamp tracker on and off so we can
	// do updates to the object with triggering the 'has been updated' state.
	use enabler;

	const SingleFieldName = 'Result';

	const DateFieldName                 = 'BuiltDate';
	const ResultFieldName               = 'BuiltResult';
	const LastBuiltTimestampFieldName   = 'BuiltTimestamp';
	const LastUpdatedTimestampFieldName = 'LastUpdatedTimestamp';

	// keep these and the enum field in sync
	const ResultCreated   = 'created';
	const ResultChanged   = 'changed';
	const ResultUnchanged = 'unchanged';

	private static $db = [
		// enum is added
		self::DateFieldName                 => 'SS_DateTime',
		self::LastBuiltTimestampFieldName   => 'Int',
		self::LastUpdatedTimestampFieldName => 'Int',
	];

	private static $options = [
		self::ResultCreated,
	    self::ResultChanged,
	    self::ResultUnchanged
	];

	public function onBeforeWrite() {
		// if we have disabled this extension then we don't update the LastUpdated tracking timestamp on update
		if ($this()->isInDB() && static::enabled()) {
			// update the tracking timestamp so can compare via builtModelUpdated
			$this()->{self::LastUpdatedTimestampFieldName} = microtime();
		} else {
			// we are new so make tracking and current timestamps the same
			// we always do this wether we are 'enabled' or not
			$this()->{self::LastUpdatedTimestampFieldName} = $this()->{self::LastBuiltTimestampFieldName};
		}
	}

	/**
	 * Set result, date and timestamp on the extended model
	 *
	 * @param string $result one of self.ResultABC constants
	 */
	public function buildModelBuilt($result) {
		$this()->{self::ResultFieldName} = $result;
		$this()->{self::DateFieldName} = date('Y-m-d h:i:s');
		$this()->{self::LastBuiltTimestampFieldName} = microtime();
	}

	public function builtModelUpdated() {
		return $this()->{self::LastUpdatedTimestampFieldName} > $this()->{self::LastBuiltTimestampFieldName};
	}
}