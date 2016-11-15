

CREATE View [dbo].[vWebManagementDivisionWorkCenterFLOCWithIR]
AS
select wc.Division, wc.WorkCenter, mg.FLOC + CASE when ISNULL(ir.SurveyType, '') <> '' THEN ' - ' + ISNULL(ir.SurveyType, '') ELSE '' END [FLOC], ISNULL(ir.SurveyType, '') [SurveyFreq] from [dbo].[rWorkCenter] wc
Join [dbo].[rgMapGridLog] mg on wc.WorkCenterAbbreviationFLOC = mg.FuncLocMWC and mg.ActiveFlag = 1
Left Join (Select * from tInspectionRequest where ActiveFlag = 1 and StatusType <> 'Completed' and AdhocFlag = 0) ir on ir.FLOC = mg.FLOC
where wc.ActiveFlag = 1 --and mg.FLOC = 'GD.PHYS.ANTI.0047.0B11'