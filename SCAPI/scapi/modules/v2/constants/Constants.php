<?php 

namespace app\modules\v2\constants;

final class Constants
{
	const DATE_FORMAT = 'Y-m-d H:i:s';
	
	//work queue status codes
	const WORK_QUEUE_ASSIGNED = 100;
	const WORK_QUEUE_IN_PROGRESS = 101;
	const WORK_QUEUE_COMPLETED = 102;
	
	//work order event indicators
	const WORK_ORDER_COMPLETED_NO_EVENT = 0;
	const WORK_ORDER_COMPLETED_WITH_EVENT = 1;
	const WORK_ORDER_CGE = 2;
	const WORK_ORDER_ADHOC = 3;
	
	private function __construct()
	{
		throw new Exception("Can't get an instance of Constants.");
	}
}