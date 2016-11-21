
CREATE View [dbo].[vTabletDropDownEquipment]
AS
select u.UserUID
, u.UserLoginID
, u.UserLANID
, u.UserFirstName
, u.UserLastName
, UserEquip.EquipmentLogUID
, UserEquip.EquipmentDisplayType
, UserEquip.EqObjType
, UserEquip.EqSerNo
, UserEquip.MWC
--, UserWC.WorkCenter
, UserEquip.PrNtfNo
, UserEquip.SAPEqID
, UserEquip.CalbDate
, Case When DATEDIFF(dd, isnull(UserEquip.LastCalDate, dateadd(dd, -7, getdate())), getdate()) < 4 THEN 'YES' ELSE 'NO' END UsedYesterday
, UserEquip.LastCalbStat
, UserEquip.MPRNo
, UserEquip.UpdateFlag
, UserEquip.MPR_Status
, UserEquip.CalbTime
, UserEquip.SrvyLanID
, UserEquip.SpvrLanID
, UserEquip.CalbHrs
, CASE WHEN UserWC.UserUID is null THEN 99 ELSE 10 END [SortOrder]
From UserTb U
Join 
(
	select u.UserUID, OQ.OQProfile, x.EquipmentDisplayType, x.SAPEquipmentType, e.EquipmentLogUID, e.EqSerNo, e.MWC, e.PrNtfNo, e.SAPEqID, e.CalbDate, e.LastCalbStat,
		e.MPRNo, e.UpdateFlag, '' [MPR_Status], e.CalbTime, e.SrvyLanID, e.SpvrLanID, e.CalbHrs, ie.LastCalDate, e.EqObjType
	from UserTb u
	Join (Select * from [dbo].[tInspectorOQLog] where ActiveFlag = 1) OQ on u.UserUID = OQ.UserUID
	Join (Select * from [dbo].[xOQEquipmentTypexRef] where ActiveFlag = 1) x on x.OQProfile = OQ.OQProfile
	Join (Select * from [dbo].[tEquipmentLog] where ActiveFlag = 1) e on e.EqObjType = x.SAPEquipmentType
	left join (select Equipmenttype, SerialNumber, max(srcdtlt) [LastCalDate] from  [dbo].[tInspectionsEquipment] Group By Equipmenttype, SerialNumber) ie on ie.EquipmentType = e.EqObjType and ie.SerialNumber = e.EqSerNo 

) UserEquip on u.UserUID = UserEquip.UserUID
Left Join
(
	select u.UserUID, wc.WorkCenter, wc.WorkCenterAbbreviationFLOC, wc.WorkCenterAbbreviation
	from UserTb u
	Join [dbo].[xReportingGroupEmployeexRef] xrg on u.UserUID = xrg.UserUID
	--join [dbo].[rReportingGroup] rg on rg.ReportingGroupUID = xrg.ReportingGroupUID
	--Join [dbo].[xReportingGroupAndWorkcenterxRef] xWC on xWC.ReportingGroupUID = rg.ReportingGroupUID
	Join [dbo].[rWorkCenter] wc on wc.WorkCenterUID = u.HomeWorkCenterUID
) UserWC on UserEquip.UserUID = UserWC.UserUID and UserEquip.MWC = UserWC.WorkCenterAbbreviation

