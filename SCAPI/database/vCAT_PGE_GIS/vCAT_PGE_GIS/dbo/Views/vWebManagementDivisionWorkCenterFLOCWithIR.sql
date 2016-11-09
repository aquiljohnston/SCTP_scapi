
Create View [dbo].[vWebManagementDivisionWorkCenterFLOCWithIR]
AS
select wc.Division, wc.WorkCenter, mg.FLOC, ISNULL(ir.SurveyType, '') [SurveyFreq] from [dbo].[rWorkCenter] wc
Left Join [dbo].[rgMapGridLog] mg on wc.WorkCenterAbbreviationFLOC = mg.FuncLocMWC and mg.ActiveFlag = 1
Left Join (Select * from tInspectionRequest where ActiveFlag = 1) ir on ir.FLOC = mg.FLOC
where wc.ActiveFlag = 1