Create View vWebManagementPicaroSerNoByDate
AS
select EqSerNo, Cast(CreateDTLT as date) UsedDate from [dbo].[tEquipmentLog] el
Join (select Distinct SAPEquipmentType from [dbo].[xOQEquipmentTypexRef] where EquipmentDisplayType = 'Picaro') Pic on el.EqObjType = pic.SAPEquipmentType