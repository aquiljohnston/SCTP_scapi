


CREATE View [dbo].[vWebManagementDivisionWorkCenterFLOC] AS 

SELECT DISTINCT
 wc.Division
,wc.WorkCenter
,mg.FLOC 
FROM [dbo].[rWorkCenter] wc
INNER JOIN [dbo].[rgMapGridLog] mg 
	on	wc.WorkCenterAbbreviationFLOC = mg.FuncLocMWC 
	and mg.ActiveFlag = 1
WHERE wc.ActiveFlag = 1