


CREATE View [dbo].[vWebManagementMapStampDropDown]
AS
Select Distinct wc.Division, 
wc.WorkCenter, 
--u.UserLastName + ', ' + u.UserFirstName  + ' (' + u.UserLANID + ')' [Surveyor],
 mg.FuncLocMap + '/' + mg.FuncLocPlat [Map/Plat]
 , Format(ir.CreateDTLT, 'd', 'en-US') AS Date
From
(select * from [dbo].[tInspectionRequest] where ActiveFlag = 1) ir
Left Join (select * from rgMapGridLog where ActiveFlag = 1) mg on ir.MapGridUID = mg.MapGridUID
Left Join (Select * from rWorkCenter where activeflag = 1) wc on mg.FuncLocMWC = wc.WorkCenterAbbreviationFLOC
--Left Join (Select * from UserTb where UserActiveFlag = 1) u on mll.CreatedUserUID = u.UserUID
Join (select Distinct InspectionRequestUID from tInspectionService where PlaceHolderFlag <> 1 and ActiveFlag = 1 and StatusType <> 'Deleted') [is] on [is].InspectionRequestUID = ir.InspectionRequestUID