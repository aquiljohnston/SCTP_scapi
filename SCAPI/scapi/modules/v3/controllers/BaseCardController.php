<?php

namespace app\modules\v3\controllers;

use Yii;

/*
* Implements basic functions for mileage and time cards
*/
class BaseCardController extends BaseActiveController
{
	
	protected function extractProjectsFromCards($type, $dropdownRecords, $projectAllOption)
	{
		$allTheProjects = [];
		//iterate and stash project name $p['ProjectID']
		foreach ($dropdownRecords as $p) {
			//currently only two option exist for key would have to update this if more views/tables/functions use this function
			//should look into standardizing this field			
			$key = array_key_exists($type.'ProjectID', $p) ? $p[$type.'ProjectID'] : $p['ProjectID'];
			$value = $p['ProjectName'];
			$allTheProjects[$key] = $value;
		}
		//remove dupes
		$allTheProjects = array_unique($allTheProjects);
		//abc order for all
		asort($allTheProjects);
		//appened all option to the front
		$allTheProjects = $projectAllOption + $allTheProjects;
		
		return $allTheProjects;
	}
	
	protected function extractEmployeesFromCards($dropdownRecords)
	{
		$employeeValues = [];
		//iterate and stash user values
		foreach ($dropdownRecords as $e) {
			//build key value pair
			$key = $e['UserID'];
			$value = $e['UserFullName'];
			$employeeValues[$key] = $value;
		}
		//remove dupes
		$employeeValues = array_unique($employeeValues);
		//abc order for all
		asort($employeeValues);
		//append all option to the front
		$employeeValues = [""=>"All"] + $employeeValues;
		
		return $employeeValues;
	}
	
	/**
    * Check if there is at least one card to be approved
    * @param $cardArr
    * @return boolean
    */
    protected function checkUnapprovedCardExist($type, $cardArr){
        foreach ($cardArr as $item){
            if ($item[$type.'ApprovedFlag'] == 0){
                return true;
            }
        }
        return false;
    }

    /**
    * Check if project was submitted ie Oasis or QB
    * @param $cardArray
    * @return boolean
    */
    protected function checkAllAssetsSubmitted($type, $cardArray){
        foreach ($cardArray as $item)
		{
			$oasisKey = array_key_exists($type.'OasisSubmitted', $item) ? $type.'OasisSubmitted' : 'OasisSubmitted';
			$qbKey = array_key_exists($type.'MSDynamicsSubmitted', $item) ? $type.'MSDynamicsSubmitted' : 'MSDynamicsSubmitted';
			
			if ($item[$oasisKey] == "No" || $item[$qbKey] == "No" ){
				return false;
			}
        }
        return true;
    }
}