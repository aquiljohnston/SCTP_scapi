



CREATE View [dbo].[vWebManagementDropDownAssigned]
AS
SELECT Distinct
wc.Division,
wc.WorkCenter,
ir.SurveyType [SurveyFreq],
mg.FLOC,
--ir.statustype,
CASE WHEN awq.LockedFlag = 0 THEN 'Assigned' ELSE 'In Progress' END [StatusType],
awq.[DispatchMethod],

Cast(Year(ir.ComplianceDueDate) as Char(4)) + ' - ' + datename(mm, ir.ComplianceDueDate) As ComplianceYearMonth,
Cast(Year(ir.ComplianceDueDate) as Char(4)) + Right('00' + Cast(Month(ir.ComplianceDueDate) as varchar(2)), 2) AS ComplianceSort
FROM [dbo].[rgMapGridLog] mg
INNER JOIN [dbo].[tInspectionRequest] ir ON ir.MapGridUID = mg.MapGridUID
Left Join (select * from rWorkCenter where ActiveFlag = 1) wc on mg.FuncLocMWC = wc.WorkCenterAbbreviationFLOC
Left Join (Select * from [dbo].[tAssignedWorkQueue] where ActiveFlag = 1) awq on awq.AssignedInspectionRequestUID = ir.InspectionRequestUID
WHERE ir.StatusType <> 'Completed' and CHARINDEX('adhoc', ir.InspectionRequestUID) = 0 and awq.AssignedInspectionRequestUID is not null and awq.[DispatchMethod] <> 'Ad Hoc'








