
CREATE VIEW [dbo].[vWebManagementTrackerCurrentLocationDropDown] AS
/*****************************************************************************************************************
NAME:		[dbo].[vWebManagementTrackerCurrentLocationDropDown]
SERVER:     SC1-DEV01
DATABASE:   vCAT_PGE_GIS_STAGE
    
Requirements:					 - None at this time.

Outstanding Issues:              - None at this time.

Permissions for View:			 - GRANT EXECUTE ON [dbo].[vWebManagementTrackerCurrentLocationDropDown] TO [Reports] AS [dbo]
								 - GRANT EXECUTE ON [dbo].[vWebManagementTrackerCurrentLocationDropDown] TO [ApplicationCometTracker] AS [dbo]
								 - GRANT EXECUTE ON [dbo].[vWebManagementTrackerCurrentLocationDropDown] TO [ApplicationLH] AS [dbo]
HISTORY
DATE            DEVELOPER         COMMENTS
-------------   ---------------   -------------------------------------------------------------------
2016-12-05      CBowker           Initial Build

*** Debug Code **
******************************************************************************************************************/

-- DECLARE
 
/*****************************************************************************************************************

	-- SELECT * FROM [dbo].[vWebManagementTrackerCurrentLocationDropDown]

******************************************************************************************************************/

---- Base View ---------------------------------------------------------------------------------------------------
---- Distinct values for Drop Downs beased upon Current Location of the Surveyor using the Home Work Center UID for Division / MWC

SELECT DISTINCT
 UPPER(wc.Division)			AS [Division]
,UPPER(wc.WorkCenter)		AS [WorkCenter]
,UPPER(CONCAT(u.UserLastName,', ',u.UserFirstName,' (',u.UserLANID ,')')) AS [Surveyor]
,CAST(b.BreadcrumbCreatedDate AS DATE) AS [Date]
--,FORMAT(b.BreadcrumbCreatedDate, 'd', 'en-US') AS [Date] -- 
FROM [dbo].[BreadcrumbTb] b
INNER JOIN ( SELECT BreadcrumbCreatedUserUID, MAX(BreadcrumbID) AS MaxBCID FROM [dbo].[BreadcrumbTb] GROUP BY BreadcrumbCreatedUserUID ) mb
	ON mb.MaxBCID = b.BreadcrumbID
LEFT  JOIN [dbo].[UserTb] u
	ON	u.UserUID = b.BreadcrumbCreatedUserUID
	AND u.UserActiveFlag = 1
LEFT  JOIN [dbo].[rWorkCenter] wc
	ON	wc.WorkCenterUID = u.HomeWorkCenterUID
	AND wc.ActiveFlag = 1