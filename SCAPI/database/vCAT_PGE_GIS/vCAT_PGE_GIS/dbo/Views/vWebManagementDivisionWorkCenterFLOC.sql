Create View vWebManagementDivisionWorkCenterFLOC
AS
select wc.Division, wc.WorkCenter, mg.FLOC from [dbo].[rWorkCenter] wc
Left Join [dbo].[rgMapGridLog] mg on wc.WorkCenterAbbreviationFLOC = mg.FuncLocMWC and mg.ActiveFlag = 1
where wc.ActiveFlag = 1