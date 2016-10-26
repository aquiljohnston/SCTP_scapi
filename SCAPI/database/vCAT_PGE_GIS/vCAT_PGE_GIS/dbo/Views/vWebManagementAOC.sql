

CREATE View [dbo].[vWebManagementAOC]
AS
select
Cast(DateFound as date) [Date],
Cast(DateFound as time) [Time],
u.UserLastName + ', ' + UserFirstName + ' (' + u.UserLANID + ')' [Surveyor],
wc.WorkCenter [WorkCenter],
mg.FuncLocMap + '/' + mg.FuncLocPlat [Map/Plat],
ir.SurveyType,
aoc.AOCType,
aoc.MeterNumber,
aa.HouseNo,
aa.Street1 + ', ' + aa.Street2 [Street],
aa.Apt,
aa.City,
aoc.Comments,
aoc.AssetAddressAOCUID [AOCUID],
aoc.ApprovedFlag,
aoc.Photo1,
aoc.Photo2,
aoc.Photo3,
wc.Division,
mg.FLOC,
u.UserLANID [LANID]
from 
	[dbo].[tgAssetAddressAOC] AOC
Join (Select * from UserTb where UserActiveFlag = 1) u on aoc.CreatedUserUID = u.UserUID
Join (Select * from [dbo].[rgMapGridLog] where ActiveFlag = 1) mg on aoc.MapGridUID = mg.MapGridUID
Join (Select * from [dbo].[rWorkCenter] where ActiveFlag = 1) wc on wc.WorkCenterAbbreviationFLOC = mg.FuncLocMWC
Join (select * from [dbo].[tgAssetAddress] where ActiveFlag = 1) aa on aa.AssetAddressUID = aoc.AssetAddressUID
Join (select * from [dbo].[tInspectionRequest] where ActiveFlag = 1) ir on ir.InspectionRequestUID = aoc.InspectionRequestUID


--select * from tgAssetInspection where AssetInspectionUID = 'AssetInspection_216698_20160912143930_System'

--IR_79367_20160824222011_System

--update tgAssetAddressAOC set InspectionRequestUID = 'IR_79367_20160824222011_System'

