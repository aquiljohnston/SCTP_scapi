
CREATE VIEW [dbo].[vWebManagementTrackerAOC] AS
/*****************************************************************************************************************
NAME:		[dbo].[vWebManagementTrackerAOC]
SERVER:     SC1-DEV01
DATABASE:   vCAT_PGE_GIS_STAGE
    
Requirements:					 - None at this time.

Outstanding Issues:              - None at this time.

Permissions for View:			 - GRANT EXECUTE ON [dbo].[vWebManagementTrackerAOC] TO [Reports] AS [dbo]
								 - GRANT EXECUTE ON [dbo].[vWebManagementTrackerAOC] TO [ApplicationCometTracker] AS [dbo]
								 - GRANT EXECUTE ON [dbo].[vWebManagementTrackerAOC] TO [ApplicationLH] AS [dbo]
HISTORY
DATE            DEVELOPER         COMMENTS
-------------   ---------------   -------------------------------------------------------------------
2016-11-25      CBowker           Initial Build

*** Debug Code **
******************************************************************************************************************/

-- DECLARE
 
/*****************************************************************************************************************

	-- SELECT * FROM [dbo].[vWebManagementTrackerAOC]

******************************************************************************************************************/

---- Base View ---------------------------------------------------------------------------------------------------
---- 

SELECT 
aoc.StatusType, -- Debug Only
aoc.ActiveFlag, -- Debug Only

 aoc.AssetAddressAOCUID					AS [UID]
,aoc.CreatedUserUID						AS[CreatedUserUID]
,u.UserLANID							AS [LanID]
,u.UserLastName							AS [LastName]
,u.UserFirstName						AS [FirstName]

,aoc.DateFound							AS [SurveyDateTime] -- May need to rename as it is FoundDateTime and it should not change
,CAST(aoc.DateFound AS DATE)			AS [SurveyDate] -- May need to rename as it FoundDate and it should not change
,FORMAT(aoc.DateFound, 'HH\:mm')		AS [SurveyTime] -- May need to rename as it FoundTime and it should not change
,aoc.SrcDTLT							AS [SrcDTLT]
,aoc.GPSType							AS [GPSType]
,aoc.Latitude							AS [Latitude] -- Question if GPS is being captured when I see most of the time the GPS shows Green Good
,aoc.Longitude							AS [Longitude] -- Question if GPS is being captured when I see most of the time the GPS shows Green Good
,aoc.Speed								AS [Speed]
,aoc.Bearing							AS [Heading]
,aoc.HDOP								AS [GPSAccuracy]
,aoc.NumberOfSatellites					AS [Satellites] 
,aoc.HeightOfGeoid						AS [Altitude]
,aoc.SourceID							AS [SourceID] -- This is also poupulated with unexpected data
--,aoc.Shape			AS [Shape] -- Cannot be used until this is populated. It can be done on the fly for the Current Activity but not on the RawBreadcrumbs
,(GEOGRAPHY::STPointFromText('POINT(' + CAST(aoc.Longitude AS VARCHAR(20)) + ' ' + CAST(aoc.Latitude AS VARCHAR(20)) + ')', 4326)) AS Shape -- WGS84

,'Missing Data NonAssetLocationFlag 1 Non Premise. 0 for Premise' AS NonAssetLocationFlag
,'Missing PipelineType Data' AS PipelineType -- ind.. It should come from adr.PipelineType (LT, SM, DM) or 'GT' if FLOC = 'GT.PHYS.TRNS.9999.0T99'

,'Missing Data if HouseNoNA Was Flagged' AS HouseNoNAFlag
,adr.HouseNo
,adr.Street1
,adr.Apt
,adr.City
,'Missing State Data' AS State -- adr.State -- Not captured Why
,'Missing Zip Data' AS ZIP	-- adr.ZIP -- Not captured Why
,'Missing Meter Data. Not in JSON InspectionActivity with lable AssetLocationID' AS AssetLocationID 
,adr.Photo1 AS [AddressPhoto1] -- Are these being recorded?
,adr.Photo2 AS [AddressPhoto2] -- Are these being recorded?
,adr.Photo3 AS [AddressPhoto3] -- Are these being recorded?

,aoc.AOCType
,aoc.Comments

,aoc.Photo1 AS [AOCPhoto1]
,aoc.Photo2 AS [AOCPhoto2]
,aoc.Photo3 AS [AOCPhoto3]


-- SELECT *
-- SELECT Count(*) AS RowCounts
FROM 
[dbo].[tgAssetAddressAOC] aoc

LEFT  JOIN [dbo].[tgAssetAddress] adr
	ON	adr.AssetAddressUID = aoc.AssetAddressUID
	AND adr.ActiveFlag = 1
	
LEFT  JOIN [dbo].[UserTb] u
	ON u.UserUID = aoc.CreatedUserUID

WHERE aoc.ActiveFlag = 1