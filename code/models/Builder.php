<?php
namespace Modular\Models;

use Modular\Model;

/**
 * Build model to which Build extensions are attached in order so they build in correct order to resolve
 * dependencies etc. Each extensions derived from Build extension should provide a requireDefaultRecords function
 * which builds the records.
 *
 * @package Modular\Models
 */
class Builder extends Model {
	use \Modular\Traits\enabler;

    const BuildAllFlag = 'all';

	// disable all BuildModel requireDefaultRecords processing (providePermissions should still work).
	private static $disable_all = false;

	private static $enabled = true;

	/**
	 * Don't require a table for this or immediately derived class at the moment. In future will be used to track builds so
	 * this can be relaxed.
	 */
    public function requireTable() {
        \DB::dontRequireTable(get_class($this));
	    \DB::dontRequireTable(__CLASS__);
    }

    /**
     * Check if we should run the build using config and request url
     * @return bool
     */
    public function shouldRun() {
        return (!self::config()->get('disable_all'))
            && (static::enabled()|| $this->forceBuild());
    }

    /**
     * Check if we are being forced to build class or classes irrespective of 'run_on_build' setting.
     * @return bool - true yes build this class, false no leave out of build
     */
    public function forceBuild() {
        // classes to build are passed as build=BuildClass1,BuildClass2,BuildClassN or 'build=all' for all classes
	    // where BuildClass is e.g. SocialActionTypeBuild
        $buildClasses = array_map(
	        'strtolower',
	        explode(',', \Controller::curr()->getRequest()->getVar('build'))
        );

	    $className = strtolower(get_class($this));

	    return in_array(static::BuildAllFlag, $buildClasses)
		    || in_array($className, $buildClasses);
    }
}