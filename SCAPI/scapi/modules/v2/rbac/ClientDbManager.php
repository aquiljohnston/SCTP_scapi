<?php

namespace app\modules\v2\rbac;

use Yii;
use yii\rbac\DbManager;

class ClientDbManager extends ScDbManager
{
	public $db = '';
	/**
     * @var string the name of the table storing authorization items. Defaults to "auth_item".
     */
	public $itemTable = '{{auth_item}}';
	/**
     * @var string the name of the table storing authorization item hierarchy. Defaults to "auth_item_child".
     */
	public $itemChildTable = '{{auth_item_child}}';
	/**
     * @var string the name of the table storing authorization item assignments. Defaults to "auth_assignment".
     */
    public $assignmentTable = '{{auth_assignment}}';
    /**
     * @var string the name of the table storing rules. Defaults to "auth_rule".
     */
    public $ruleTable = '{{auth_rule}}';
	
	public function __construct($currentDb)
	{
		$this->db = $currentDb;
	}
}