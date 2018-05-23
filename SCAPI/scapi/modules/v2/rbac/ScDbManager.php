<?php

namespace app\modules\v2\rbac;

use Yii;
use yii\rbac\DbManager;
use app\modules\v2\models\BaseActiveRecord;
use app\modules\v2\controllers\BaseActiveController;
use yii\base\InvalidCallException;
use yii\base\InvalidParamException;

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
	
	public function addChildren($parent, $children)
	{
		//create array of data to insert
		$bulkInsertArray = array();
		
		foreach($children as $child)
		{
			if ($parent->name === $child->name) {
				throw new InvalidParamException("Cannot add '{$parent->name}' as a child of itself.");
			}

			if ($parent instanceof Permission && $child instanceof Role) {
				throw new InvalidParamException('Cannot add a role as a child of a permission.');
			}
			
			//this takes a cumulative ~20-25 seconds to perform becuase it queries the db
			if ($this->detectLoop($parent, $child)) {
				throw new InvalidCallException("Cannot add '{$child->name}' as a child of '{$parent->name}'. A loop has been detected.");
			}
			
			//add data to array
			$bulkInsertArray[] = [
				'parent' => $parent->name,
				'child' => $child->name
			];

			$this->invalidateCache();
		}
		
		//perform bulk insert
		if(count($bulkInsertArray) > 0){
			$columns = ['parent', 'child'];
			$this->db->createCommand()
				->batchInsert($this->itemChildTable, $columns, $bulkInsertArray)
				->execute();
		}			
		
        return true;
	}
	
	/**
     * @inheritdoc
     */
    public function bulkAssign($bulkInsertArray)
    {
		$columns = [
			'user_id', 'item_name', 'created_at'
		];

		if(count($bulkInsertArray) > 0){
			$this->db->createCommand()
				->batchInsert($this->assignmentTable, $columns, $bulkInsertArray)->execute();
		}
		
        return true;
    }
	
	public function addItems($items){
		
		//create array of data to insert
		$bulkInsertArray = array();
		$time = time();
		
		foreach($items as $item)
		{
			if ($item->createdAt === null) {
				$item->createdAt = $time;
			}
			if ($item->updatedAt === null) {
				$item->updatedAt = $time;
			}
			
			$bulkInsertArray[] = [
				'name' => $item->name,
				'type' => $item->type,
				'description' => $item->description,
				'rule_name' => $item->ruleName,
				'data' => $item->data === null ? null : serialize($item->data),
				'created_at' => $item->createdAt,
				'updated_at' => $item->updatedAt,
			];

			$this->invalidateCache();
		}
		
		//perform bulk insert
		if(count($bulkInsertArray) > 0){
			$columns = ['name', 'type', 'description', 'rule_name', 'data', 'created_at', 'updated_at'];
			$this->db->createCommand()
				->batchInsert($this->itemTable, $columns, $bulkInsertArray)
				->execute();
		}

        return true;
    }
}