



CREATE VIEW [dbo].[vTabletMapGrids_New] AS

/*****************************************************************************************************************
NAME:		[dbo].[vTabletMapGrids]
SERVER:     SC1-DEV01
DATABASE:   vCAT_PGE_GIS_DEV
    
Requirements:					 - None at this time.

Outstanding Issues:              - None at this time.

Permissions for View:			 - GRANT SELECT ON [dbo].[vTabletMapGrids] TO [ApplicationCometTracker] AS [dbo]

HISTORY
DATE            DEVELOPER         COMMENTS
-------------   ---------------   -------------------------------------------------------------------
2016-08-23      CBowker           Initial Build
2016-08-24		CBowker			  Modifictions for new table changes from MapGrids. This view should replace the 
									one without the '_New' as the same name after demo on 8/25/16

*** Debug Code **
******************************************************************************************************************/

-- DECLARE
 
/*****************************************************************************************************************

	-- SELECT * FROM [dbo].[vTabletMapGrid]

******************************************************************************************************************/

---- Base View ---------------------------------------------------------------------------------------------------

SELECT 
	 mg.MapGridUID
	,mg.FLOC
	,wc.WorkCenter
	,wc.WorkCenterAbbreviation
	,wc.WorkCenterAbbreviationFLOC
	,mg.FuncLocMWC	
	,mg.FuncLocMapBoundary	
	,mg.FuncLocPlatSuffix	
	,mg.FuncLocMap	
	,mg.FuncLocPlat	
	,mg.FuncLocPlatPrefix
	,mg.FuncLocPlatNo
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




