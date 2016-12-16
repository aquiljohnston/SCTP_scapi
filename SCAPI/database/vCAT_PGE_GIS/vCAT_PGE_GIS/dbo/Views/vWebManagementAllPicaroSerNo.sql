Create View vWebManagementAllPicaroSerNo
AS
select Distinct EqSerNo PicSerNo from [dbo].[tEquipmentLog] where EqObjType = 'G_COGIPICA'