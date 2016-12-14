

CREATE VIEW [dbo].[vWebManagementTrackerCurrentLocation] AS
/*****************************************************************************************************************
NAME:		[dbo].[vWebManagementTrackerCurrentLocation]
SERVER:     SC1-DEV01
DATABASE:   vCAT_PGE_GIS_STAGE
    
Requirements:					 - None at this time.

Outstanding Issues:              - None at this time.

Permissions for View:			 - GRANT EXECUTE ON [dbo].[vWebManagementTrackerCurrentLocation] TO [Reports] AS [dbo]
								 - GRANT EXECUTE ON [dbo].[vWebManagementTrackerCurrentLocation] TO [ApplicationCometTracker] AS [dbo]
								 - GRANT EXECUTE ON [dbo].[vWebManagementTrackerCurrentLocation] TO [ApplicationLH] AS [dbo]
HISTORY
DATE            DEVELOPER         COMMENTS
-------------   ---------------   -------------------------------------------------------------------
2016-11-25      CBowker           Initial Build

*** Debug Code **
******************************************************************************************************************/

-- DECLARE
 
/*****************************************************************************************************************

	-- SELECT * FROM [dbo].[vWebManagementTrackerCurrentLocation]

******************************************************************************************************************/

---- Base View ---------------------------------------------------------------------------------------------------
---- Current Location based upon last recorded Breadcrumb from the user that Created UserUID 

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
,(GEOGRAPHY::STPointFromText('POINT(' + CAST(b.BreadcrumbLongitude AS VARCHAR(20)) + ' ' + CAST(b.BreadcrumbLatitude AS VARCHAR(20)) + ')', 4326)) AS Shape -- WGS84
-- SELECT *
-- SELECT Count(*) AS RowCounts
FROM [dbo].[BreadcrumbTb] b
INNER JOIN ( SELECT BreadcrumbCreatedUserUID, MAX(BreadcrumbID) AS MaxBCID FROM [dbo].[BreadcrumbTb] GROUP BY BreadcrumbCreatedUserUID ) mb
	ON mb.MaxBCID = b.BreadcrumbID
LEFT  JOIN [dbo].[UserTb] u
	ON u.UserUID = b.BreadcrumbCreatedUserUID