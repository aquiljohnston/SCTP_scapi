





Create View [dbo].[vWebManagementDropDownAssignedFLOC]
AS
SELECT Distinct
mg.FLOC,
wc.Division,
wc.WorkCenter,
ir.SurveyType
FROM [dbo].[rgMapGridLog] mg
INNER JOIN [dbo].[tInspectionRequest] ir ON ir.MapGridUID = mg.MapGridUID
Left Join (select * from rWorkCenter where ActiveFlag = 1) wc on mg.FuncLocMWC = wc.WorkCenterAbbreviationFLOC
Left Join (Select * from [dbo].[tAssignedWorkQueue] where ActiveFlag = 1) awq on awq.AssignedInspectionRequestUID = ir.InspectionRequestUID
WHERE ir.StatusType <> 'Completed'  and awq.AssignedInspectionRequestUID is Not null




