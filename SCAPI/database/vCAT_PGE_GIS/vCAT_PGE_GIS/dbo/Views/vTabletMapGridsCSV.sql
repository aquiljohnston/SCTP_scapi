

CREATE VIEW [dbo].[vTabletMapGridsCSV] AS

/*****************************************************************************************************************
NAME:		[dbo].[vTabletMapGridsCSV]
SERVER:     SC1-DEV01
DATABASE:   vCAT_PGE_GIS_STAGE
    
Requirements:					 - None at this time.

Outstanding Issues:              - None at this time.

Permissions for View:			 - GRANT SELECT ON [dbo].[vTabletMapGridsCSV] TO [ApplicationCometTracker] AS [dbo]

HISTORY
DATE            DEVELOPER         COMMENTS
-------------   ---------------   -------------------------------------------------------------------
2016-12-07      CBowker           Initial Build

*** Debug Code **
******************************************************************************************************************/

-- DECLARE
 
/*****************************************************************************************************************

	-- SELECT * FROM [dbo].[vTabletMapGridsCSV]

******************************************************************************************************************/

---- Base View -----


SELECT 
 mg.MapGridUID			AS [MapGridsUID]
,CONCAT(mg.FuncLocMapBoundary,mg.FuncLocMap) AS [Map]
-- ,mg.FuncLocMap		AS [Map]
--,mg.FuncLocPlat		AS [Plat]
,RIGHT(CONCAT(mg.FuncLocPlat,mg.FuncLocPlatSuffix),4) AS [Plat]
,mg.FuncLocPlatPrefix	AS [PlatPrefix]
,mg.FuncLocPlatSuffix	AS [PlatSuffix]
,ISNULL(mg.FuncLocMapBoundary, '')	AS [Boundry]
,mg.CentroidLat			AS [Latitude]
,mg.CentroidLong		AS [Longitude]
,mg.GeoBufferWithDrift	AS [MaxDistance]
--,mg.LeakSurveyFrequency AS [SurveyType]
--,'' AS [SurveyType]
,wc.WorkCenter			AS [WorkCenter]
,mg.CreateDTLT			AS [CreatedDate]
,mg.ModifiedDTLT		AS [ModifiedDate]


FROM [dbo].[rgMapGridLog] mg
LEFT JOIN [dbo].[rWorkCenter] wc ON 
		wc.WorkCenterAbbreviationFLOC = mg.FuncLocMWC
	AND wc.ActiveFlag = 1
	AND wc.StatusType = 'Active'
WHERE 
	mg.StatusType = 'Active'
AND mg.ActiveFlag = 1