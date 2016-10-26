
CREATE View [dbo].[vWebManagementLeakLogDropDown]
AS
Select wc.Division, 
wc.WorkCenter, 
u.UserLastName + ', ' + u.UserFirstName  + ' (' + u.UserLANID + ')' [Surveyor],
 mg.FuncLocMap + '/' + mg.FuncLocPlat [Map/Plat]
 , Format(mll.SrcDTLT, 'd', 'en-US') AS Date
From
(select * from [dbo].[tMasterLeakLog] where ActiveFlag = 1) mll
Left Join (select * from rgMapGridLog where ActiveFlag = 1) mg on mll.MapGridUID = mg.MapGridUID
Left Join (Select * from rWorkCenter where activeflag = 1) wc on mg.FuncLocMWC = wc.WorkCenterAbbreviationFLOC
Left Join (Select * from UserTb where UserActiveFlag = 1) u on mll.CreatedUserUID = u.UserUID
