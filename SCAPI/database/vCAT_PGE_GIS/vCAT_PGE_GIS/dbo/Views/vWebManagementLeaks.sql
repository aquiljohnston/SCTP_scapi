﻿








CREATE View [dbo].[vWebManagementLeaks]
AS
select
aai.StatusType [Status],
CASE WHEN aai.StatusType not in ('In Progress', 'NotApproved', 'Rejected', 'Pending') THEN 1 ELSE 0 END [Approved],
CASE WHEN aai.PotentialHCAType IN ('Y', 'Yes') THEN 'Yes' ELSE 'No' END HCA,
--aai.Map + '/' + aai.Plat [Map/Plat],
mg.FuncLocMap + '/' + mg.FuncLocPlat [Map/Plat],
ISNULL(aai.SAPNo, '') [SAPLeakNumber],
ISNULL(aai.SAPComments, '') [SAPComments],
aai.AboveBelowGroundType [AboveBelowGround],
aai.FoundDateTime,
aa.HouseNo + ' ' + aa.Street1 [Address],
aa.City,
aai.SORLType [SORL],
aai.ReadingGrade [ReadingInPct],

CASE WHEN FoundBy.InspecitonEquipmentID is null THEN 'V - Visual' ELSE  ISNULL(FoundByType.WebDisplayType, '') + ' (' + Foundby.SerialNumber + ')' END [InstTypeFoundBy],

CASE WHEN GradeBy.InspecitonEquipmentID is null THEN 'V - Visual' ELSE  ISNULL(GradeByType.WebDisplayType, '') + ' (' + GradeBy.SerialNumber + ')' END [InstTypeGradeBy],
aai.GradeType [Grade],
ISNULL(aai.Comments, '') [LocationRemarks],
aai.AssetAddressIndicationUID [UID],
aai.MasterLeakLogUID,
wc.Division,
aai.Map + '-' + aai.Plat [MapPlatNumber],
CASE WHEN aai.MapPlatLeakNumber is not null THEN CAST(aai.mapplatleaknumber as varchar(10)) + '/' ELSE '' END + ISNULL(aai.LeakNo, '') [LeakNo],
aai.LockedFlag [LockFlag]
from (Select * from tgAssetAddressIndication where ActiveFlag = 1) aai
--Join (select * from rDropDown where FilterName = 'ddLHLeakMgmtCurrentStatus' and ActiveFlag = 1) dd on aai.StatusType = dd.FieldValue
Join (Select * from tgAssetAddress where ActiveFlag = 1) aa on aai.AssetAddressUID = aa.AssetAddressUID
Join (Select * from [dbo].[rgMapGridLog] where ActiveFlag = 1) mg on mg.MapGridUID = aai.MapGridUID
join (Select * from [dbo].[rWorkCenter] where ActiveFlag = 1) wc on wc.WorkCenterAbbreviationFLOC = mg.FuncLocMWC
Left Join (Select * from tInspectionsEquipment where ActiveFlag = 1) FoundBy on aai.EquipmentFoundByUID = FoundBy.InspecitonEquipmentUID
Left Join (Select * from tInspectionsEquipment where ActiveFlag = 1) GradeBy on aai.EquipmentGradeByUID = GradeBy.InspecitonEquipmentUID
Left Join (select * from [dbo].[xOQEquipmentTypexRef]) FoundByType on Foundby.EquipmentType = FoundByType.SAPEquipmentType
Left Join (select * from [dbo].[xOQEquipmentTypexRef]) GradeByType on Gradeby.EquipmentType = GradeByType.SAPEquipmentType


