﻿
CREATE View [dbo].[vWebManagementDropDownAOCSurveyor]
AS

Select distinct u.UserLastName + ', ' + u.UserFirstName + ' (' + u.UserLANID + ')' [Surveyor], wc.WorkCenter, wc.Division
From
(Select * from [dbo].[tgAssetAddressAOC] where ActiveFlag = 1) AOC
Join (Select * from [dbo].[rgMapGridLog] where activeflag = 1) mg on aoc.MapGridUID = mg.MapGridUID
Join (select * from [dbo].[rWorkCenter] where ActiveFlag = 1) wc on mg.FuncLocMWC = wc.WorkCenterAbbreviationFLOC
join (select * from UserTb where UserActiveFlag = 1) u on aoc.CreatedUserUID = u.UserUID

