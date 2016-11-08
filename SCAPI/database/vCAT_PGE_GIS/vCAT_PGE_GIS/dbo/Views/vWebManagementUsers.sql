







CREATE View [dbo].[vWebManagementUsers] AS

select Distinct
-- rg.GroupName 
'' [GroupName]
, Case WHEN u.UserInactiveDTLT is null THEN 'Active' ELSE 'Inactive' END [Status]
, u.UserLastName [LastName] 
, u.UserFirstName
, u.UserLANID
,CONCAT(u.UserLastName, ', ', u.UserFirstName) AS [UserFullName] -- , ' (', u.UserLANID, ')') AS [UserFullName]
, u.UserEmployeeType
, ISNULL(oq.Status, 'No OQ') [OQ]
, ISNULL(r.RoleName, 'No Role') Role
, u.UserUID
, wc.WorkCenter
, u.HomeWorkCenterUID
, u.UserAppRoleType [AppRoleType]
from (Select * from usertb where UserActiveFlag = 1 and UserInActiveFlag = 0) u
Left join [dbo].[xReportingGroupEmployeexRef] rgxrf on u.UserUID = rgxrf.UserUID
Left join rReportingGroup rg on rg.ReportingGroupUID = rgxrf.ReportingGroupUID
Left Join vUserCurrentOQStatusByUserUID OQ on oq.UserUID = u.UserUID
left Join rRole r on r.RoleUID = rgxrf.RoleUID
left join (select * from rWorkCenter where ActiveFlag = 1) wc on u.HomeWorkCenterUID = wc.WorkCenterUID








