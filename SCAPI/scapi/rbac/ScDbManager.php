<?php

namespace app\rbac;

use Yii;
use yii\rbac\DbManager;
use app\modules\v1\models\BaseActiveRecord;
use app\modules\v1\controllers\BaseActiveController;

class ScDbManager extends DbManager
{
	public $db = '';
	/**
     * @var string the name of the table storing authorization items. Defaults to "auth_item".
     */
	public $itemTable = '{{%rbac.auth_item}}';
	/**
     * @var string the name of the table storing authorization item hierarchy. Defaults to "auth_item_child".
     */
	public $itemChildTable = '{{%rbac.auth_item_child}}';
	/**
     * @var string the name of the table storing authorization item assignments. Defaults to "auth_assignment".
     */
    public $assignmentTable = '{{%rbac.auth_assignment}}';
    /**
     * @var string the name of the table storing rules. Defaults to "auth_rule".
     */
    public $ruleTable = '{{%rbac.auth_rule}}';
	
	public function __construct($optionalDb = null)
	{
		if ($optionalDb === null)
		{
			$this->db = $this->getDb();
		}
		else
		{
			$this->db = $optionalDb;
		}
	}
	
	public function getDb()
	{
		BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
		return BaseActiveRecord::getDb();
	}
}