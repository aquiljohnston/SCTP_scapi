
CREATE VIEW [dbo].[vWebManagementTrackerHistoryDropDown] AS
/*****************************************************************************************************************
NAME:		[dbo].[vWebManagementTrackerHistoryDropDown]
SERVER:     SC1-DEV01
DATABASE:   vCAT_PGE_GIS_STAGE
    
Requirements:					 - None at this time.

Outstanding Issues:              - None at this time.

Permissions for View:			 - GRANT EXECUTE ON [dbo].[vWebManagementTrackerHistoryDropDown] TO [Reports] AS [dbo]
								 - GRANT EXECUTE ON [dbo].[vWebManagementTrackerHistoryDropDown] TO [ApplicationCometTracker] AS [dbo]
								 - GRANT EXECUTE ON [dbo].[vWebManagementTrackerHistoryDropDown] TO [ApplicationLH] AS [dbo]
HISTORY
DATE            DEVELOPER         COMMENTS
-------------   ---------------   -------------------------------------------------------------------
2016-12-05      CBowker           Initial Build

*** Debug Code **
******************************************************************************************************************/

-- DECLARE
 
/*****************************************************************************************************************

	-- SELECT * FROM [dbo].[vWebManagementTrackerHistoryDropDown]

******************************************************************************************************************/

---- Base View ---------------------------------------------------------------------------------------------------
---- Distinct values for Drop Downs beased upon Current Indications, AOCs and CGIs of the MapGridUID Surveyed for Division / MWC

SELECT -- DISTINCT
 UPPER(wc.Division)			AS [Division]
,UPPER(wc.WorkCenter)		AS [WorkCenter]
,UPPER(CONCAT(u.UserLastName,', ',u.UserFirstName,' (',u.UserLANID ,')')) AS [Surveyor]
,CAST(ind.FoundDateTime AS DATE)		AS [Date]

FROM [dbo].[tgAssetAddressIndication] ind
LEFT  JOIN [dbo].[rgMapGridLog] mg
	ON	mg.MapGridUID = ind.MapGridUID
	AND mg.ActiveFlag = 1
LEFT  JOIN [dbo].[UserTb] u
	ON	u.UserUID = ind.CreatedUserUID
	AND u.UserActiveFlag = 1
LEFT  JOIN [dbo].[rWorkCenter] wc
	ON	wc.WorkCenterAbbreviationFLOC = mg.FuncLocMWC
	AND wc.ActiveFlag = 1

UNION 


SELECT --DISTINCT
 UPPER(wc.Division)			AS [Division]
,UPPER(wc.WorkCenter)		AS [WorkCenter]
,UPPER(CONCAT(u.UserLastName,', ',u.UserFirstName,' (',u.UserLANID ,')')) AS [Surveyor]
,CAST(aoc.DateFound AS DATE)		AS [Date]

FROM [dbo].[tgAssetAddressAOC] aoc
LEFT  JOIN [dbo].[rgMapGridLog] mg
	ON	mg.MapGridUID = aoc.MapGridUID
	AND mg.ActiveFlag = 1
LEFT  JOIN [dbo].[UserTb] u
	ON	u.UserUID = aoc.CreatedUserUID
	AND u.UserActiveFlag = 1
LEFT  JOIN [dbo].[rWorkCenter] wc
	ON	wc.WorkCenterAbbreviationFLOC = mg.FuncLocMWC
	AND wc.ActiveFlag = 1


-- Cannot add CGE table as the FoundDateTime does not exist. This will have to be added at a later time. 
/***
UNION 


SELECT --DISTINCT
 UPPER(wc.Division)			AS [Division]
,UPPER(wc.WorkCenter)		AS [WorkCenter]
,UPPER(CONCAT(u.UserLastName,', ',u.UserFirstName,' (',u.UserLANID ,')')) AS [Surveyor]
,CAST(cge.FoundDateTime AS DATE)		AS [Date]

FROM [dbo].[tgAssetAddressCGE] CGE
LEFT  JOIN [dbo].[rgMapGridLog] mg
	ON	mg.MapGridUID = cge.MapGridUID
	AND mg.ActiveFlag = 1
LEFT  JOIN [dbo].[UserTb] u
	ON	u.UserUID = cge.CreatedUserUID
	AND u.UserActiveFlag = 1
LEFT  JOIN [dbo].[rWorkCenter] wc
	ON	wc.WorkCenterAbbreviationFLOC = mg.FuncLocMWC
	AND wc.ActiveFlag = 1

***/