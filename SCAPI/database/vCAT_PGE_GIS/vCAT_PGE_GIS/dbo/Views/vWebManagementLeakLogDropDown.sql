

CREATE View [dbo].[vWebManagementLeakLogDropDown] AS


Select
 wc.Division
,wc.WorkCenter
,u.UserLastName + ', ' + u.UserFirstName  + ' (' + u.UserLANID + ')' [Surveyor]
--mg.FuncLocMap + '/' + mg.FuncLocPlat [Map/Plat] -- Old removed by cmb 20161209
,UPPER(CONCAT(RIGHT(ir.FLOC, 14) ,' - ', ir.InspectionFrequencyType )) AS [Map/Plat] -- added CMB 20161209
,Format(mll.ServiceDate, 'd', 'en-US') AS Date

From
(select * from [dbo].[tMasterLeakLog] where ActiveFlag = 1) mll
Left Join (select * from [dbo].[rgMapGridLog] where ActiveFlag = 1) mg on mll.MapGridUID = mg.MapGridUID
Left Join (Select * from [dbo].[rWorkCenter] where activeflag = 1) wc on mg.FuncLocMWC = wc.WorkCenterAbbreviationFLOC
Left Join (Select * from [dbo].[UserTb] where UserActiveFlag = 1) u on mll.CreatedUserUID = u.UserUID
LEFT  JOIN [dbo].[tInspectionRequest] ir
	ON ir.InspectionRequestUID = mll.InspectionRequestLogUID
