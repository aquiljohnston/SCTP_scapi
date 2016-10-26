





CREATE View [dbo].[vTabletEquipment]
AS
select 
el.PrNtfNo
, el.SAPEqID
, el.EqObjType
, el.EqSerNo
, el.MWC
, el.CalbDate
, el.CalbStat
, el.LastCalbStat
, el.MPRNo
, el.UpdateFlag
, '???' MPR_Status
, Case When DATEDIFF(dd, isnull(ie.LastCalDate, dateadd(dd, -7, getdate())), getdate()) < 4 THEN 'YES' ELSE 'NO' END UsedYesterday
, el.CalbTime
, el.SrvyLanID
, el.SpvrLanID
, el.CalbHrs
, el.EquipmentLogUID
from [dbo].[tEquipmentLog] el
left join (select Equipmenttype, SerialNumber, max(srcdtlt) [LastCalDate] from  [dbo].[tInspectionsEquipment] Group By Equipmenttype, SerialNumber) ie on ie.EquipmentType = el.EqObjType and ie.SerialNumber = el.EqSerNo 
where el.ActiveFlag = 1 and el.EqObjType <> 'G_COGIPICA'







