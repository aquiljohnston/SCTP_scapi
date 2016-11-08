






CREATE View [dbo].[vWebManagementMapStampManagement]
AS
select 
IR.StatusType [MapStampStatus]
, wc.Division
, wc.WorkCenter
, ir.FLOC
, ir.InspectionType
, ir.LsNtfNo [NotificationID]
, ir.SurveyType
, ir.ComplianceDueDate [ComplianceDate]
, (DATEdiff(d, [is].[DetailStartDate], [is].[DetailEndDate]) +1) [TotalNoOfDays]
, ISNULL(ind.TotalLeaks, 0) [TotalNoOfLeaks]
, [is].TotalFeetOfMain
, [is].TotalServices
, [is].[FOVTotalFeetOfMain]
, [is].[FOVTotalServices]
, [is].[LISATotalFeetOfMain]
, [is].[LISATotalServices]
, [is].[GAPTotalFeetOfMain]
, [is].[GAPTotalServices]
, ir.InspectionRequestUID
, ir.PrevServ
, ir.PrevFtOfMain
, [is].[DetailStartDate]
, [is].[DetailEndDate]
from 
(Select * from [dbo].[tInspectionRequest] where ActiveFlag = 1) IR
Join (Select InspectionRequestUID, Sum(EstimatedFeet) [TotalFeetOfMain] 
			, Sum(EstimatedServices) [TotalServices]
			, Min(CreateDateTime) [DetailStartDate]
			, Max(CreateDateTime) [DetailEndDate]
			, SUM(CASE WHEN CHARINDEX('FOV', EquipmentModeType) > 0 THEN EstimatedFeet ELSE 0 END) FOVTotalFeetOfMain
			, SUM(CASE WHEN CHARINDEX('FOV', EquipmentModeType) > 0 THEN EstimatedServices ELSE 0 END) FOVTotalServices
			, SUM(CASE WHEN CHARINDEX('LISA', EquipmentModeType) > 0 THEN EstimatedFeet ELSE 0 END) LISATotalFeetOfMain
			, SUM(CASE WHEN CHARINDEX('LISA', EquipmentModeType) > 0 THEN EstimatedServices ELSE 0 END) LISATotalServices
			, SUM(CASE WHEN CHARINDEX('GAP', EquipmentModeType) > 0 THEN EstimatedFeet ELSE 0 END) GAPTotalFeetOfMain
			, SUM(CASE WHEN CHARINDEX('GAP', EquipmentModeType) > 0 THEN EstimatedServices ELSE 0 END) GAPTotalServices
	From [dbo].[tInspectionService] 
	where ActiveFlag = 1 and CHARINDEX('Forms/', MasterLeaklogUID) = 0 and SurveyMode <> 'G'
	Group By InspectionRequestUID) [is] on [is].InspectionRequestUID = ir.InspectionRequestUID
--Join (Select InspectionRequestUID, Min(srvdtlt) StartDate
	--From [dbo].[tInspectionService] 
	--Group By InspectionRequestUID) [is2] on [is2].InspectionRequestUID = ir.InspectionRequestUID
Join (Select * from [dbo].[rgMapGridLog] where ActiveFlag = 1) mg on mg.MapGridUID = ir.MapGridUID
Join (select * from [dbo].[rWorkCenter] where ActiveFlag = 1) wc on wc.WorkCenterAbbreviationFLOC = mg.FuncLocMWC
Left Join (Select InspectionRequestUID, Count(*) [TotalLeaks]
	From [dbo].[tInspectionService] 
	where ActiveFlag = 1
	Group By InspectionRequestUID) ind on ind.InspectionRequestUID = ir.InspectionRequestUID







