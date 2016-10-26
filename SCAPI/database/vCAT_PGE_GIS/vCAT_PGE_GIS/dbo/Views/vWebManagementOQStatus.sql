
Create View [vWebManagementOQStatus]
AS
select 
u.UserLANID
, oq.OQProfile as [OQ]
, CASE WHEN oq.OQExpireDate < getdate() THEN 'Lapsed' ELSE 'Current' END [Status]
, oq.OQExpireDate [Expires]
from usertb u
Join [dbo].[tInspectorOQLog] OQ on oq.UserUID = u.UserUID