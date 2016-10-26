


CREATE View [dbo].[vWebManagementDropDownAssignedDivision]
AS
SELECT Distinct
 wc.Division
--,wc.WorkCenter
FROM (Select * From [dbo].[rgMapGridLog] where ActiveFlag = 1) mg
INNER JOIN (Select * from [dbo].[tInspectionRequest] where ActiveFlag = 1) ir ON ir.MapGridUID = mg.MapGridUID
INNER JOIN (Select * from [dbo].[rWorkCenter] where ActiveFlag = 1) wc on wc.WorkCenterAbbreviationFLOC = mg.FuncLocMWC
JOIN (
			SELECT AssignedInspectionRequestUID, Count(*) AS AssignedCount 
			FROM  [dbo].[tAssignedWorkQueue] 
			Where ActiveFlag = 1
			GROUP BY AssignedInspectionRequestUID
		   ) awq ON awq.AssignedInspectionRequestUID = ir.InspectionRequestUID
WHERE ir.StatusType <> 'Completed' and awq.AssignedInspectionRequestUID is not null


