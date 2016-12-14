


CREATE FUNCTION [dbo].[fnTabletIR](	@UserUID varchar(100) )


RETURNS @TempIR TABLE
(
	IRUID varchar(100) NULL
	,Division varchar(20) NULL
	,WorkCenter varchar(20) NULL
	,SurveyType varchar(20) NULL
	,MapPlat varchar(20) NULL
	,[NotificationID] varchar(20) NULL
	,ComplianceDueDate varchar(20) NULL
	,[SAPReleased] varchar(20) NULL
	,ComplianceYearMonth varchar(20) NULL
	,AssignedUserUID varchar(100) NULL
	,DispatchMethod varchar(100) NULL
	,IRStatus varchar(100)
	,MapGridUID varchar(100)
	,AssignedWorkQueueUID varchar(100)
	,AssignedDate datetime
	,SortOrder int
	,Within3Days bit
)

AS
BEGIN

/*

Select * From fnTabletIR('User_57590026_20160822135947_Postman') Order by SortOrder, WorkCenter

*/

--Declare @UserUID varchar(100) = 'User_57590026_20160822135947_Postman'


	Insert Into @TempIR
	SELECT
		 ir.InspectionRequestUID
		,wc.Division
		,wc.WorkCenter
		,ir.SurveyType
		--,Replace(ir.MapID, '-', '/') as [MapPlat]
		,COALESCE(FuncLocMapBoundary, '') + mg.FuncLocMap + '/' + Right('0000' + mg.funcLocPlat + COALESCE(mg.FuncLocPlatSuffix, ''), 4) as [MapPlat]
		,ir.LsNtfNo AS [NotificationID]
		,ir.ComplianceDueDate
		,ir.ReleaseDate AS [SAPReleased]
		,CAST(YEAR(ir.ComplianceDueDate) AS CHAR(4)) + ' - ' + DATENAME(mm, ir.ComplianceDueDate) AS ComplianceYearMonth
		, CASE WHEN awq.AssignedUserUID = @UserUID THEN awq.AssignedUserUID ELSE '' END [AssignedUserUID]
		, CASE WHEN awq.AssignedUserUID = @UserUID THEN awq.DispatchMethod ELSE '' END [DispatchMethod]
		, IR.StatusType
		, IR.MapGridUID
		, ISNULL(awq.AssignedWorkQueueUID, '') [AssignedWorkQueueUID]
		, awq.AssignedDate
		,CASE WHEN u.UserName is NULL THEN 99 else 0 END SortOrder
		,CASE WHEN DATEDIFF(ww, Cast(getdate() as date), ir.ComplianceDueDate) < 4 THEN 1 ELSE 0 END [Within3Days]
	FROM (Select * from [dbo].[rgMapGridLog] where ActiveFlag = 1) mg
	INNER JOIN (Select * from [dbo].[tInspectionRequest] where ActiveFlag = 1) ir ON ir.MapGridUID = mg.MapGridUID
	INNER JOIN (select * from  [dbo].[rWorkCenter] where ActiveFlag = 1) wc on wc.WorkCenterAbbreviationFLOC = mg.FuncLocMWC
	JOIN (SELECT * From  [dbo].[tAssignedWorkQueue] where ActiveFlag = 1 and AssignedUserUID = @UserUID) awq ON awq.AssignedInspectionRequestUID = ir.InspectionRequestUID
	Join (Select * from UserTb where UserActiveFlag = 1 and ISNULL(UserInActiveFlag, 0) = 0 and UserUID = @UserUID) u on u.UserUID = awq.AssignedUserUID
	WHERE ir.StatusType <> 'Completed' AND ISNULL(ir.LsNtfNo, '') <> ''

Union 


	SELECT Distinct
		 ir.InspectionRequestUID
		,wc.Division
		,wc.WorkCenter
		,ir.SurveyType
		--,Replace(ir.MapID, '-', '/') as [MapPlat]
		,COALESCE(FuncLocMapBoundary, '') + mg.FuncLocMap + '/' + Right('0000' + mg.funcLocPlat + COALESCE(mg.FuncLocPlatSuffix, ''), 4) as [MapPlat]
		,ir.LsNtfNo AS [NotificationID]
		,ir.ComplianceDueDate
		,ir.ReleaseDate AS [SAPReleased]
		,CAST(YEAR(ir.ComplianceDueDate) AS CHAR(4)) + ' - ' + DATENAME(mm, ir.ComplianceDueDate) AS ComplianceYearMonth
		, CASE WHEN awq.AssignedUserUID = @UserUID THEN awq.AssignedUserUID ELSE '' END [AssignedUserUID]
		, CASE WHEN awq.AssignedUserUID = @UserUID THEN awq.DispatchMethod ELSE '' END [DispatchMethod]
		, IR.StatusType
		, IR.MapGridUID
		, ISNULL(awq.AssignedWorkQueueUID, '') [AssignedWorkQueueUID]
		, awq.AssignedDate
		,CASE WHEN u.UserName is NULL THEN 99 else 0 END SortOrder
		,CASE WHEN DATEDIFF(ww, Cast(getdate() as date), ir.ComplianceDueDate) < 4 THEN 1 ELSE 0 END [Within3Days]
	FROM (Select * from [dbo].[rgMapGridLog] where ActiveFlag = 1) mg
	INNER JOIN (Select * from [dbo].[tInspectionRequest] where ActiveFlag = 1) ir ON ir.MapGridUID = mg.MapGridUID
	INNER JOIN (select * from  [dbo].[rWorkCenter] where ActiveFlag = 1) wc on wc.WorkCenterAbbreviationFLOC = mg.FuncLocMWC
	Left JOIN (SELECT * From  [dbo].[tAssignedWorkQueue] where ActiveFlag = 1 and AssignedUserUID = @UserUID) awq ON awq.AssignedInspectionRequestUID = ir.InspectionRequestUID
	left Join (Select * from UserTb where UserActiveFlag = 1 and ISNULL(UserInActiveFlag, 0) = 0 and UserUID = @UserUID) u on u.UserUID = awq.AssignedUserUID
	WHERE ir.StatusType <> 'Completed' AND ISNULL(ir.LsNtfNo, '') <> '' and awq.AssignedInspectionRequestUID is null
	
	
	Update t set sortOrder = 50
	From @TempIR t
	Join 
	(
	select u.UserUID, wc.WorkCenter, wc.WorkCenterAbbreviation, wc.WorkCenterAbbreviationFLOC from 
	(SELECT * From  UserTb where UserActiveFlag = 1 and ISNULL(UserInActiveFlag, 0) = 0 and UserUID = @UserUID) u
	Join [dbo].[xReportingGroupEmployeexRef] xRG on xrg.UserUID = u.UserUID
	Join [dbo].[rReportingGroup] rg on rg.ReportingGroupUID = xrg.ReportingGroupUID
	Join [dbo].[xReportingGroupAndWorkcenterxRef] xWC on xWC.ReportingGroupUID = rg.ReportingGroupUID
	Join [dbo].[rWorkCenter] wc on wc.WorkCenterUID = xWC.WorkCenterUID
	) U on t.WorkCenter = u.WorkCenter
	Where t.SortOrder <> 0

	--select * from #TempIR order by SortOrder, WorkCenter

	--Drop Table #TempIR

	RETURN

END




