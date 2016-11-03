



CREATE View [dbo].[vWebManagementMapStampDetail]
AS
select 
MapStampPicaroUID [SourceUID]
, msp.InspectionRequestUID [IRUID]
, msp.StatusType [Status]
, 'All' [SurveyArea]
, 'PIC' [SurveyType]
, msp.SurveyDate [DateSurveyed]
, ISNULL(u.UserLANID, '') [SurveyorLANID]
, msp.PicaroEquipmentID [InstSerialNum]
, 'G_COGIPICA' [InspType]
, msp.WindSpeedStart
, msp.WindSpeedMid
, 0 [SurveyModeFoot]
, 1 [SurveyModeMoble]
, msp.FeetOfMain
, msp.Services
, 0 [SortOrder]
, msp.Seq
, msp.LockedFlag
from (select * From [dbo].[tMapStampPicaro] where ActiveFlag = 1) msp
Left Join (Select * from UserTb where UserActiveFlag = 1) u on msp.SurveyorUID = u.UserUID

	


Union 
select 
[is]. InspectionServicesUID [SourceUID]
, [is].InspectionRequestUID [IRUID]
, [is].StatusType [Status]
, Cast([is].MapAreaNumber as varchar(10)) [SurveyArea]
, CASE WHEN CHARINDEX('PIC', EquipmentModeType) > 0 
	THEN 
		CASE WHEN CHARINDEX('LISA', EquipmentModeType) > 0 THEN 'LISA'
			 WHEN CHARINDEX('FOV', EquipmentModeType) > 0 THEN 'FOV'
			 WHEN CHARINDEX('GAP', EquipmentModeType) > 0 THEN 'GAP'
		END
	ELSE EquipmentModeType
	END [SurveyType]
, [is2].SurveyDate [DateSurveyed]
, ISNULL(u.UserLANID, '') [SurveyorLANID]
, ie.SerialNumber [InstSerialNum]
, ie.EquipmentType [InspType]
, wsbegin.WindSpeed [WindSpeedStart]
, wsmid.WindSpeed [WindSpeedMid]
, CASE WHEN [is].SurveyMode = 'F' THEN 1 ELSE 0 END [SurveyModeFoot]
, CASE WHEN [is].SurveyMode = 'M' THEN 1 ELSE 0 END [SurveyModeMoble]
, [is].EstimatedFeet [FeetOfMain]
, [is].EstimatedServices [Services]
--, 'tInspectionService' [Source Table]
, 1 [SortOrder]
, 0 Seq
, [is].LockedFlag
from 
(Select * from [dbo].[tInspectionService] where ActiveFlag = 1 and CHARINDEX('Forms/', MasterLeaklogUID) = 0 and SurveyMode <> 'G') [is]
left Join (Select * from UserTb where UserActiveFlag = 1) u on [is].CreatedUserUID = u.UserUID
Join (Select InspectionServicesUID, SrcDTLT [SurveyDate] from [dbo].[tInspectionService] where Revision= 0) [is2] on [is].InspectionServicesUID = [is2].InspectionServicesUID 
Left Join (Select * from tgWindSpeed where ActiveFlag = 1) wsbegin on [is].WindSpeedStartUID = wsbegin.WindSpeedUID
Left Join (Select * from tgWindSpeed where ActiveFlag = 1) wsmid on [is].WindSpeedStartUID = wsmid.WindSpeedUID
Left Join (Select * from [dbo].[tInspectionsEquipment] where ActiveFlag = 1) ie on [is].InspectionEquipmentUID = ie.InspecitonEquipmentUID
--Join (Select InspectionRequestUID
	--	, Sum(EstimatedFeet) [TotalFeetOfMain] 
	--	, Sum(EstimatedServices) [TotalServices]
	--	, SUM(CASE WHEN CHARINDEX('FOV', EquipmentModeType) > 0 THEN EstimatedFeet ELSE 0 END) FOVTotalFeetOfMain
	--	, SUM(CASE WHEN CHARINDEX('FOV', EquipmentModeType) > 0 THEN EstimatedServices ELSE 0 END) FOVTotalServices
	--	, SUM(CASE WHEN CHARINDEX('LISA', EquipmentModeType) > 0 THEN EstimatedFeet ELSE 0 END) LISATotalFeetOfMain
	--	, SUM(CASE WHEN CHARINDEX('LISA', EquipmentModeType) > 0 THEN EstimatedServices ELSE 0 END) LISATotalServices
	--	, SUM(CASE WHEN CHARINDEX('GAP', EquipmentModeType) > 0 THEN EstimatedFeet ELSE 0 END) GAPTotalFeetOfMain
	--	, SUM(CASE WHEN CHARINDEX('GAP', EquipmentModeType) > 0 THEN EstimatedServices ELSE 0 END) GAPTotalServices
	--From [dbo].[tInspectionService] 
	--where ActiveFlag = 1
	--Group By InspectionRequestUID) [is3] on [is3].InspectionRequestUID = [is].InspectionRequestUID
