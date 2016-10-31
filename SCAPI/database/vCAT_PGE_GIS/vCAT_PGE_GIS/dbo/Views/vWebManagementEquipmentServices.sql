





CREATE view [dbo].[vWebManagementEquipmentServices]
AS
Select 
cast([is].srcdtlt as date) [Date],
SubString(ie.EquipmentType, 7, 10) [EquipmentType],
ie.SerialNumber,
[is].SurveyMode,
ws_start.WindSpeed [WindSpeedStart],
ws_mid.WindSpeed [WindSpeedMid],
[is].EstimatedFeet [FeetOfMain],
[is].EstimatedServices [NumOfServices],
[is].EstimatedHours [Hours],
[is].InspectionServicesUID,
--ie.InspecitonEquipmentUID,
[is].MasterLeakLogUID,
u.UserLastName + ', ' + u.UserFirstName [Surveyor],
wc.Division,
wc.WorkCenter,
mg.FuncLocMap + '/' + mg.FuncLocPlat [Map/Plat],
[is].MapAreaNumber,
[is].LockedFlag,
u.UserLANID,
ir.SurveyType
from tInspectionService [is]
Left Join (Select * from [dbo].[tInspectionsEquipment] where ActiveFlag = 1) [ie] on [is].InspectionEquipmentUID = [ie].InspecitonEquipmentUID
Left Join (select * from [dbo].[tgWindSpeed] where ActiveFlag = 1) ws_start on ws_start.WindSpeedUID = [is].WindSpeedStartUID
Left Join (select * from [dbo].[tgWindSpeed] where ActiveFlag = 1) ws_mid  on ws_mid.WindSpeedUID = [is].WindSpeedStartUID
Left Join (Select * from [dbo].[rgMapGridLog] where ActiveFlag = 1) mg on [is].MapGridUID = mg.MapGridUID
left Join (select * from [dbo].[rWorkCenter] where ActiveFlag = 1) wc on mg.FuncLocMWC = wc.WorkCenterAbbreviationFLOC
left join (select * from UserTb where UserActiveFlag = 1) u on [is].CreatedUserUID = u.UserUID
Left join (select * from tInspectionRequest where ActiveFlag = 1) ir on ir.InspectionRequestUID = [is].InspectionRequestUID
Where [is].ActiveFlag = 1


	




