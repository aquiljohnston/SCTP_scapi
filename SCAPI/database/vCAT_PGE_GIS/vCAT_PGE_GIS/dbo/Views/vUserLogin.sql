


CREATE View [dbo].[vUserLogin]
AS
Select u.UserUID, u.UserLoginID, u.UserFirstName, u.UserLastName, u.UserLANID, ISNULL(AllowLogin.GoodOQ, 0) [TabletLogin] , wc.WorkCenter, u.UserLastName +', ' + u.UserFirstName [UserFullName]
from UserTb u
Left Join
(Select UserUID
, CASE WHEN OQ0304 > getdate() and OQ0901 > getdate() and (OQ0401 > getdate() or OQ0403 > getdate()) THEN 1 ELSE 0 END [GoodOQ]
From
(
Select UserUID, Max([OQ-0304]) [OQ0304], Max([OQ-0401]) [OQ0401], Max([OQ-0403]) [OQ0403], Max([OQ-0901]) [OQ0901]
From
(select Useruid
,CASE WHEN OQProfile = 'OQ-0304' THEN OQExpireDate ELSE '1/1/1900' END [OQ-0304]
,CASE WHEN OQProfile = 'OQ-0401' THEN OQExpireDate ELSE '1/1/1900' END [OQ-0401]
,CASE WHEN OQProfile = 'OQ-0403' THEN OQExpireDate ELSE '1/1/1900' END [OQ-0403]
,CASE WHEN OQProfile = 'OQ-0901' THEN OQExpireDate ELSE '1/1/1900' END [OQ-0901]
from [dbo].[tInspectorOQLog] Where StatusType = 'Active' and ActiveFlag = 1) Main
Group By UserUID
) OQ ) AllowLogin on u.UserUID = AllowLogin.UserUID
left Join (select * from rWorkCenter where ActiveFlag = 1) wc on u.HomeWorkCenterUID = wc.WorkCenterUID

