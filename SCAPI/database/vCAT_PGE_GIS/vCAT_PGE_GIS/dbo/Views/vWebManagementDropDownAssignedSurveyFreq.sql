

Create View [dbo].[vWebManagementDropDownAssignedSurveyFreq]
AS
select Distinct 
ir.SurveyType,
wc.Division,
wc.WorkCenter
from tInspectionRequest ir 
Left Join (Select * from rgMapGridLog where ActiveFlag = 1) mg on mg.MapGridUID = ir.MapGridUID
Left Join (select * from rWorkCenter where ActiveFlag = 1) wc on mg.FuncLocMWC = wc.WorkCenterAbbreviationFLOC
Left Join (Select * from [dbo].[tAssignedWorkQueue] where ActiveFlag = 1) awq on awq.AssignedInspectionRequestUID = ir.InspectionRequestUID
where ir.StatusType <> 'Completed' and awq.AssignedInspectionRequestUID is not null

