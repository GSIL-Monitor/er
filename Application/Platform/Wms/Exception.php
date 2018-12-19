<?php
namespace Platform\Wms;
use Platform\Wms\Exception;
require_once(ROOT_DIR . '/Wms/php_exception.php');
/**
 * An exception originating from the Pheanstalk package
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_Exception
	extends Exception
{
}
