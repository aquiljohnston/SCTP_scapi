Create View vWebManagementDropDownAOCType
AS

Select distinct 
AOC.AOCType,
wc.WorkCenter, 
wc.Division,
u.UserLastName + ', ' + u.UserFirstName + ' (' + u.UserLANID + ')' [Surveyor]
From
(Select * from [dbo].[tgAssetAddressAOC] where ActiveFlag = 1) AOC
Left Join (Select * from [dbo].[rgMapGridLog] where activeflag = 1) mg on aoc.MapGridUID = mg.MapGridUID
Left Join (select * from [dbo].[rWorkCenter] where ActiveFlag = 1) wc on mg.FuncLocMWC = wc.WorkCenterAbbreviationFLOC
Left join (select * from UserTb where UserActiveFlag = 1) u on aoc.CreatedUserUID = u.UserUID


