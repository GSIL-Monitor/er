<?php
namespace Platform\Wms;
use Platform\Wms\Pheanstalk_Exception;
require_once(ROOT_DIR . '/Wms/Exception.php');
/**
 * An exception originating from the beanstalkd client
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_Exception_ClientException
	extends Pheanstalk_Exception
{
}
