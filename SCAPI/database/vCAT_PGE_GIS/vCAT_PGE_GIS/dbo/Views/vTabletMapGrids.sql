




CREATE VIEW [dbo].[vTabletMapGrids] AS

/*****************************************************************************************************************
NAME:		[dbo].[vTabletMapGrids]
SERVER:     SC1-DEV01
DATABASE:   vCAT_PGE_GIS_DEV
    
Requirements:					 - None at this time.

Outstanding Issues:              - None at this time.

Permissions for View:			 - GRANT EXECUTE ON [dbo].[vTabletMapGrids] TO [Reports] AS [dbo]
								 - GRANT EXECUTE ON [dbo].[vTabletMapGrids] TO [ApplicationCometTracker] AS [dbo]
HISTORY
DATE            DEVELOPER         COMMENTS
-------------   ---------------   -------------------------------------------------------------------
2016-08-23      CBowker           Initial Build
2016-08-24		CBowker			  Modifictions for new table changes from MapGrids. Left old columns until demo is done on 8/25/16
									Need to retire this view and replace it with the same name from the one that has '_New' after it

*** Debug Code **
******************************************************************************************************************/

-- DECLARE
 
/*****************************************************************************************************************

	-- SELECT * FROM [dbo].[vTabletMapGrid]

******************************************************************************************************************/

---- Base View ---------------------------------------------------------------------------------------------------

SELECT 
	 mg.MapGridUID AS MapGridsUID
	,mg.CreateDTLT
	,mg.ModifiedDTLT
	,mg.FLOC
	,wc.WorkCenter
	,wc.WorkCenterAbbreviation
	,wc.WorkCenterAbbreviationFLOC
	,mg.FuncLocMWC	
	,mg.FuncLocMapBoundary	
	,mg.FuncLocPlatSuffix	
	,mg.FuncLocMap	
	,mg.FuncLocPlat	
	,mg.FuncLocPlatPrefix AS FuncLocPlatChar2
	,mg.FuncLocPlatNo AS FuncLocPlatChar3
	,NULL AS FuncLocPlatChar4
	,mg.CentroidLat
	,mg.CentroidLong
	,mg.GeoBufferWithDrift
FROM [dbo].[rgMapGridLog] mg
LEFT JOIN [dbo].[rWorkCenter] wc ON 
		wc.WorkCenterAbbreviationFLOC = mg.FuncLocMWC
	AND wc.ActiveFlag = 1
	AND wc.StatusType = 'Active'
WHERE 
	mg.StatusType = 'Active'
AND mg.ActiveFlag = 1

-- Debug filters
--AND mg.FuncLocMWC = 'SNFR'
--AND mg.FuncLocPlatChar3 = 1





GO
GRANT SELECT
    ON OBJECT::[dbo].[vTabletMapGrids] TO [ApplicationCometTracker]
    AS [dbo];

