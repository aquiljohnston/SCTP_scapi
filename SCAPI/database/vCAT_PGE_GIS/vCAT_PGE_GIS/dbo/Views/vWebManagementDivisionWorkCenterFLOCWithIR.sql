
CREATE View [dbo].[vWebManagementDivisionWorkCenterFLOCWithIR] AS

SELECT DISTINCT
 wc.Division
,wc.WorkCenter
--,mg.FLOC + 
--	CASE 
--		when ISNULL(ir.SurveyType, '') <> '' THEN ' - ' + ISNULL(ir.SurveyType, '') 
--		ELSE '' 
--	END [FLOC] -- Removed CMB 20161210
,UPPER(CONCAT(mg.FLOC, ' - ', COALESCE(InspectionFrequencyType,'')))AS [FLOC] -- Added CMB 20161210
--,ISNULL(ir.SurveyType, '') [SurveyFreq] -- Removed CMB 20161210
,UPPER(COALESCE(InspectionFrequencyType,'')) AS [SurveyFreq] -- Added CMB 20161210
FROM [dbo].[rWorkCenter] wc
INNER JOIN [dbo].[rgMapGridLog] mg
	ON	wc.WorkCenterAbbreviationFLOC = mg.FuncLocMWC 
	AND mg.ActiveFlag = 1
INNER JOIN (SELECT * FROM [dbo].[tInspectionRequest] WHERE ActiveFlag = 1 and StatusType <> 'Completed' and AdhocFlag = 0) ir 
	ON ir.FLOC = mg.FLOC
WHERE wc.ActiveFlag = 1 --and mg.FLOC = 'GD.PHYS.ANTI.0047.0B11'