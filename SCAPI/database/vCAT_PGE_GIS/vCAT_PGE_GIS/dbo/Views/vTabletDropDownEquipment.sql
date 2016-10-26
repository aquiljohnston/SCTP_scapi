Create View vTabletDropDownEquipment
AS
select u.UserUID
, u.UserLoginID
, u.UserLANID
, u.UserFirstName
, u.UserLastName
, UserEquip.EquipmentLogUID
, UserEquip.EquipmentDisplayType
, UserEquip.SAPEquipmentType
, UserEquip.EqSerNo
, UserEquip.WCAbbrev
, CASE WHEN UserWC.UserUID is null THEN 99 ELSE 10 END [SortOrder]
From UserTb U
Join 
(
	select u.UserUID, OQ.OQProfile, x.EquipmentDisplayType, x.SAPEquipmentType, e.EquipmentLogUID, e.EqSerNo, e.MWC [WCAbbrev]
	from UserTb u
	Join (Select * from [dbo].[tInspectorOQLog] where ActiveFlag = 1) OQ on u.UserUID = OQ.UserUID
	Join (Select * from [dbo].[xOQEquipmentTypexRef] where ActiveFlag = 1) x on x.OQProfile = OQ.OQProfile
	Join (Select * from [dbo].[tEquipmentLog] where ActiveFlag = 1) e on e.EqObjType = x.SAPEquipmentType
) UserEquip on u.UserUID = UserEquip.UserUID
Left Join
(
	select u.UserUID, wc.WorkCenter, wc.WorkCenterAbbreviationFLOC, wc.WorkCenterAbbreviation
	from UserTb u
	Join [dbo].[xReportingGroupEmployeexRef] xrg on u.UserUID = xrg.UserUID
	join [dbo].[rReportingGroup] rg on rg.ReportingGroupUID = xrg.ReportingGroupUID
	Join [dbo].[xReportingGroupAndWorkcenterxRef] xWC on xWC.ReportingGroupUID = rg.ReportingGroupUID
	Join [dbo].[rWorkCenter] wc on wc.WorkCenterUID = xWC.WorkCenterUID
) UserWC on UserEquip.UserUID = UserWC.UserUID and UserEquip.WCAbbrev = UserWC.WorkCenterAbbreviation

