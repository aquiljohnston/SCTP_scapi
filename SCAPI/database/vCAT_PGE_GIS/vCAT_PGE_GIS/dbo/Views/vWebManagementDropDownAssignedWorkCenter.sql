



CREATE View [dbo].[vWebManagementDropDownAssignedWorkCenter]
AS
SELECT Distinct
 wc.Division
,wc.WorkCenter
FROM [dbo].[rgMapGridLog] mg
INNER JOIN [dbo].[tInspectionRequest] ir ON ir.MapGridUID = mg.MapGridUID
INNER JOIN [dbo].[rWorkCenter] wc on wc.WorkCenterAbbreviationFLOC = mg.FuncLocMWC
JOIN (
			SELECT AssignedInspectionRequestUID, Count(*) AS AssignedCount 
			FROM  [dbo].[tAssignedWorkQueue] 
			Where ActiveFlag = 1
			GROUP BY AssignedInspectionRequestUID
		   ) awq ON awq.AssignedInspectionRequestUID = ir.InspectionRequestUID
WHERE ir.StatusType <> 'Completed'



