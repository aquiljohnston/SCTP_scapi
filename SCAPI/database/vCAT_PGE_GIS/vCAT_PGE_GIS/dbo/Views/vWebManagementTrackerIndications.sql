
CREATE VIEW [dbo].[vWebManagementTrackerIndications] AS
/*****************************************************************************************************************
NAME:		[dbo].[vWebManagementTrackerIndications]
SERVER:     SC1-DEV01
DATABASE:   vCAT_PGE_GIS_STAGE
    
Requirements:					 - None at this time.

Outstanding Issues:              - None at this time.

Permissions for View:			 - GRANT EXECUTE ON [dbo].[vWebManagementTrackerIndications] TO [Reports] AS [dbo]
								 - GRANT EXECUTE ON [dbo].[vWebManagementTrackerIndications] TO [ApplicationCometTracker] AS [dbo]
								 - GRANT EXECUTE ON [dbo].[vWebManagementTrackerIndications] TO [ApplicationLH] AS [dbo]
HISTORY
DATE            DEVELOPER         COMMENTS
-------------   ---------------   -------------------------------------------------------------------
2016-11-25      CBowker           Initial Build

*** Debug Code **
******************************************************************************************************************/

-- DECLARE
 
/*****************************************************************************************************************

	-- SELECT * FROM [dbo].[vWebManagementTrackerMapGridCompliance]

******************************************************************************************************************/

---- Base View ---------------------------------------------------------------------------------------------------
---- 


SELECT 
ind.StatusType, -- Debug Only
ind.ActiveFlag, -- Debug Only

 ind.AssetAddressIndicationUID			AS [UID]
,ind.CreatedUserUID						AS[CreatedUserUID]
,u.UserLANID							AS [LanID]
,u.UserLastName							AS [LastName]
,u.UserFirstName						AS [FirstName]
,ind.FoundDateTime						AS [SurveyDateTime]
,CAST(ind.FoundDateTime AS DATE)		AS [SurveyDate]
,FORMAT(ind.FoundDateTime, 'HH\:mm')	AS [SurveyTime]
,ind.SrcDTLT							AS [SrcDTLT]
,ind.GPSType							AS [GPSType]
,ind.Latitude							AS [Latitude] -- Question if GPS is being captured when I see most of the time the GPS shows Green Good
,ind.Longitude							AS [Longitude] -- Question if GPS is being captured when I see most of the time the GPS shows Green Good
,ind.Speed								AS [Speed]
,ind.Bearing							AS [Heading]
,ind.HDOP								AS [GPSAccuracy]
,ind.NumberOfSatellites					AS [Satellites] 
,ind.HeightOfGeoid						AS [Altitude]
,ind.SourceID							AS [SourceID] -- This is also poupulated with unexpected data
--,ind.Shape			AS [Shape] -- Cannot be used until this is populated. It can be done on the fly for the Current Activity but not on the RawBreadcrumbs
,(GEOGRAPHY::STPointFromText('POINT(' + CAST(ind.Longitude AS VARCHAR(20)) + ' ' + CAST(ind.Latitude AS VARCHAR(20)) + ')', 4326)) AS Shape -- WGS84

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

,ind.LeakNo
,ind.SAPNo -- Only when StatsType = 'Completed' and data comes from INF007. Grade 1 and GT leaks will never have SAPNo
,ind.Route  -- Only on PipelineType = 'LT' is available if Route exist for the FLOC
,ind.StationBegin -- Only on PipelineType = 'LT' and where Route was slected. Calcualted in the creation of INF006
,ind.StationEnd -- Only on PipelineType = 'LT' and where Route was slected. Calcualted in the creation of INF006

,ind.FacilityType
,ind.AboveBelowGroundType
,ind.InitialLeakSourceType
,ind.ReportedByType
,ind.DescriptionReadingLocation
,ind.PavedType
,ind.SORLType
,ind.SORLOther
,ind.Within5FeetOfBuildingType
,ind.SuspectedCopperType

,ind.EquipmentFoundByUID
,ind.FoundBy -- Not populated. Do not know why?
,ind.FoundBySerialNumber -- Not populated. Do not know why?

,REPLACE(fnd.EquipmentType, 'G_COGI', '') AS fndEquipmentType
,REPLACE(fnd.SerialNumber, 'GI_', '') AS fndSerialNumber

,ind.InstrumentTypeGradeByType -- No BRs applied to this. Also Visual is written vs V - Visual. 
,SUBSTRING(grd.EquipmentType, 6,1) AS InstGradByType -- This is more accurate for forced rules execpt for Visual. Need more testing for CGI Inst Type (SCOP)

,ind.EquipmentGradeByUID
,ind.GradeBy -- Not populated. Do not know why?
,ind.GradeBySerialNumber -- Not populated. Do not know why?

,REPLACE(grd.EquipmentType, 'G_COGI', '') AS grdEquipmentType
,REPLACE(grd.SerialNumber, 'GI_', '') AS grdSerialNumber

,ind.ReadingGrade
,ind.GradeType
,ind.InfoCodesType
,ind.PotentialHCAType
,ind.HCAConstructionSupervisorUserUID -- This is not UID at all but LanIDs
,ind.HCADistributionPlanningEngineerUserUID -- This is not UID at all but LanIDs
,ind.HCAPipelineEngineerUserUID -- This is not UID at all but LanIDs
,ind.Comments AS [LocationRemarks]

,ind.Photo1 AS [LeakPhoto1]
,ind.Photo2 AS [LeakPhoto2]
,ind.Photo3 AS [LeakPhoto3]


-- SELECT *
-- SELECT Count(*) AS RowCounts
FROM [dbo].[tgAssetAddressIndication] ind

LEFT  JOIN [dbo].[tgAssetAddress] adr
	ON	adr.AssetAddressUID = ind.AssetAddressUID
	AND adr.ActiveFlag = 1
	
LEFT  JOIN [dbo].[UserTb] u
	ON u.UserUID = ind.CreatedUserUID

LEFT  JOIN [dbo].[tInspectionsEquipment] fnd
	ON	fnd.InspecitonEquipmentUID = ind.EquipmentFoundByUID
	AND	fnd.ActiveFlag = 1

LEFT  JOIN [dbo].[tInspectionsEquipment] grd
	ON	grd.InspecitonEquipmentUID = ind.EquipmentGradeByUID
	AND	grd.ActiveFlag = 1

WHERE ind.ActiveFlag = 1