









CREATE View [dbo].[vWebManagementDispatch] AS
/*****************************************************************************************************************
NAME:		[dbo].[vWebManagementDispatch]
SERVER:     SC1-DEV01
DATABASE:   vCAT_PGE_GIS_DEV
    
Requirements:					 - None at this time.

Outstanding Issues:              - None at this time.

Permissions for View:			 - GRANT SELECT ON [dbo].[vWebManagementDispatch] TO [ApplicationCometTracker] AS [dbo]
HISTORY
DATE            DEVELOPER         COMMENTS
-------------   ---------------   -------------------------------------------------------------------
2016-08-23      GWheeler          Initial Build
2016-08-24		CBowker			  Modifictions for new table changes from MapGrids. Left old columns 
2016-08-24		CBowker			  Added InspectionRequestUID per Jose request

*** Debug Code **
******************************************************************************************************************/

-- DECLARE
 
/*****************************************************************************************************************

	-- SELECT * FROM [dbo].[vTabletMapGrid]

******************************************************************************************************************/

---- Base View ---------------------------------------------------------------------------------------------------

/***
-- Old code that need to be updated to support the correct locations of Wall, Plat 

select wc.Division
, mg.WorkCenter
, ir.SurveyType
, Replace(ir.MapID, '-', '/') as [Map/Plat]
, ir.LsNtfNo [Notification ID]
, ir.ComplianceDueDate
, ir.ReleaseDate [SAP Released]
, ISNULL(awq.AssignedCount, 0) [Assigned]
, Cast(Year(ir.ComplianceDueDate) as Char(4)) + ' - ' + datename(mm, ir.ComplianceDueDate) As ComplianceYearMonth
from rgMapGridLog mg
Join tInspectionRequest ir on ir.MapGridUID = mg.MapGridsUID
Join rWorkCenter wc on wc.WorkCenter = mg.WorkCenter
Left Join (Select AssignedInspectionRequestUID, Count(*) AssignedCount From  tAssignedWorkQueue group by AssignedInspectionRequestUID) awq on awq.AssignedInspectionRequestUID = ir.InspectionRequestUID
where ir.StatusType <> 'Completed'

***/

---------------------------------------------------------------------------------------------------------------------------------

SELECT
 ir.InspectionRequestUID
,wc.Division
,wc.WorkCenter
,ir.SurveyType
,Replace(ir.MapID, '-', '/') as [MapPlat]
,ir.LsNtfNo AS [Notification ID]
,ir.ComplianceDueDate
,ir.ReleaseDate AS [SAP Released]
,ISNULL(awq.AssignedCount, 0) AS [Assigned]
,CAST(YEAR(ir.ComplianceDueDate) AS CHAR(4)) + ' - ' + DATENAME(mm, ir.ComplianceDueDate) AS ComplianceYearMonth
,mg.FLOC	
,CASE WHEN DATEDIFF(dd, Cast(getdate() as date), ir.ComplianceDueDate) < 4 THEN 1 ELSE 0 END [Within3Days]

FROM [dbo].[rgMapGridLog] mg
INNER JOIN [dbo].[tInspectionRequest] ir ON ir.MapGridUID = mg.MapGridUID
INNER JOIN [dbo].[rWorkCenter] wc on wc.WorkCenterAbbreviationFLOC = mg.FuncLocMWC
LEFT JOIN (
			SELECT AssignedInspectionRequestUID, Count(*) AS AssignedCount 
			FROM  [dbo].[tAssignedWorkQueue]
			Where ActiveFlag = 1 
			GROUP BY AssignedInspectionRequestUID
		   ) awq ON awq.AssignedInspectionRequestUID = ir.InspectionRequestUID
WHERE ir.StatusType <> 'Completed' and ir.ActiveFlag = 1






