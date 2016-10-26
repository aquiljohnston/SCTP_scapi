
CREATE View [dbo].[vWebManagementDropDownDispatchDivision]
AS
SELECT Distinct
 wc.Division
--,wc.WorkCenter
FROM [dbo].[rgMapGridLog] mg
INNER JOIN [dbo].[tInspectionRequest] ir ON ir.MapGridUID = mg.MapGridUID
INNER JOIN [dbo].[rWorkCenter] wc on wc.WorkCenterAbbreviationFLOC = mg.FuncLocMWC
LEFT JOIN (
			SELECT AssignedInspectionRequestUID, Count(*) AS AssignedCount 
			FROM  [dbo].[tAssignedWorkQueue] 
			GROUP BY AssignedInspectionRequestUID
		   ) awq ON awq.AssignedInspectionRequestUID = ir.InspectionRequestUID
WHERE ir.StatusType <> 'Completed'
