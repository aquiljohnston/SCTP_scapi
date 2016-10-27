Create View [vMapGridDivisionWorkCenterSupervisor]
AS
select 
mg.MapGridUID, 
mg.FLOC, 
mg.FuncLocMap, 
mg.FuncLocPlat, 
mg.FuncLocMapBoundary, 
mg.FuncLocPlatPrefix, 
mg.FuncLocPlatSuffix,
wc.WorkCenterUID,
wc.Division,
wc.WorkCenter,
mg.FuncLocMWC,
u.UserLANID,
u.UserLastName,
u.UserFirstName
From
(Select * from rgMapGridLog where ActiveFlag = 1) mg
Left Join (Select * from rWorkCenter where ActiveFlag = 1) wc on wc.WorkCenterAbbreviationFLOC = mg.FuncLocMWC
Left Join (select * from UserTb where UserActiveFlag = 1 ) u on u.UserUID = wc.SupervisorUID