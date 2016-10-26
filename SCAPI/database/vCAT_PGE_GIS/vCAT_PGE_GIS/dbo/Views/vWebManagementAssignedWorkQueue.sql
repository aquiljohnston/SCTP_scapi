




CREATE View [dbo].[vWebManagementAssignedWorkQueue]
AS
Select 
wc.Division
,wc.WorkCenter
,ir.SurveyType
,Replace(ir.MapID, '-', '/') as [MapPlat]
,ir.LsNtfNo AS [NotificationID]
,u.UserLastName + ', ' + u.UserFirstName AS [Surveyor]
,u.UserEmployeeType [EmployeeType]
,ir.ComplianceDueDate [ComplianceDate]
,CASE WHEN awq.LockedFlag = 0 THEN 'Assigned' ELSE 'In Progress' END [Status]
--,ir.StatusType [Status]
,awq.DispatchMethod [DispatchMethod]
,ir.InspectionRequestUID [IRUID]
,awq.AssignedWorkQueueUID [AssignedWorkQueueUID]
,u.UserUID
,ir.MapGridUID
,mg.FLOC
,CAST(YEAR(ir.ComplianceDueDate) AS CHAR(4)) + ' - ' + DATENAME(mm, ir.ComplianceDueDate) AS ComplianceYearMonth
,awq.AssignedDate

from
(SELECT * From [dbo].[tAssignedWorkQueue] where ActiveFlag = 1) awq
Join (Select * from UserTb where UserActiveFlag = 1) u on awq.AssignedUserUID = u.UserUID
Join (Select * from tInspectionRequest where ActiveFlag = 1) ir on awq.AssignedInspectionRequestUID = ir.InspectionRequestUID
Join (Select * from rgMapGridLog where ActiveFlag = 1) mg on mg.MapGridUID = ir.MapGridUID
Join (Select * from rWorkCenter where ActiveFlag = 1) wc on wc.WorkCenterAbbreviationFLOC = mg.FuncLocMWC
Where ir.StatusType <> 'Completed'






