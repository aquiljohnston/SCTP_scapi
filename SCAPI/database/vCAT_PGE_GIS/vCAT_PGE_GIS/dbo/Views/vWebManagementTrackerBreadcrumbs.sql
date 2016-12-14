
CREATE VIEW [dbo].[vWebManagementTrackerBreadcrumbs] AS
/*****************************************************************************************************************
NAME:		[dbo].[vWebManagementTrackerBreadcrumbs]
SERVER:     SC1-DEV01
DATABASE:   vCAT_PGE_GIS_STAGE
    
Requirements:					 - None at this time.

Outstanding Issues:              - None at this time.

Permissions for View:			 - GRANT EXECUTE ON [dbo].[vWebManagementTrackerBreadcrumbs] TO [Reports] AS [dbo]
								 - GRANT EXECUTE ON [dbo].[vWebManagementTrackerBreadcrumbs] TO [ApplicationCometTracker] AS [dbo]
								 - GRANT EXECUTE ON [dbo].[vWebManagementTrackerBreadcrumbs] TO [ApplicationLH] AS [dbo]
HISTORY
DATE            DEVELOPER         COMMENTS
-------------   ---------------   -------------------------------------------------------------------
2016-11-25      CBowker           Initial Build

*** Debug Code **
******************************************************************************************************************/

-- DECLARE
 
/*****************************************************************************************************************

	-- SELECT * FROM [dbo].[vWebManagementTrackerBreadcrumbs]

******************************************************************************************************************/

---- Base View ---------------------------------------------------------------------------------------------------
---- Breadcrumbs. -- PROXY for on the fly SHAPE calculation should be removed when insert from API can create the SHAPE on Insert.

SELECT 
 b.BreadcrumbUID			AS [UID]
,b.BreadcrumbCreatedUserUID	AS [CreatedUserUID]
,u.UserLANID				AS [LanID]
,u.UserLastName				AS [LastName]
,u.UserFirstName			AS [FirstName]
,CAST(b.BreadcrumbCreatedDate AS DATE) AS [SurveyDate]
,FORMAT(b.BreadcrumbCreatedDate, 'HH\:mm') AS [SurveyTime]
,b.BreadcrumbSrcDTLT		AS [SrcDTLT]
,BreadcrumbGPSType			AS [GPSType]
,b.BreadcrumbLatitude		AS [Latitude]
,b.BreadcrumbLongitude		AS [Longitude]
,b.BreadcrumbBatteryLevel	AS [BatteryLevel]
,b.BreadcrumbSpeed			AS [Speed]
,b.BreadcrumbHeading		AS [Heading]
,b.BreadcrumbGPSAccuracy	AS [GPSAccuracy]
,b.BreadcrumbSatellites		AS [Satellites]
,b.BreadcrumbAltitude		AS [Altitude]
,b.BreadcrumbDeviceID		AS [DeviceID]
--,b.BreadcrumbShape			AS [Shape] -- Cannot be used until this is populated. It can be done on the fly for the Current Activity but not on the RawBreadcrumbs
,(GEOGRAPHY::STPointFromText('POINT(' + CAST(b.BreadcrumbLongitude AS VARCHAR(20)) + ' ' + CAST(b.BreadcrumbLatitude AS VARCHAR(20)) + ')', 4326)) AS Shape -- WGS84 -- PROXY until this can be corrected
-- SELECT *
-- SELECT Count(*) AS RowCounts
FROM [dbo].[BreadcrumbTb] b
LEFT  JOIN [dbo].[UserTb] u
	ON u.UserUID = b.BreadcrumbCreatedUserUID