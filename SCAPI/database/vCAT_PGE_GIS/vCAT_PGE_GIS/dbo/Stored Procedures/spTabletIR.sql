CREATE Procedure spTabletIR
(
	@UserUID varchar(100)
)
AS

/*

EXEC spTabletIR 'User_57590026_20160822135947_Postman'

*/

--Declare @UserUID varchar(100) = 'User_57590026_20160822135947_Postman'


Select *  Into #TempIR From
(SELECT
 ir.InspectionRequestUID
,wc.Division
,wc.WorkCenter
,ir.SurveyType
,Replace(ir.MapID, '-', '/') as [MapPlat]
,ir.LsNtfNo AS [Notification ID]
,ir.ComplianceDueDate
,ir.ReleaseDate AS [SAP Released]
,CAST(YEAR(ir.ComplianceDueDate) AS CHAR(4)) + ' - ' + DATENAME(mm, ir.ComplianceDueDate) AS ComplianceYearMonth
, awq.AssignedUserUID
,CASE WHEN u.UserName is NULL THEN 99 else 0 END SortOrder
FROM [dbo].[rgMapGridLog] mg
INNER JOIN [dbo].[tInspectionRequest] ir ON ir.MapGridUID = mg.MapGridUID
INNER JOIN [dbo].[rWorkCenter] wc on wc.WorkCenterAbbreviationFLOC = mg.FuncLocMWC
LEFT JOIN (SELECT * From  [dbo].[tAssignedWorkQueue] where ActiveFlag = 1) awq ON awq.AssignedInspectionRequestUID = ir.InspectionRequestUID
Left Join (Select * from UserTb where UserActiveFlag = 1 and ISNULL(UserInActiveFlag, 0) = 0 and UserUID = @UserUID) u on u.UserUID = awq.AssignedUserUID
WHERE ir.StatusType <> 'Completed') IR

Update t set sortOrder = 50
From #TempIR t
Join 
(
select u.UserUID, wc.WorkCenter, wc.WorkCenterAbbreviation, wc.WorkCenterAbbreviationFLOC from 
(SELECT * From  UserTb where UserActiveFlag = 1 and ISNULL(UserInActiveFlag, 0) = 0 and UserUID = @UserUID) u
Join [dbo].[xReportingGroupEmployeexRef] xRG on xrg.UserUID = u.UserUID
Join [dbo].[rReportingGroup] rg on rg.ReportingGroupUID = xrg.ReportingGroupUID
Join [dbo].[xReportingGroupAndWorkcenterxRef] xWC on xWC.ReportingGroupUID = rg.ReportingGroupUID
Join [dbo].[rWorkCenter] wc on wc.WorkCenterUID = xWC.WorkCenterUID
) U on t.WorkCenter = u.WorkCenter


select * from #TempIR order by SortOrder, WorkCenter

Drop Table #TempIR